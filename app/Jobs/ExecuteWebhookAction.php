<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Support\SafeUrl;

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
        // Re-validated at send time: the rule may have been edited, or the job
        // may have been queued while a DNS record still pointed somewhere
        // public. Defence in depth against a redirect-then-rebind hop.
        SafeUrl::assert($this->url, 'Webhook URL');

        $body = json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Http::timeout(10)
            // A public redirector must not be able to bounce the request to
            // an internal address after validation.
            ->withOptions(['allow_redirects' => false])
            ->withHeaders([
                'X-Urundogrula-Signature' => 'sha256=' . hash_hmac('sha256', (string) $body, $this->secret ?? ''),
            ])
            ->withBody((string) $body, 'application/json')
            ->post($this->url)
            ->throw();
    }
}
