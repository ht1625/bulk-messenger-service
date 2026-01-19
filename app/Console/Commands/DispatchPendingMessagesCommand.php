<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class DispatchPendingMessagesCommand extends Command
{
    protected $signature = 'messages:dispatch
                            {--segment= : Only dispatch messages for a given segment}
                            {--limit= : Maximum number of pending messages to dispatch}';

    protected $description = 'Dispatch pending messages to queue for sending';

    public function handle(MessageRepositoryInterface $messageRepository): int
    {
        $segment = $this->option('segment');
        $limit = (int) ($this->option('limit') ?? config('messaging.dispatch_batch_limit', 500));

        $rateKey = 'message_dispatch_rate';
        $allow = (int) config('messaging.rate_limit.allow', 2);
        $every = (int) config('messaging.rate_limit.every_seconds', 5);

        Log::info('messages:dispatch started', [
            'segment' => $segment,
            'requested_limit' => $limit,
            'allow' => $allow,
            'every_seconds' => $every,
        ]);

        try {
            $used = Redis::get($rateKey) ?? 0;

            if ($used >= $allow) {
                $this->info("Rate limit reached ({$allow}/{$every}s). Nothing dispatched.");

                Log::warning('Message dispatch rate limit reached', [
                    'rate_key' => $rateKey,
                    'used' => $used,
                    'allow' => $allow,
                    'every_seconds' => $every,
                ]);

                return self::SUCCESS;
            }

            $remaining = $allow - $used;
            $limit = min($limit, $remaining);

            $pending = $messageRepository->getPending($limit, $segment);

            if ($pending->isEmpty()) {
                $this->info('No pending messages found.');

                Log::info('No pending messages found for dispatch', [
                    'segment' => $segment,
                ]);

                return self::SUCCESS;
            }

            Log::info('Pending messages fetched', [
                'segment' => $segment,
                'count' => $pending->count(),
                'dispatch_limit' => $limit,
            ]);

            foreach ($pending as $message) {

                SendMessageJob::dispatch($message->id);

                // Sayaç artır + TTL koy (ilk kezse)
                Redis::incr($rateKey);

                if (!Redis::ttl($rateKey)) {
                    Redis::expire($rateKey, $every);
                }

                Log::debug('Message dispatched to queue', [
                    'message_id' => $message->id,
                    'recipient_phone' => $message->recipient_phone,
                    'segment' => $message->segment,
                ]);
            }

            $this->info("Dispatched {$pending->count()} messages to queue.");

            Log::info('messages:dispatch completed', [
                'dispatched_count' => $pending->count(),
                'segment' => $segment,
            ]);

            return self::SUCCESS;

        } catch (Throwable $e) {

            Log::error('Error in messages:dispatch command', [
                'segment' => $segment,
                'limit' => $limit,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Dispatch failed. Check logs.');

            return self::FAILURE;
        }
    }
}
