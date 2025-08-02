<?php
namespace App\Events;

use App\Models\Conversation;

class ConversationCreated
{
    public function __construct(public Conversation $conversation)
    {
    }
}
