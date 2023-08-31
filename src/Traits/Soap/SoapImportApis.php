<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;
use Xelon\VmWareClient\Types\OvfConsumerOstNode;
use Xelon\VmWareClient\Types\OvfCreateImportSpecParams;
use Xelon\VmWareClient\Types\OvfParseDescriptorParams;
use Xelon\VmWareClient\Types\ToolsConfigInfo;
use Xelon\VmWareClient\Types\VAppEntityConfigInfo;
use Xelon\VmWareClient\Types\VirtualMachineConfigSpec;
use Xelon\VmWareClient\Types\VirtualMachineFileInfo;
use Xelon\VmWareClient\Types\VirtualMachineFlagInfo;
use Xelon\VmWareClient\Types\VirtualMachineImportSpec;

trait SoapImportApis
{
    use SoapRequest;
    use SoapTransform;
    use SoapVmApis;

    public function parseDescriptor(string $ovfPath)
    {
        $body = [
            '_this' => [
                '_' => 'OvfManager',
                'type' => 'OvfManager',
            ],
            'ovfDescriptor' => file_get_contents($ovfPath),
            'pdp' => new OvfParseDescriptorParams([
                'locale' => '',
                'deploymentOption' => '',
            ]),
        ];
        return $this->request('ParseDescriptor', $body);
    }

    public function createImportSpec(string $ovfPath, string $resourcepool, string $datastore, array $networkMapping = null)
    {
        $body = [
            '_this' => [
                '_' => 'OvfManager',
                'type' => 'OvfManager',
            ],
            'ovfDescriptor' => file_get_contents($ovfPath),
            'resourcePool' => $resourcepool,
            'datastore' => $datastore,
            'cisp' => new OvfCreateImportSpecParams([
                'locale' => '',
                'entityName' => '',
                'deploymentOption' => '',
                'networkMapping' => $networkMapping,
            ]),
        ];

        return $this->request('CreateImportSpec', $body, true);
    }

    public function importVApp(string $resourcePoolId, $entityConfig, $instantiationOst, $configSpec, $folder = null, $host = null)
    {
        $extraConfig = [];
        if (isset($configSpec->extraConfig)) {
            // sometimes there is only one level of nesting
            if (property_exists($configSpec->extraConfig, 'key')) {
                $extraConfig['key'] = $configSpec->extraConfig->key;
                $extraConfig['value:string'] = $configSpec->extraConfig->value->_value ?? '';
                // but sometimes there are several
            } else {
                foreach ($configSpec->extraConfig as $i => $config) {
                    $extraConfig[$i]['key'] = $config->key;
                    $extraConfig[$i]['value:string'] = $config->value->_value ?? '';
                }
            }
        }

        // devices may vary from one VM to another, therefore we can not explicitly set those params manually
        $deviceChange = [];
        if (isset($configSpec->deviceChange) && \count($configSpec->deviceChange) > 0) {
            foreach ($configSpec->deviceChange as $i => $deviceObj) {
                $deviceConfig = json_decode(json_encode($deviceObj), true);
                $deviceChange[$i] = $this->build($deviceConfig, []);
            }
        }

        $body = [
            '_this' => [
                '_' => $resourcePoolId,
                'type' => 'ResourcePool',
            ],
            'spec' => new VirtualMachineImportSpec([
                'entityConfig' => new VAppEntityConfigInfo([
                    'key' => $entityConfig->key ?? null,
                    'tag' => $entityConfig->tag ?? null,
                    'startOrder' => $entityConfig->startOrder ?? null,
                    'startDelay' => $entityConfig->startDelay ?? null,
                    'waitingForGuest' => $entityConfig->waitingForGuest ?? null,
                    'startAction' => $entityConfig->startAction ?? null,
                    'stopDelay' => $entityConfig->stopDelay ?? null,
                    'stopAction' => $entityConfig->stopAction ?? null,
                    'destroyWithParent' => $entityConfig->destroyWithParent ?? null,
                ]),
                'instantiationOst' => new OvfConsumerOstNode([
                    'id' => $instantiationOst->id ?? null,
                    'type' => $instantiationOst->type ?? null,
                    'section' => $instantiationOst->section ?? null,
                    'child' => $instantiationOst->child ? [
                        'id' => $instantiationOst->child->id ?? null,
                        'type' => $instantiationOst->child->type ?? null,
                    ] : null,
                    'entity' => $instantiationOst->entity ?? null,
                ]),
                'configSpec' => new VirtualMachineConfigSpec([
                    'name' => $configSpec->name ?? null,
                    'version' => $configSpec->version ?? null,
                    'uuid' => $configSpec->uuid ?? null,
                    'guestId' => $configSpec->guestId ?? null,
                    'files' => $configSpec->files ? new VirtualMachineFileInfo([
                        'vmPathName' => $configSpec->files->vmPathName ?? null,
                        'snapshotDirectory' => $configSpec->files->snapshotDirectory ?? null,
                        'suspendDirectory' => $configSpec->files->suspendDirectory ?? null,
                        'logDirectory' => $configSpec->files->logDirectory ?? null,
                        'ftMetadataDirectory' => $configSpec->files->ftMetadataDirectory ?? null,
                    ]) : null,
                    'tools' => $configSpec->tools ? new ToolsConfigInfo([
                        'toolsVersion' => $configSpec->tools->toolsVersion ?? null,
                        'toolsInstallType' => $configSpec->tools->toolsInstallType ?? null,
                        'afterPowerOn' => $configSpec->tools->afterPowerOn ?? null,
                        'afterResume' => $configSpec->tools->afterResume ?? null,
                        'beforeGuestStandby' => $configSpec->tools->beforeGuestStandby ?? null,
                        'beforeGuestShutdown' => $configSpec->tools->beforeGuestShutdown ?? null,
                        'beforeGuestReboot' => $configSpec->tools->beforeGuestReboot ?? null,
                        'toolsUpgradePolicy' => $configSpec->tools->toolsUpgradePolicy ?? null,
                        'pendingCustomization' => $configSpec->tools->pendingCustomization ?? null,
                        'customizationKeyId' => $configSpec->tools->customizationKeyId ?? null,
                        'syncTimeWithHostAllowed' => $configSpec->tools->syncTimeWithHostAllowed ?? null,
                        'syncTimeWithHost' => $configSpec->tools->syncTimeWithHost ?? null,
                        'lastInstallInfo' => $configSpec->tools->lastInstallInfo ?? null,

                    ]) : null,
                    'flags' => $configSpec->flags ? new VirtualMachineFlagInfo([
                        'disableAcceleration' => $configSpec->flags->disableAcceleration ?? null,
                        'enableLogging' => $configSpec->flags->enableLogging ?? null,
                        'useToe' => $configSpec->flags->useToe ?? null,
                        'runWithDebugInfo' => $configSpec->flags->runWithDebugInfo ?? null,
                        'monitorType' => $configSpec->flags->monitorType ?? null,
                        'htSharing' => $configSpec->flags->htSharing ?? null,
                        'snapshotDisabled' => $configSpec->flags->snapshotDisabled ?? null,
                        'snapshotLocked' => $configSpec->flags->snapshotLocked ?? null,
                        'diskUuidEnabled' => $configSpec->flags->diskUuidEnabled ?? null,
                        'virtualMmuUsage' => $configSpec->flags->virtualMmuUsage ?? null,
                        'virtualExecUsage' => $configSpec->flags->virtualExecUsage ?? null,
                        'snapshotPowerOffBehavior' => $configSpec->flags->snapshotPowerOffBehavior ?? null,
                        'recordReplayEnabled' => $configSpec->flags->recordReplayEnabled ?? null,
                        'faultToleranceType' => $configSpec->flags->faultToleranceType ?? null,
                        'cbrcCacheEnabled' => $configSpec->flags->cbrcCacheEnabled ?? null,
                        'vvtdEnabled' => $configSpec->flags->vvtdEnabled ?? null,
                        'vbsEnabled' => $configSpec->flags->vbsEnabled ?? null,
                    ]) : null,
                    'numCPUs' => $configSpec->numCPUs ?? null,
                    'numCoresPerSocket' => $configSpec->numCoresPerSocket ?? null,
                    'memoryMB' => $configSpec->memoryMB ?? null,
                    'memoryHotAddEnabled' => $configSpec->memoryHotAddEnabled ?? null,
                    'cpuHotAddEnabled' => $configSpec->cpuHotAddEnabled ?? null,
                    'cpuHotRemoveEnabled' => $configSpec->cpuHotRemoveEnabled ?? null,
                    'virtualICH7MPresent' => $configSpec->virtualICH7MPresent ?? null,
                    'virtualSMCPresent' => $configSpec->virtualSMCPresent ?? null,
                    'deviceChange' => $deviceChange,
                    'memoryAllocation' => isset($configSpec->memoryAllocation) ? [
                        'reservation' =>  $configSpec->memoryAllocation->reservation ?? null,
                        'expandableReservation' => $configSpec->memoryAllocation->expandableReservation ?? null,
                        'limit' => $configSpec->memoryAllocation->limit ?? null,
                        'shares' => $configSpec->memoryAllocation->shares ? [
                            'shares' => $configSpec->memoryAllocation->shares->shares ?? null,
                            'level' => $configSpec->memoryAllocation->shares->level ?? null,
                        ] : null,
                    ] : null,
                    'extraConfig' => \count($extraConfig) > 0 ? $extraConfig : null,
                    'bootOptions' => $configSpec->bootOptions ? [
                        'bootDelay' => $configSpec->bootOptions->bootDelay ?? null,
                        'enterBIOSSetup' => $configSpec->bootOptions->enterBIOSSetup ?? null,
                        'efiSecureBootEnabled' => $configSpec->bootOptions->efiSecureBootEnabled,
                        'bootRetryEnabled' => $configSpec->bootOptions->bootRetryEnabled ?? null,
                        'bootRetryDelay' => $configSpec->bootOptions->bootRetryDelay ?? null,
                        'bootOrder' => $configSpec->bootOptions->bootOrder ?? null,
                        'networkBootProtocol' => $configSpec->bootOptions->networkBootProtocol ?? null,
                    ] : null,
                    'vAppConfig' => [
                        'product' => $configSpec->vAppConfig->product ? [
                            'operation' => $configSpec->vAppConfig->product->operation ?? null,
                            'info' => $configSpec->vAppConfig->product->info ? [
                                'key' => $configSpec->vAppConfig->product->info->key ?? null,
                                'classId' => $configSpec->vAppConfig->product->info->classId ?? null,
                                'instanceId' => $configSpec->vAppConfig->product->info->instanceId ?? null,
                                'name' => $configSpec->vAppConfig->product->info->name ?? null,
                                'vendor' => $configSpec->vAppConfig->product->info->vendor ?? null,
                                'version' => $configSpec->vAppConfig->product->info->version ?? null,
                                'fullVersion' => $configSpec->vAppConfig->product->info->fullVersion ?? null,
                                'vendorUrl' => $configSpec->vAppConfig->product->info->vendorUrl ?? null,
                                'productUrl' => $configSpec->vAppConfig->product->info->productUrl ?? null,
                                'appUrl' => $configSpec->vAppConfig->product->info->appUrl ?? null,
                            ] : null,
                        ] : null,
                        'installBootRequired' => $configSpec->vAppConfig->installBootRequired ?? null,
                        'installBootStopDelay' => $configSpec->vAppConfig->installBootStopDelay ?? null,
                    ],
                    'firmware' => $configSpec->firmware ?? null,
                    'nestedHVEnabled' => $configSpec->nestedHVEnabled ?? null,
                    'sgxInfo' => isset($configSpec->sgxInfo) ? [
                        'epcSize' => $configSpec->sgxInfo->epcSize ?? null,
                        'flcMode' => $configSpec->sgxInfo->flcMode ?? null,
                        'lePubKeyHash' => $configSpec->sgxInfo->lePubKeyHash ?? null,
                    ] : null,
                    'sevEnabled' => $configSpec->sevEnabled ?? null,
                ]),
            ]),
            'folder' => $folder,
            'host' => $host,
        ];

        return $this->request('ImportVApp', $body);
    }

    private function build($data, $arrayToBuild, $object = null)
    {
        if ($object) {
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $object->$key = $value;
                } else if (is_array($value) && array_key_exists('@attributes', $value)) {
                    $className = "Xelon\\VmWareClient\\Types\\".$value['@attributes']['type'];
                    $newObj = (new $className);
                    unset($value['@attributes']);

                    $object->$key = $this->build($value, [], $newObj);
                } else if (is_array($value)) {
                    $object->$key = $this->build($value, []);
                }
            }

            return $object;
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $arrayToBuild[$key] = $value;
            } else if (is_array($value) && array_key_exists('@attributes', $value)) {
                $className = "Xelon\\VmWareClient\\Types\\".$value['@attributes']['type'];
                $newObj = (new $className);
                unset($value['@attributes']);

                $arrayToBuild[$key] = $this->build($value, [], $newObj);
            } else if (is_array($value)) {
                $arrayToBuild[$key] = $this->build($value, []);
            }
        }

        return $arrayToBuild;
    }

    public function httpNfcLeaseProgress($httpNfcLease, int $percent)
    {
        $body = [
            '_this' => [
                '_' => $httpNfcLease->returnval->_,
                'type' => 'HttpNfcLease',
            ],
            'percent' => $percent,
        ];

        return $this->request('HttpNfcLeaseProgress', $body);
    }

    public function httpNfcLeaseComplete($httpNfcLease)
    {
        $body = [
            '_this' => [
                '_' => $httpNfcLease->returnval->_,
                'type' => 'HttpNfcLease',
            ],
        ];

        return $this->request('HttpNfcLeaseComplete', $body);
    }
}