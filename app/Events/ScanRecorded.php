<?php

namespace App\Events;

use App\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScanRecorded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Scan $scan;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }
}
