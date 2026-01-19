<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

interface MessageRepositoryInterface
{
    public function findById(int $id): ?Message;

    public function getPending(int $limit, ?string $segment = null): Collection;

    public function markProcessing(int $id): bool;

    public function markSent(int $id, string $providerMessageId, Carbon $sentAt): void;

    public function markFailed(int $id, string $reason): void;

    public function getSentPaginated(int $perPage = 15): LengthAwarePaginator;
}
