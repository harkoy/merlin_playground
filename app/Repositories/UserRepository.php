<?php

declare(strict_types=1);

namespace App\Repositories;

interface UserRepository
{
    public function getProfile(int $userId): array;
}

