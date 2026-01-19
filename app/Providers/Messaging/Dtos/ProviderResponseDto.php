<?php

namespace App\Providers\Messaging\Dtos;

use Carbon\Carbon;

class ProviderResponseDto
{
    public function __construct(
        public readonly string $messageId,
        public readonly Carbon $sentAt,
        public readonly array $raw = []
    ) {}
}
