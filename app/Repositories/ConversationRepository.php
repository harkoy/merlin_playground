<?php

declare(strict_types=1);

namespace App\Repositories;

interface ConversationRepository
{
    public function recent(int $userId, int $tokenBudget): array;
}

