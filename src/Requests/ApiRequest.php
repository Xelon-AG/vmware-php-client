<?php

namespace Xelon\VmWareClient\Requests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

trait ApiRequest
{
    private function request(string $method, string $uri, array $options = [])
    {
        try {
            return json_decode($this->guzzleClient->request($method, $uri, $options)->getBody());
        } catch (ConnectException $e) {
            Log::error('Rest api Connect exception: '.$e->getMessage());
        } catch (ServerException $e) {
            Log::error('Rest api server exception: '.$e->getMessage());
        } catch (RequestException $e) {
            Log::error('Rest api Request exception: '.$e->getMessage());

            return [
                'isError' => true,
                'code' => $e->getCode(),
                'info' => json_decode($e->getResponse()->getBody()->getContents()),
            ];
        } catch (ClientException $e) {
            // if 401, create new session and reply attempt
        } catch (\Exception $e) {
            Log::error('Rest api exception : '.$e->getMessage());
        }
    }
}
