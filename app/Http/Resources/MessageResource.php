<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Message */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'segment' => $this->segment,
            'recipient_phone' => $this->recipient_phone,
            'content' => $this->content,
            'status' => $this->status,
            'provider_message_id' => $this->provider_message_id,
            'sent_at' => optional($this->sent_at)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
