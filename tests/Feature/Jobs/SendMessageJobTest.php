<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use App\Services\Messaging\Contracts\MessageSenderServiceInterface;
use App\Support\Enums\MessageStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_message_as_sent_after_successful_send(): void
    {
        config(['cache.default' => 'array']);
        config(['messaging.sent_cache.enabled' => false]);

        Http::fake([
            '*' => Http::response([
                'messageId' => 'abc-123'
            ], 200),
        ]);

        $mockSender = $this->mock(MessageSenderServiceInterface::class);
        $mockSender->shouldReceive('send')
            ->once()
            ->andReturn([
                'success' => true,
                'provider_message_id' => 'abc-123'
            ]);

        $message = Message::factory()->create([
            'status' => MessageStatus::PENDING,
        ]);

        (new SendMessageJob($message->id))->handle(
            app(\App\Repositories\Contracts\MessageRepositoryInterface::class),
            $mockSender
        );

        $message->refresh();

        $this->assertEquals(MessageStatus::SENT, $message->status);
        $this->assertEquals('abc-123', $message->provider_message_id);
    }
}