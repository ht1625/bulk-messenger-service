<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Eloquent\MessageRepository;

use App\Services\Messaging\Contracts\MessageSenderServiceInterface;
use App\Services\Messaging\MessageSenderService;

use App\Providers\Messaging\Contracts\MessageProviderInterface;
use App\Providers\Messaging\WebhookMessageProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(MessageProviderInterface::class, WebhookMessageProvider::class);
        $this->app->bind(MessageSenderServiceInterface::class, MessageSenderService::class);
    }

    public function boot(): void
    {
        //
    }
}
