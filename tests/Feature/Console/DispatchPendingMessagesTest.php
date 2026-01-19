<?php

namespace Tests\Feature\Console;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class DispatchPendingMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_pending_messages_to_queue(): void
    {
        Queue::fake();

        config()->set('messaging.rate_limit.allow', 10);

        Redis::shouldReceive('get')
            ->with('message_dispatch_rate')
            ->andReturn(0);

        Redis::shouldReceive('incr')->times(5);
        Redis::shouldReceive('ttl')->times(5)->andReturn(null);
        Redis::shouldReceive('expire')->times(5);

        Message::factory()->count(5)->create(); // pending
        Message::factory()->count(2)->sent()->create();

        $this->artisan('messages:dispatch')
            ->assertExitCode(0);

        Queue::assertPushed(SendMessageJob::class, 5);
    }
}
