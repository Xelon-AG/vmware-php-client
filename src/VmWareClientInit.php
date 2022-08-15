<?php

namespace Xelon\VmWareClient;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

class VmWareClientInit
{
    public const MODE_REST = 'rest';

    public const MODE_SOAP = 'soap';

    public const MODE_BOTH = 'both';

    private string $ip;

    private string $login;

    private string $password;

    protected ?GuzzleClient $guzzleClient;

    public ?\SoapClient $soapClient;

    public function __construct(string $ip, string $login, string $password, string $mode = self::MODE_REST)
    {
        $this->ip = $ip;
        $this->login = $login;
        $this->password = $password;

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

        if (!$sessionInfo) {
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
            $authReponse = $this->guzzleClient->post('/api/session', ['auth' => [$this->login, $this->password]]);
            $apiSessionId = json_decode($authReponse->getBody());

            Cache::add("vcenter-rest-session-$this->ip", [
                'api_session_id' => $apiSessionId,
                'expired_at' => Carbon::now()->addSeconds(config('vmware-php-client.session_ttl') * 60 - 30)
            ]);

            $this->createNewGuzzleClient($apiSessionId);
        } catch (ConnectException $e) {
            Log::error('Rest api Connect exception: ' . $e->getMessage());
        } catch (ServerException $e) {
            Log::error('Rest api server exception: ' . $e->getMessage());
        } catch (RequestException $e) {
            Log::error('Rest api Request exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Rest api exception : ' . $e->getMessage());
        }
    }

    private function deleteRestSession(string $apiSessionId): void
    {
        try {
            $this->guzzleClient->delete('api/session', [
                'headers' => [
                    'vmware-api-session-id' => $apiSessionId,
                ]
            ]);
        } catch (\Exception $exception) {}

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
                'content-type' => 'application/json'
            ]
        ]);
    }

    private function initSoapSession(): void
    {
        $sessionInfo = Cache::get("vcenter-soap-session-$this->ip");

        if (!$sessionInfo) {
            $this->createSoapSession();
        } elseif ($this->isSessionExpired($sessionInfo['expired_at'])) {
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
                        'allow_self_signed' => true
                    ]
                ])
            ]);

            $serviceInstanseMessage['_this'] = new \Soapvar('ServiceInstance', XSD_STRING, 'ServiceInstance');
            $result = $this->soapClient->RetrieveServiceContent($serviceInstanseMessage);
            $serviceContent = $result->returnval;

            $loginMessage = [
                '_this' => $serviceContent->sessionManager,
                'userName' => $this->login,
                'password' => $this->password
            ];
            $this->soapClient->Login($loginMessage);

            /*Cache::add("vcenter-soap-session-$this->ip", [
                'vmware_soap_session' => $this->soapClient->_cookies['vmware_soap_session'][0],
                'expired_at' => Carbon::now()->addSeconds(config('vmware-php-client.session_ttl') * 60 - 30)
            ]);*/
        } catch (\Exception $e) {
            Log::error('Soap api exception : ' . $e->getMessage());
        }
    }

    private function createSoapClientWithExistingSession(string $soapSessionToken)
    {
        $this->soapClient = new \SoapClient("$this->ip/sdk/vimService.wsdl", [
            'location' => "$this->ip/sdk/",
            'encoding' => 'UTF-8' ,
            //'cache_wsdl' => WSDL_CACHE_MEMORY,
            //'compression'=> SOAP_COMPRESSION_ACCEPT|SOAP_COMPRESSION_GZIP,
            //'soap_version'=>SOAP_1_2,
            //'keep_alive'=>true,
            'exceptions'=>true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'trace'=>1,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ])
        ]);

        $this->soapClient->__setCookie('vmware_soap_session', $soapSessionToken);
    }

    private function deleteSoapSession()
    {
        $sessionManager = new \stdClass();
        $sessionManager->_ = $sessionManager->type = 'SessionManager';

        $soaplogout['_this'] = $sessionManager;
        $this->soapClient->Logout($soaplogout);

        Cache::forget("vcenter-soap-session-$this->ip");
    }
}
