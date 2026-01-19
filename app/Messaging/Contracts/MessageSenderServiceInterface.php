<?php

namespace App\Services\Messaging\Contracts;

use App\Models\Message;

interface MessageSenderServiceInterface
{
    public function send(Message $message): void;
}
