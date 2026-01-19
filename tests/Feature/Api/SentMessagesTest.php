<?php

namespace Tests\Feature\Api;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_sent_messages(): void
    {
        Message::factory()->count(3)->sent()->create();
        Message::factory()->count(2)->create(); // pending

        $response = $this->getJson('/api/v1/messages/sent');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(3, $data);

        foreach ($data as $item) {
            $this->assertEquals('sent', $item['status']);
            $this->assertNotEmpty($item['provider_message_id']);
        }
    }
}
