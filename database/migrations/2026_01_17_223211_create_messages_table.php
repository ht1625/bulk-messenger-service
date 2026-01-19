<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\Enums\MessageStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->string('segment')->nullable()->index();
            $table->string('recipient_phone')->index();
            $table->text('content');

            $table->string('status')->default(MessageStatus::PENDING)->index();

            $table->string('provider_message_id')->nullable()->index();
            $table->dateTime('sent_at')->nullable();

            $table->string('fail_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
