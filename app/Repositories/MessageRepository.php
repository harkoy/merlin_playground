<?php
namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;

class MessageRepository
{
    public function create(array $data): Message
    {
        /** @var Conversation $conversation */
        $conversation = $data['conversation'];
        $message = new Message($data['role'], $data['content']);
        $conversation->addMessage($message);
        return $message;
    }
}
