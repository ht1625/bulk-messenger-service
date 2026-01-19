<?php

namespace Database\Factories;

use App\Models\Message;
use App\Support\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'segment' => $this->faker->randomElement(['A', 'B', 'C']),
            'recipient_phone' => '+905' . $this->faker->numberBetween(100000000, 999999999),
            'content' => $this->faker->text(80),
            'status' => MessageStatus::PENDING,
            'provider_message_id' => null,
            'sent_at' => null,
            'fail_reason' => null,
        ];
    }

    public function sent(): self
    {
        return $this->state(fn () => [
            'status' => MessageStatus::SENT,
            'provider_message_id' => 'msg_' . $this->faker->uuid(),
            'sent_at' => now(),
        ]);
    }
}
