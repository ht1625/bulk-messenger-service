<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Support\Enums\MessageStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Eloquent implementation of MessageRepositoryInterface.
 */
class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @param int $id
     * @return Message|null
     */
    public function findById(int $id): ?Message
    {
        return Message::query()->find($id);
    }

    /**
     * Get pending messages with optional segment filter.
     *
     * @param int $limit
     * @param string|null $segment
     * @return Collection
     */
    public function getPending(int $limit, ?string $segment = null): Collection
    {
        $query = Message::query()
            ->where('status', MessageStatus::PENDING)
            ->orderBy('id', 'asc')
            ->limit($limit);

        if (!empty($segment)) {
            $query->where('segment', $segment);
        }

        return $query->get();
    }

    /**
     * Atomically mark a message as processing.
     *
     * @param int $id
     * @return bool
     */
    public function markProcessing(int $id): bool
    {
        $updated = Message::query()
            ->whereKey($id)
            ->where('status', MessageStatus::PENDING)
            ->update([
                'status' => MessageStatus::PROCESSING,
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            $current = Message::query()->find($id);

            Log::channel('messages')->warning('Message could not be marked as processing', [
                'message_id' => $id,
                'current_status' => $current?->status ?? 'not_found',
            ]);
        }

        return $updated === 1;
    }

    /**
     * Mark message as sent.
     *
     * @param int $id
     * @param string $providerMessageId
     * @param Carbon $sentAt
     */
    public function markSent(int $id, string $providerMessageId, Carbon $sentAt): void
    {
        Message::query()
            ->whereKey($id)
            ->update([
                'status' => MessageStatus::SENT,
                'provider_message_id' => $providerMessageId,
                'sent_at' => $sentAt,
                'fail_reason' => null,
                'updated_at' => now(),
            ]);

        Log::channel('messages')->info('Message marked as sent', [
            'message_id' => $id,
            'provider_message_id' => $providerMessageId,
        ]);
    }

    /**
     * Mark message as failed.
     *
     * @param int $id
     * @param string $reason
     */
    public function markFailed(int $id, string $reason): void
    {
        Message::query()
            ->whereKey($id)
            ->update([
                'status' => MessageStatus::FAILED,
                'fail_reason' => $reason,
                'updated_at' => now(),
            ]);

        Log::channel('messages')->error('Message marked as failed', [
            'message_id' => $id,
            'reason' => $reason,
        ]);
    }

    /**
     * Get sent messages with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getSentPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Message::query()
            ->where('status', MessageStatus::SENT)
            ->orderByDesc('sent_at')
            ->paginate($perPage);
    }
}
