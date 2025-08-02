<?php

namespace App\Models;

use App\Enums\ConversationPhase;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['phase'];

    protected $attributes = [
        'phase' => ConversationPhase::BRIEFING->value,
    ];
}
