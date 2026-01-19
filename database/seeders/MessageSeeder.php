<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('messages')->insert([
            [
                'segment' => 'premium_users',
                'recipient_phone' => '+905301111111',
                'content' => 'Merhaba Premium Kullanıcı!',
                'status' => 'pending',
                'provider_message_id' => null,
                'sent_at' => null,
                'fail_reason' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'segment' => 'basic_users',
                'recipient_phone' => '+905302222222',
                'content' => 'Kampanya mesajı!',
                'status' => 'pending',
                'provider_message_id' => null,
                'sent_at' => null,
                'fail_reason' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'segment' => null,
                'recipient_phone' => '+905303333333',
                'content' => 'Genel duyuru mesajı.',
                'status' => 'sent',
                'provider_message_id' => 'abc123',
                'sent_at' => Carbon::now()->subHour(),
                'fail_reason' => null,
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subHour(),
            ],
        ]);
    }
}
