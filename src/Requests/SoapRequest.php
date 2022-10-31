<?php

namespace Xelon\VmWareClient\Requests;

use Illuminate\Support\Facades\Log;
use stdClass;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapRequest
{
    use SoapTransform;

    /**
     * @param  string  $method
     * @param  array  $requestBody
     * @param  bool  $convertToSoap
     * @return stdClass
     */
    public function request(string $method, array $requestBody, bool $convertToSoap = true)
    {
        try {
            $response = $this->soapClient->$method($convertToSoap ? $this->arrayToSoapVar($requestBody) : $requestBody);

            if (config('vmware-php-client.enable_logs')) {
                Log::info(
                    'SOAP REQUEST SUCCESS:'.
                    "\nSOAP method: ".$method.
                    property_exists($this->soapClient, '__last_request')
                        ? "\nSOAP request start***".json_encode(simplexml_load_string($this->soapClient->__last_request))."***SOAP request end"
                        : ''
                );
            }

            return $response;
        } catch (\Exception $exception) {
            $message = "SOAP REQUEST FAILED:\nMessage: ".$exception->getMessage().
            "\nSOAP method: ".$method.
            (
                property_exists($this->soapClient, '__last_request')
                    ? "\nSOAP request start***".json_encode(simplexml_load_string($this->soapClient->__last_request))."***SOAP request end"
                    : ''
            ).(
                property_exists($this->soapClient, '__last_request')
                    ? "\nSOAP response start***: ".json_encode(simplexml_load_string($this->soapClient->__last_response))."***SOAP response end"
                    : ''
            ).
                "\nTrace: ".json_encode($exception->getTrace());

            Log::error($message);
            throw new \Exception($message);
        }
    }

    /**
     * @param  string  $method
     * @param  string  $vmId
     * @param  array  $requestBody
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
