<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;
    
    protected $table = 'messages';

    protected $fillable = [
        'segment',
        'recipient_phone',
        'content',
        'status',
        'provider_message_id',
        'sent_at',
        'fail_reason',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
