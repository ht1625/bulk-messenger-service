<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Providers\Messaging\Contracts\MessageProviderInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Messaging\Contracts\MessageSenderServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MessageSenderService implements MessageSenderServiceInterface
{
    public function __construct(
        private readonly MessageProviderInterface $provider,
        private readonly MessageRepositoryInterface $messageRepository
    ) {}

    public function send(Message $message): void
    {
        $limit = (int) config('messaging.char_limit', 160);

        Log::channel('messages')->info('MessageSenderService: send started', [
            'message_id' => $message->id,
            'phone' => $message->recipient_phone,
        ]);

        try {
            if (mb_strlen($message->content) > $limit) {
                $this->messageRepository->markFailed(
                    $message->id,
                    'CHAR_LIMIT_EXCEEDED'
                );

                Log::channel('messages')->warning('Message failed due to character limit', [
                    'message_id' => $message->id,
                    'length' => mb_strlen($message->content),
                    'limit' => $limit,
                ]);

                return;
            }

            if (empty($message->recipient_phone)) {
                $this->messageRepository->markFailed(
                    $message->id,
                    'RECIPIENT_PHONE_EMPTY'
                );

                Log::channel('messages')->warning('Message failed due to empty recipient phone', [
                    'message_id' => $message->id,
                ]);

                return;
            }

            $providerResponse = $this->provider->send(
                recipientPhone: $message->recipient_phone,
                content: $message->content
            );

            $this->messageRepository->markSent(
                id: $message->id,
                providerMessageId: $providerResponse->messageId,
                sentAt: $providerResponse->sentAt
            );

            if (config('messaging.sent_cache.enabled')) {
                $keyPrefix = (string) config('messaging.sent_cache.key_prefix', 'message:sent:');
                $ttl = (int) config('messaging.sent_cache.ttl_seconds', 604800);

                $cacheKey = $keyPrefix . $message->id;

                Cache::store('redis')->put($cacheKey, [
                    'messageId' => $providerResponse->messageId,
                    'sentAt' => $providerResponse->sentAt->toIso8601String(),
                ], $ttl);

                Log::channel('messages')->debug('Sent message cached in Redis', [
                    'message_id' => $message->id,
                    'cache_key' => $cacheKey,
                    'ttl' => $ttl,
                ]);
            }

            Log::channel('messages')->info('MessageSenderService: send completed', [
                'message_id' => $message->id,
            ]);

        } catch (Throwable $e) {

            Log::channel('messages')->error('MessageSenderService error', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
