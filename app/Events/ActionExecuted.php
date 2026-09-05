<?php

namespace App\Events;

use App\Models\ActionRule;
use App\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActionExecuted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public ActionRule $rule;

    public Scan $scan;

    public array $payload;

    public function __construct(ActionRule $rule, Scan $scan, array $payload)
    {
        $this->rule = $rule;
        $this->scan = $scan;
        $this->payload = $payload;
    }
}
