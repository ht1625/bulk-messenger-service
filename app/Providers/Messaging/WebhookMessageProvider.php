<?php

namespace App\Providers\Messaging;

use App\Providers\Messaging\Contracts\MessageProviderInterface;
use App\Providers\Messaging\Dtos\ProviderResponseDto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class WebhookMessageProvider implements MessageProviderInterface
{
    public function send(string $recipientPhone, string $content): ProviderResponseDto
    {
        $url = config('messaging.webhook_url');

        if (empty($url)) {
            Log::channel('messages')->error('Webhook URL is missing in configuration', [
                'env_key' => 'MESSAGING_WEBHOOK_URL',
            ]);

            throw new RuntimeException('MESSAGING_WEBHOOK_URL is not set in .env');
        }

        $payload = [
            'to' => $recipientPhone,
            'content' => $content,
            //'sentAt' => now()->toIso8601String(),
        ];

        Log::channel('messages')->info('Sending message to webhook provider', [
            'url' => $url,
            'to' => $recipientPhone,
            'payload_preview' => [
                'to' => $recipientPhone,
                'content_length' => mb_strlen($content),
            ],
        ]);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            $body = $response->json() ?? [];

            Log::channel('messages')->info('Webhook response received', [
                'status' => $response->status(),
                'body_preview' => array_slice($body, 0, 10), 
            ]);

            $messageId =
                data_get($body, 'messageId')
                ?? (string) Str::uuid();

            $sentAt = now()->toIso8601String();

            return new ProviderResponseDto(
                messageId: (string) $messageId,
                sentAt: Carbon::parse($sentAt),
                raw: [
                    'status_code' => $response->status(),
                    'body' => $body,
                ]
            );

        } catch (\Throwable $e) {

            Log::channel('messages')->error('Webhook provider request failed', [
                'url' => $url,
                'to' => $recipientPhone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
