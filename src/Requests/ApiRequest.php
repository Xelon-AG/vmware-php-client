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
            $result = json_decode($this->guzzleClient->request($method, $uri, $options)->getBody());

            if ($this->version < 7 && isset($result->value)) {
                return $result->value;
            }

            return $result;
        } catch (ConnectException $e) {
            Log::error('Rest api Connect exception: '.$e->getMessage());
        } catch (ServerException $e) {
            Log::error('Rest api server exception: '.$e->getMessage());
        } catch (RequestException $e) {
            Log::error('Rest api Request exception: '.$e->getMessage());

            return [
                'isError' => true,
                'code' => $e->getCode(),
                'info' => $this->transformErrorInfo(json_decode($e->getResponse()->getBody()->getContents(), true)),
            ];
        } catch (ClientException $e) {
            // if 401, create new session and reply attempt
        } catch (\Exception $e) {
            Log::error('Rest api exception : '.$e->getMessage());
        }
    }

    private function transformErrorInfo(array $info)
    {
        if ($this->version < 7) {
            $info['messages'] = $info['value']['messages'];
        } elseif (count($info['messages']) === 0) {
            $info['messages'][0]['default_message'] = $info['error_type'];
        }

        return $info;
    }
}
