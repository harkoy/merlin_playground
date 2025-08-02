<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\UserRepository;

class ContextBuilder
{
    public function __construct(
        private UserRepository $users,
        private ConversationRepository $convos
    ) {
    }

    public function build(int $userId, int $tokenBudget = 1800): array
    {
        $profile = $this->users->getProfile($userId);
        $recent = $this->convos->recent($userId, $tokenBudget);

        return [
            'profile' => $profile,
            'recent_conversation' => $recent,
        ];
    }
}

