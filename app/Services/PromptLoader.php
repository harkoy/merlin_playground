<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\ConversationRepository;
use App\Repositories\PromptRepository;
use App\Repositories\UserRepository;

class PromptLoader
{
    public function __construct(
        private PromptRepository $promptRepo,
        private UserRepository $users,
        private ConversationRepository $convos
    ) {
    }

    public function buildMerlinPrompt(User $user, int $tokenBudget = 1800): array
    {
        $master = $this->promptRepo->findBySlug('merlin_master')->content;

        $context = (new ContextBuilder($this->users, $this->convos))
            ->build($user->id, $tokenBudget);

        return [
            ['role' => 'system', 'content' => $master],
            [
                'role' => 'system',
                'content' => 'CONTEXTO_USUARIO\n' .
                    json_encode($context, JSON_UNESCAPED_UNICODE),
            ],
        ];
    }
}

