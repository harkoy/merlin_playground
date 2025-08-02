<?php
use App\Events\ConversationCreated;
use App\Listeners\SendOpeningGreeting;
use App\Repositories\MessageRepository;
use App\Services\ContextBuilder;
use App\Services\ConversationService;
use App\Services\PromptLoader;
use App\Services\PromptRepository;
use App\Services\UserRepository;

function app(string $class)
{
    return new $class();
}

function event(object $event): void
{
    if ($event instanceof ConversationCreated) {
        $listener = new SendOpeningGreeting(
            new PromptLoader(new PromptRepository(), new ContextBuilder(new UserRepository())),
            new MessageRepository()
        );
        $listener->handle($event);
    }
}
