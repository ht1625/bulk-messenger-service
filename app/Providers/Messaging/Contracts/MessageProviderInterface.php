<?php

namespace App\Providers\Messaging\Contracts;

use App\Providers\Messaging\Dtos\ProviderResponseDto;

interface MessageProviderInterface
{
    public function send(string $recipientPhone, string $content): ProviderResponseDto;
}
