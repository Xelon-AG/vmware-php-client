<?php

namespace Xelon\VmWareClient\Requests;

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
     * @return stdClass
     */
    public function request(string $method, array $requestBody, bool $convertToSoap = true)
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
                        ? "\nSOAP request start***".$this->soapClient->__getLastRequest().'***SOAP request end'
                        : ''
                );
            }

            return $response;
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

                    return $this->request($method, $requestBody, $convertToSoap);
                }
            }

            $message = "SOAP REQUEST FAILED:\nMessage: ".$exception->getMessage().
                "\nSOAP method: ".$method.
                (
                $this->soapClient->__getLastRequest()
                    ? "\nSOAP request start***".$this->soapClient->__getLastRequest().'***SOAP request end'
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
}
