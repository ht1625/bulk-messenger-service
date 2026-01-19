<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use App\Support\Enums\MessageStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_message_as_sent_after_successful_send(): void
    {
        Http::fake([
            '*' => Http::response([
                'messageId' => 'abc-123'
            ], 200),
        ]);

        $message = Message::factory()->create([
            'status' => MessageStatus::PENDING,
        ]);

        (new SendMessageJob($message->id))->handle(
            app(\App\Repositories\Contracts\MessageRepositoryInterface::class),
            app(\App\Services\Messaging\Contracts\MessageSenderServiceInterface::class),
        );

        $message->refresh();

        $this->assertEquals(MessageStatus::SENT, $message->status);
        $this->assertEquals('abc-123', $message->provider_message_id);
    }
}
