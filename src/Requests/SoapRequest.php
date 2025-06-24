<?php

namespace Xelon\VmWareClient\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;
use Xelon\VmWareClient\Events\RequestEvent;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapRequest
{
    private int $maxTries = 10;

    private int $tries = 0;

    use SoapTransform;

    /**
     * @throws \Exception
     */
    public function request(string $method, array $requestBody, bool $withXMLAttributes = false, bool $convertToSoap = true): stdClass|array
    {
        try {
            $response = $this->soapClient->$method($convertToSoap ? $this->arrayToSoapVar($requestBody) : $requestBody);

            RequestEvent::dispatch(
                $this->soapClient->__getLastRequest() ?? '',
                $this->soapClient->__getLastResponse() ?? '',
                true
            );

            if (config('vmware-php-client.enable_logs')) {
                Log::info(
                    'SOAP REQUEST SUCCESS:'.
                    "\nSOAP method: ".$method.
                    $this->soapClient->__getLastRequest()
                        ? "\nSOAP request start***".preg_replace('#<password>.+</password>#', '<password>*****</password>', $this->soapClient->__getLastRequest()).'***SOAP request end'
                        : ''
                );
            }

            return ($withXMLAttributes)
                ? $this->parseXMLResponse($this->soapClient->__getLastResponse())
                : $response;
        } catch (\Exception $exception) {
            RequestEvent::dispatch(
                $this->soapClient->__getLastRequest() ?? '',
                $this->soapClient->__getLastResponse() ?? '',
                false
            );

            if ($exception->getMessage() === 'The session is not authenticated.' && $this->tries < $this->maxTries) {
                $this->tries++;
                $sessionInfo = Cache::get("vcenter-soap-session-$this->ip");

                if ($sessionInfo) {
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

                    $this->soapClient->__setCookie('vmware_soap_session', $sessionInfo['vmware_soap_session']);

                    try {
                        Cache::forget("vcenter-soap-session-$this->ip");

                        $sessionManager = new \stdClass();
                        $sessionManager->_ = $sessionManager->type = 'SessionManager';

                        $soaplogout['_this'] = $sessionManager;
                        $this->soapClient->Logout($soaplogout);
                    } catch (\Exception $exception) {
                        Log::error('Can\'t delete soap session: ' . $exception->getMessage());
                    }
                }

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

                return $this->request($method, $requestBody, $withXMLAttributes, $convertToSoap);
            }

            $message = "SOAP REQUEST FAILED:\nMessage: ".$exception->getMessage().
            "\nSOAP method: ".$method.
            (
                $this->soapClient->__getLastRequest()
                    ? "\nSOAP request start***".preg_replace('#<password>.+</password>#', '<password>*****</password>', $this->soapClient->__getLastRequest()).'***SOAP request end'
                    : ''
                ).(
                $this->soapClient->__getLastResponse()
                    ? "\nSOAP response start***: ".$this->soapClient->__getLastResponse().'***SOAP response end'
                    : ''
                );
            // "\nTrace: ".json_encode($exception->getTrace());

            Log::error($message);
            throw new \Exception($message);
        }
    }

    /**
     * @return stdClass
     */
    private function vmRequest(string $method, string $vmId, array $requestBody = [])
    {
        $soapMessage = [
            '_this' => [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ],
        ];
        $soapMessage = array_merge($soapMessage, $requestBody);

        return $this->request($method, $soapMessage);
    }

    /*
     * Totally not stolen from https://stackoverflow.com/a/46349713
     */
    private function parseXMLResponse(string $xmlResponse)
    {
        $previous_value = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xmlResponse);
        libxml_use_internal_errors($previous_value);

        if (libxml_get_errors()) {
            return [];
        }

        return $this->domToArray($dom);
    }

    private function domToArray($root) {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if (in_array($child->nodeType,[XML_TEXT_NODE,XML_CDATA_SECTION_NODE])) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }

            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->domToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->domToArray($child);
                }
            }
        }

        return $result;
    }
}
