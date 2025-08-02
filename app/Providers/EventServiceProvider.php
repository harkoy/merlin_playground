<?php
namespace App\Providers;

use App\Events\ConversationCreated;
use App\Listeners\SendOpeningGreeting;

class EventServiceProvider
{
    protected array $listen = [
        ConversationCreated::class => [
            SendOpeningGreeting::class,
        ],
    ];
}
