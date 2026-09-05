<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ExecuteWebhookAction implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public int $timeout = 15;

    public function __construct(
        public string $url,
        public ?string $secret,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $body = json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Http::timeout(10)
            ->withHeaders([
                'X-Urundogrula-Signature' => 'sha256=' . hash_hmac('sha256', (string) $body, $this->secret ?? ''),
            ])
            ->withBody((string) $body, 'application/json')
            ->post($this->url)
            ->throw();
    }
}
