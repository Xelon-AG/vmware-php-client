<?php

namespace Xelon\VmWareClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VmWareClientInit
{
    public const MODE_REST = 'rest';

    public const MODE_SOAP = 'soap';

    public const MODE_BOTH = 'both';

    protected string $ip;

    private string $login;

    private string $password;

    protected float $version;

    protected ?GuzzleClient $guzzleClient;

    public ?\SoapClient $soapClient;

    public function __construct(
        string $ip,
        string $login,
        string $password,
        string $mode = self::MODE_REST,
        float $version = 7
    ) {
        $this->ip = $ip;
        $this->login = $login;
        $this->password = $password;
        $this->version = $version;

        switch ($mode) {
            case self::MODE_REST:
                $this->initRestSession();
                break;
            case self::MODE_SOAP:
                $this->initSoapSession();
                break;
            case self::MODE_BOTH:
                $this->initRestSession();
                $this->initSoapSession();
                break;
            default:
                throw new \Exception('Illegal mode type');
        }
    }

    private function initRestSession(): void
    {
        $this->guzzleClient = new GuzzleClient(['verify' => false, 'base_uri' => $this->ip]);
        $sessionInfo = Cache::get("vcenter-rest-session-$this->ip");

        if (! $sessionInfo) {
            $this->createRestSession();
        } elseif ($this->isSessionExpired($sessionInfo['expired_at'])) {
            $this->deleteRestSession($sessionInfo['api_session_id']);
            $this->createRestSession();
        } else {
            $this->createNewGuzzleClient($sessionInfo['api_session_id']);
        }
    }

    private function createRestSession(): void
    {
        try {
            $authReponse = $this->guzzleClient->post(
                $this->version >= 7 ? '/api/session' : '/rest/com/vmware/cis/session',
                ['auth' => [$this->login, $this->password]]
            );
            $apiSessionId = json_decode($authReponse->getBody());

            if ($this->version < 7) {
                $apiSessionId = $apiSessionId->value;
            }

            Cache::put("vcenter-rest-session-$this->ip", [
                'api_session_id' => $apiSessionId,
                'expired_at' => Carbon::now()->addSeconds(config('vmware-php-client.session_ttl') * 60 - 30),
            ]);

            $this->createNewGuzzleClient($apiSessionId);
        } catch (ConnectException $e) {
            Log::error('Rest api Connect exception: '.$e->getMessage());
        } catch (ServerException $e) {
            Log::error('Rest api server exception: '.$e->getMessage());
        } catch (RequestException $e) {
            Log::error('Rest api Request exception: '.$e->getMessage());
        } catch (\Exception $e) {
            Log::error('Rest api exception : '.$e->getMessage());
        }
    }

    private function deleteRestSession(string $apiSessionId): void
    {
        try {
            $this->guzzleClient->delete(
                $this->version >= 7 ? 'api/session' : '/rest/com/vmware/cis/session',
                ['headers' => ['vmware-api-session-id' => $apiSessionId],
                ]);
        } catch (\Exception $exception) {
        }

        Cache::forget("vcenter-rest-session-$this->ip");
    }

    private function isSessionExpired(string $datetime): bool
    {
        return Carbon::parse($datetime)->isPast();
    }

    private function createNewGuzzleClient(string $apiSessionId): void
    {
        $this->guzzleClient = new GuzzleClient([
            'verify' => false,
            'base_uri' => $this->ip,
            'headers' => [
                'vmware-api-session-id' => $apiSessionId,
                'content-type' => 'application/json',
            ],
        ]);
    }

    private function initSoapSession(): void
    {
        $sessionInfo = Cache::get("vcenter-soap-session-$this->ip");

        if (! $sessionInfo) {
            $this->createSoapSession();
        } elseif (
            $this->isSessionExpired($sessionInfo['expired_at'])
            || Carbon::parse($sessionInfo['expired_at'])->diffInSeconds(Carbon::now()) < 30
        ) {
            $this->createSoapClientWithExistingSession($sessionInfo['vmware_soap_session']);
            $this->deleteSoapSession();
            $this->createSoapSession();
        } else {
            $this->createSoapClientWithExistingSession($sessionInfo['vmware_soap_session']);
        }
    }

    private function createSoapSession(): void
    {
        try {
            $this->soapClient = new \SoapClient("$this->ip/sdk/vimService.wsdl", [
                'location' => "$this->ip/sdk/",
                'trace' => 1,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            $serviceInstanseMessage['_this'] = new \Soapvar('ServiceInstance', XSD_STRING, 'ServiceInstance');
            $result = $this->soapClient->RetrieveServiceContent($serviceInstanseMessage);
            $serviceContent = $result->returnval;

            $loginMessage = [
                '_this' => $serviceContent->sessionManager,
                'userName' => $this->login,
                'password' => $this->password,
            ];
            $this->soapClient->Login($loginMessage);

            if (array_key_exists('vmware_soap_session', $this->soapClient->__getCookies())) {
                $soapSessionToken = $this->soapClient->__getCookies()['vmware_soap_session'][0];
            } else {
                $responseHeaders = $this->soapClient->__getLastResponseHeaders();

                $string = strstr($responseHeaders, 'vmware_soap_session');
                $string = strstr($string, '"');
                $string = ltrim($string, '"');
                $soapSessionToken = substr($string, 0, strpos($string, '"'));
                $this->soapClient->__setCookie('vmware_soap_session', $soapSessionToken);
            }

            Cache::put("vcenter-soap-session-$this->ip", [
                'vmware_soap_session' => $soapSessionToken,
                'expired_at' => Carbon::now()->addSeconds(config('vmware-php-client.session_ttl') * 60 - 30),
            ]);
        } catch (\Exception $e) {
            Log::error('Soap api exception : '.$e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function createSoapClientWithExistingSession(string $soapSessionToken)
    {
        $this->soapClient = new \SoapClient("$this->ip/sdk/vimService.wsdl", [
            'location' => "$this->ip/sdk/",
            'trace' => 1,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]),
        ]);

        $this->soapClient->__setCookie('vmware_soap_session', $soapSessionToken);
    }

    private function deleteSoapSession()
    {
        try {
            Cache::forget("vcenter-soap-session-$this->ip");

            $sessionManager = new \stdClass();
            $sessionManager->_ = $sessionManager->type = 'SessionManager';

            $soaplogout['_this'] = $sessionManager;
            $this->soapClient->Logout($soaplogout);
        } catch (\Exception $exception) {
            Log::error('Can\'t delete soap session: '.$exception->getMessage());
        }
    }
}
