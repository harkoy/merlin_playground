<?php
namespace App\Listeners;

use App\Events\ConversationCreated;
use App\Repositories\MessageRepository;
use App\Services\PromptLoader;

class SendOpeningGreeting
{
    public function __construct(
        private PromptLoader $prompts,
        private MessageRepository $messages
    ) {}

    public function handle(ConversationCreated $event): void
    {
        $user = $event->conversation->user;
        $greeting = str_replace(
            '{nombre_del_usuario}',
            $user->first_name ?? $user->name,
            'Saludos, {nombre_del_usuario}. Soy MERLIN, Guardián del Oráculo. Hoy te acompañaré para descubrir la esencia de tu marca y recabar cada detalle necesario para que nuestro diseñador cree un logotipo y un manual de marca impecables. ¿Listo para comenzar?'
        );

        $this->messages->create([
            'conversation' => $event->conversation,
            'role' => 'assistant',
            'content' => $greeting,
        ]);
    }
}
