<?php

namespace App\Jobs;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Messaging\Contracts\MessageSenderServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;

    public function __construct(public readonly int $messageId)
    {
        Log::channel('messages')->info('SendMessageJob dispatched', [
            'message_id' => $this->messageId,
        ]);
    }

    public function handle(
        MessageRepositoryInterface $messageRepository,
        MessageSenderServiceInterface $senderService
    ): void {
        $this->process($messageRepository, $senderService);
    }

    private function process(
        MessageRepositoryInterface $messageRepository,
        MessageSenderServiceInterface $senderService
    ): void {
        Log::channel('messages')->debug('SendMessageJob started processing', [
            'message_id' => $this->messageId,
        ]);

        $message = $messageRepository->findById($this->messageId);

        if (!$message) {
            Log::channel('messages')->warning('Message not found for job', [
                'message_id' => $this->messageId,
            ]);
            return;
        }

        if (!$messageRepository->markProcessing($message->id)) {
            Log::warning('Message could not be marked as processing (possibly already taken)', [
                'message_id' => $message->id,
                'status' => $message->status ?? null,
            ]);
            return;
        }

        try {

            $senderService->send($message);

            Log::channel('messages')->info('Message successfully sent', [
                'message_id' => $message->id,
            ]);

        } catch (\Throwable $e) {

            $messageRepository->markFailed(
                $message->id,
                'PROVIDER_ERROR: ' . $e->getMessage()
            );

            Log::channel('messages')->error('SendMessageJob failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
