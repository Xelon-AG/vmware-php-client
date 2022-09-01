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
        ?string $scriptPath,
        string $guestFilePath,
        array $params = [],
        string $script = null
    ): void {
        if ($scriptPath) {
            $fullScriptPath = base_path($scriptPath);
            $script = file_get_contents($fullScriptPath);
        }

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $script = str_replace('{'.$key.'}', $value, $script);
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
                'interactiveSession' => true,
                'username' => $username,
                'password' => $password,
            ],
            'guestFilePath' => "{$guestFilePath}",
            'fileAttributes' => new \stdClass(),
            'fileSize' => strlen($script),
            'overwrite' => true,
        ];

        $response = $this->soapClient->InitiateFileTransferToGuest($this->arrayToSoapVar($body));

        $client = new GuzzleClient(['verify' => false]);

        try {
            if (is_string($response->returnval) && substr($response->returnval, 0, 4) !== 'http') {
                throw new Exception('File transfer invalid response url');
            }
            $client->request('PUT', $response->returnval, ['body' => $script]);
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
}
