<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Models\PixTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class NotifyWebhookEndpoints implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PixTransaction $transaction) {}

    public function handle()
    {
        $data = [
            'id' => $this->transaction->external_transaction_id,
            'status' => $this->transaction->status,
        ];

        foreach (WebhookEndpoint::all() as $endpoint) {
            Http::withHeaders([
                'X-Signature-Token' => $endpoint->token ?? '',
            ])->post($endpoint->url, $data);
        }
    }
}
