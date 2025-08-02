<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class ChatService
{
    public function __construct(
        private PromptLoader $promptLoader,
        private TokenCounter $tokenCounter
    ) {
    }

    public function buildMessages(User $user, string $currentMessage): array
    {
        $messages = $this->promptLoader->buildMerlinPrompt($user);
        $messages[] = ['role' => 'user', 'content' => $currentMessage];

        $tokenCount = $this->tokenCounter->count($messages);
        if ($tokenCount > 7000) {
            $budget = 1800;
            do {
                $budget -= 200;
                $messages = $this->promptLoader->buildMerlinPrompt($user, $budget);
                $messages[] = ['role' => 'user', 'content' => $currentMessage];
                $tokenCount = $this->tokenCounter->count($messages);
            } while ($tokenCount > 7000 && $budget > 0);
        }

        return $messages;
    }
}

