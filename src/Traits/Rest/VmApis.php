<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait VmApis
{
    use ApiRequest;

    public function getHardware()
    {
        // TODO:
    }

    public function updateHardware()
    {
        // TODO:
    }

    public function upgradeHardware()
    {
        // TODO:
    }

    public function getLibraryItem()
    {
        // TODO:
    }

    public function getPower()
    {
        // TODO:
    }

    public function resetPower(string $vmId)
    {
        return $this->request(
            'post',
            $this->version >= 7 ? "/api/vcenter/vm/$vmId/power?action=reset" : "/rest/vcenter/vm/$vmId/power/reset"
        );
    }

    public function startPower(string $vmId)
    {
        return $this->request(
            'post',
            $this->version >= 7 ? "/api/vcenter/vm/$vmId/power?action=start" : "/rest/vcenter/vm/$vmId/power/start"
        );
    }

    public function stopPower(string $vmId)
    {
        $a = 5;

        return $this->request(
            'post',
            $this->version >= 7 ? "/api/vcenter/vm/$vmId/power?action=stop" : "/rest/vcenter/vm/$vmId/power/stop"
        );
    }

    public function suspendPower(string $vmId)
    {
        return $this->request(
            'post',
            $this->version >= 7 ? "/api/vcenter/vm/$vmId/power?action=suspend" : "/rest/vcenter/vm/$vmId/power/suspend"
        );
    }

    public function getTools()
    {
        // TODO:
    }

    public function updateTools()
    {
        // TODO:
    }

    public function upgradeTools()
    {
        // TODO:
    }

    public function getComputePolicies()
    {
        // TODO:
    }

    public function createConsoleTickets()
    {
        // TODO:
    }

    public function setGuestCustomization()
    {
        // TODO:
    }

    public function getGuestCustomization()
    {
        // TODO:
    }

    public function getGuestEnvironment()
    {
        // TODO:
    }

    public function listGuestEnvironment()
    {
        // TODO:
    }

    public function getGuestIdentity()
    {
        // TODO:
    }

    public function getGuestLocalFilesystem()
    {
        // TODO:
    }

    public function getGuestNetworking()
    {
        // TODO:
    }

    public function getGuestOperations()
    {
        // TODO:
    }

    public function getGuestPower()
    {
        // TODO:
    }

    public function rebootGuestPower()
    {
        // TODO:
    }

    public function shutdownGuestPower(string $vmId)
    {
        return $this->request('post', "$this->apiUrlPrefix/vcenter/vm/$vmId/guest/power?action=shutdown");
    }

    public function standbyGuestPower()
    {
        // TODO:
    }

    public function deleteGuestProcesses()
    {
        // TODO:
    }

    public function getGuestProcesses()
    {
        // TODO:
    }

    public function createGuestProcesses()
    {
        // TODO:
    }

    public function listGuestProcesses()
    {
        // TODO:
    }

    public function createGuestFilesystemDirectories()
    {
        // TODO:
    }

    public function createTemporaryGuestFilesystemDirectories()
    {
        // TODO:
    }

    public function deleteGuestFilesystemDirectories()
    {
        // TODO:
    }

    public function moveGuestFilesystemDirectories()
    {
        // TODO:
    }

    public function deleteGuestFilesystemFiles()
    {
        // TODO:
    }

    public function getGuestFilesystemFiles()
    {
        // TODO:
    }

    public function createTemporaryGuestFilesystemFiles()
    {
        // TODO:
    }

    public function listGuestFilesystemFiles()
    {
        // TODO:
    }

    public function updateGuestFilesystemFiles()
    {
        // TODO:
    }

    public function createGuestFilesystemTransfers()
    {
        // TODO:
    }

    public function listGuestNetworkingInterfaces()
    {
        // TODO:
    }

    public function listGuestNetworkingRoutes()
    {
        // TODO:
    }

    public function updateHardwareBoot()
    {
        // TODO:
    }

    public function getHardwareBoot()
    {
        // TODO:
    }

    public function listHardwareCdRom()
    {
        // TODO:
    }

    public function createHardwareCdRom()
    {
        // TODO:
    }

    public function getHardwareCdRom()
    {
        // TODO:
    }

    public function updateHardwareCdRom()
    {
        // TODO:
    }

    public function deleteHardwareCdRom()
    {
        // TODO:
    }

    public function connectHardwareCdRom()
    {
        // TODO:
    }

    public function disconnectHardwareCdRom()
    {
        // TODO:
    }

    public function getHardwareCpu()
    {
        // TODO:
    }

    public function updateHardwareCpu(
        string $vmId,
        int $coresPerSocket,
        int $count,
        bool $hotAddEnabled = false,
        bool $hotRemoveEnabled = false
    ) {
        $requestBody = [
            'cores_per_socket' => $coresPerSocket,
            'count' => $count,
            'hot_add_enabled' => $hotAddEnabled,
            'hot_remove_enabled' => $hotRemoveEnabled,
        ];

        if ($this->version < 7) {
            $requestBody = ['spec' => $requestBody];
        }

        return $this->request(
            'patch',
            "$this->apiUrlPrefix/vcenter/vm/$vmId/hardware/cpu",
            ['json' => $requestBody]);
    }

    public function listHardwareDisk()
    {
        // TODO:
    }

    public function createHardwareDisk(string $vmId, array $body)
    {
        return $this->request('post', "$this->apiUrlPrefix/vcenter/vm/$vmId/hardware/disk", ['json' => $body]);
    }

    public function getHardwareDisk()
    {
        // TODO:
    }

    public function updateHardwareDisk()
    {
        // TODO:
    }

    public function deleteHardwareDisk(string $vmId, int $diskKey)
    {
        return $this->request('delete', "$this->apiUrlPrefix/vcenter/vm/$vmId/hardware/disk/$diskKey");
    }

    public function listHardwareEthernet()
    {
        // TODO:
    }

    public function createHardwareEthernet()
    {
        // TODO:
    }

    public function getHardwareEthernet()
    {
        // TODO:
    }

    public function updateHardwareEthernet()
    {
        // TODO:
    }

    public function deleteHardwareEthernet(string $vmId, int $nicKey)
    {
        return $this->request('delete', "$this->apiUrlPrefix/vcenter/vm/$vmId/hardware/ethernet/$nicKey");
    }

    public function connectHardwareEthernet()
    {
        // TODO:
    }

    public function disconnectHardwareEthernet()
    {
        // TODO:
    }

    public function listHardwareFloppy()
    {
        // TODO:
    }

    public function createHardwareFloppy()
    {
        // TODO:
    }

    public function getHardwareFloppy()
    {
        // TODO:
    }

    public function updateHardwareFloppy()
    {
        // TODO:
    }

    public function deleteHardwareFloppy()
    {
        // TODO:
    }

    public function connectHardwareFloppy()
    {
        // TODO:
    }

    public function disconnectHardwareFloppy()
    {
        // TODO:
    }

    public function getHardwareMemory()
    {
        // TODO:
    }

    public function updateHardwareMemory(string $vmId, int $size, bool $hotAddEnabled = false)
    {
        $requestBody = ['hot_add_enabled' => $hotAddEnabled, 'size_MiB' => $size];

        if ($this->version < 7) {
            $requestBody = ['spec' => $requestBody];
        }

        return $this->request(
            'patch',
            "$this->apiUrlPrefix/vcenter/vm/$vmId/hardware/memory",
            ['json' => $requestBody]
        );
    }

    public function listHardwareParallel()
    {
        // TODO:
    }

    public function createHardwareParallel()
    {
        // TODO:
    }

    public function getHardwareParallel()
    {
        // TODO:
    }

    public function updateHardwareParallel()
    {
        // TODO:
    }

    public function deleteHardwareParallel()
    {
        // TODO:
    }

    public function connectHardwareParallel()
    {
        // TODO:
    }

    public function disconnectHardwareParallel()
    {
        // TODO:
    }

    public function listHardwareSerial()
    {
        // TODO:
    }

    public function createHardwareSerial()
    {
        // TODO:
    }

    public function getHardwareSerial()
    {
        // TODO:
    }

    public function updateHardwareSerial()
    {
        // TODO:
    }

    public function deleteHardwareSerial()
    {
        // TODO:
    }

    public function connectHardwareSerial()
    {
        // TODO:
    }

    public function disconnectHardwareSerial()
    {
        // TODO:
    }

    public function listHardwareAdapterNvme()
    {
        // TODO:
    }

    public function createHardwareAdapterNvme()
    {
        // TODO:
    }

    public function getHardwareAdapterNvme()
    {
        // TODO:
    }

    public function deleteHardwareAdapterNvme()
    {
        // TODO:
    }

    public function listHardwareAdapterSata()
    {
        // TODO:
    }

    public function createHardwareAdapterSata()
    {
        // TODO:
    }

    public function getHardwareAdapterSata()
    {
        // TODO:
    }

    public function deleteHardwareAdapterSata()
    {
        // TODO:
    }

    public function listHardwareAdapterScsi()
    {
        // TODO:
    }

    public function createHardwareAdapterScsi()
    {
        // TODO:
    }

    public function deleteHardwareAdapterScsi()
    {
        // TODO:
    }

    public function getHardwareAdapterScsi()
    {
        // TODO:
    }

    public function updateHardwareAdapterScsi()
    {
        // TODO:
    }

    public function getHardwareBootDevice()
    {
        // TODO:
    }

    public function setHardwareBootDevice()
    {
        // TODO:
    }

    public function getStoragePolicy()
    {
        // TODO:
    }

    public function updateStoragePolicy()
    {
        // TODO:
    }

    public function getStoragePolicyCompliance()
    {
        // TODO:
    }

    public function checkStoragePolicyCompliance()
    {
        // TODO:
    }

    public function getToolsInstaller()
    {
        // TODO:
    }

    public function connectToolsInstaller()
    {
        // TODO:
    }

    public function disconnectToolsInstaller()
    {
        // TODO:
    }
}
