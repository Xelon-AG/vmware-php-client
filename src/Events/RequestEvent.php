<?php

namespace Xelon\VmWareClient\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public string $requestBody;

    public string $responseBody;

    public string $status;

    public function __construct($requestBody, $responseBody, $status)
    {
        $this->requestBody = $requestBody;
        $this->responseBody = $responseBody;
        $this->status = $status ? 'Success' : 'Error';
    }

    public function broadcastOn()
    {
        return new PrivateChannel('vmware-client');
    }
}