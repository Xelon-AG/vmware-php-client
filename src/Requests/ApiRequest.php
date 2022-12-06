<?php

namespace Xelon\VmWareClient\Requests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ApiRequest
{
    private int $maxTries = 10;

    private int $tries = 0;

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

            $code = $e->getCode();

            if ($code === 401 && $this->tries < $this->maxTries) {
                $this->tries++;
                $sessionInfo = Cache::get("vcenter-rest-session-$this->ip");

                if ($sessionInfo) {
                    $this->guzzleClient = new GuzzleClient([
                        'verify' => false,
                        'base_uri' => $this->ip,
                        'headers' => [
                            'vmware-api-session-id' => $sessionInfo['api_session_id'],
                            'content-type' => 'application/json',
                        ],
                    ]);
                    return $this->request($method, $uri, $options);
                }
            }

            return [
                'isError' => true,
                'code' => $code,
                'info' => $this->transformErrorInfo(json_decode($e->getResponse()->getBody()->getContents(), true)),
            ];
        } catch (ClientException $e) {
            // if 401, create new session and reply attempt
        } catch (\Exception $e) {
            Log::error('Rest api exception: '.$e->getMessage());
        }
    }

    private function transformErrorInfo(array $info)
    {
        if ($this->version < 7) {
            if (isset($info['value']['messages'])) {
                $info['messages'] = $info['value']['messages'];
            } elseif (isset($info['localizableMessages'])) {
                $info['messages'] = $info['localizableMessages'];
            }
        } elseif (count($info['messages']) === 0) {
            $info['messages'][0]['default_message'] = $info['error_type'];
        }

        return $info;
    }
}
