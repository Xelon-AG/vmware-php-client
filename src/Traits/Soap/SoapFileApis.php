<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapFileApis
{
    use SoapRequest;
    use SoapTransform;

    public function initiateFileTransferToGuest(
        string $username,
        string $password,
        string $vmId,
        ?string $localFilePath,
        string $guestFilePath,
        array $params = [],
        string $data = null
    ): void {
        if ($localFilePath) {
            $fullScriptPath = base_path($localFilePath);
            $data = file_get_contents($fullScriptPath);
        }

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $data = str_replace('{'.$key.'}', $value, $data);
            }
        }

        $body = [
            '_this' => [
                'type' => 'GuestFileManager',
                '_' => 'guestOperationsFileManager',
            ],
            'vm' => [
                'type' => 'VirtualMachine',
                '_' => $vmId,
            ],
            'auth' => [
                '@type' => 'NamePasswordAuthentication',
                'interactiveSession' => false,
                'username' => $username,
                'password' => $password,
            ],
            'guestFilePath' => "{$guestFilePath}",
            'fileAttributes' => new \stdClass(),
            'fileSize' => strlen($data),
            'overwrite' => true,
        ];

        $response = $this->soapClient->InitiateFileTransferToGuest($this->arrayToSoapVar($body));

        $client = new GuzzleClient(['verify' => false]);

        try {
            if (is_string($response->returnval) && substr($response->returnval, 0, 4) !== 'http') {
                throw new Exception('File transfer invalid response url');
            }
            $client->request('PUT', $response->returnval, ['body' => $data]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createTemporaryDirectoryInGuest(
        string $username,
        string $password,
        string $vmId,
        string $directoryPath,
        string $prefix = '',
        string $sufix = ''
    ) {
        $body = [
            '_this' => [
                'type' => 'GuestFileManager',
                '_' => 'guestOperationsFileManager',
            ],
            'vm' => [
                'type' => 'VirtualMachine',
                '_' => $vmId,
            ],
            'auth' => [
                '@type' => 'NamePasswordAuthentication',
                'interactiveSession' => false,
                'username' => $username,
                'password' => $password,
            ],
            'prefix' => $prefix,
            'suffix' => $sufix,
            'directoryPath' => $directoryPath,
        ];

        return $this->soapClient->CreateTemporaryDirectoryInGuest($this->arrayToSoapVar($body));
    }

    public function deleteDirectoryInGuest(
        string $username,
        string $password,
        string $vmId,
        string $directoryPath
    ) {
        $body = [
            '_this' => [
                'type' => 'GuestFileManager',
                '_' => 'guestOperationsFileManager',
            ],
            'vm' => [
                'type' => 'VirtualMachine',
                '_' => $vmId,
            ],
            'auth' => [
                '@type' => 'NamePasswordAuthentication',
                'interactiveSession' => false,
                'username' => $username,
                'password' => $password,
            ],
            'directoryPath' => $directoryPath,
            'recursive' => true,
        ];

        return $this->soapClient->DeleteDirectoryInGuest($this->arrayToSoapVar($body));
    }

    public function startProgramInGuest(
        string $username,
        string $password,
        string $vmId,
        string $filePath,
        string $program = '/bin/bash'
    ) {
        $body = [
            '_this' => [
                'type' => 'GuestProcessManager',
                '_' => 'guestOperationsProcessManager',
            ],
            'vm' => [
                'type' => 'VirtualMachine',
                '_' => $vmId,
            ],
            'auth' => [
                '@type' => 'NamePasswordAuthentication',
                'interactiveSession' => false,
                'username' => $username,
                'password' => $password,
            ],
            'spec' => [
                'programPath' => $program,
                'arguments' => "{$filePath}",
            ],
        ];

        return $this->soapClient->StartProgramInGuest($this->arrayToSoapVar($body));
    }

    public function createTaskCollectorForVm(string $vmId)
    {
        $body = [
            '_this' => [
                '_' => 'TaskManager',
                'type' => 'TaskManager',
            ],
            'filter' => [
                'entity' => [
                    'entity' => [
                        'type' => 'VirtualMachine',
                        '_' => $vmId,
                    ],
                    'recursion' => 'self',
                ]
            ]
        ];

        return $this->soapClient->CreateCollectorForTasks($body);
    }

    public function destroyTaskCollector(string $taskCollectorId)
    {
        $body = [
          '_this' => [
              'type' => 'HistoryCollector',
              '_' => $taskCollectorId
          ]
        ];

        return $this->soapClient->DestroyCollector($body);
    }
}
