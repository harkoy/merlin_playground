<?php

namespace App\Services;

use App\Models\Conversation;
use App\Enums\ConversationPhase;

class ConversationService
{
    public function advancePhase(Conversation $conv, ConversationPhase $phase): void
    {
        $conv->phase = $phase->value;
        $conv->save();
    }
}
