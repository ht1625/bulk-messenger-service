<?php

namespace App\Support\Enums;

final class MessageStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const SENT = 'sent';
    public const FAILED = 'failed';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::SENT,
            self::FAILED,
        ];
    }
}
