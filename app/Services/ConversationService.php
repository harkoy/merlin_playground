<?php
namespace App\Services;

use App\Events\ConversationCreated;
use App\Models\Conversation;
use App\Models\ConversationPhase;
use App\Models\User;

class ConversationService
{
    public function bootConversation(User $user): Conversation
    {
        $conv = Conversation::create([
            'user'  => $user,
            'phase' => ConversationPhase::BRIEFING->value
        ]);

        event(new ConversationCreated($conv));

        return $conv;
    }
}
