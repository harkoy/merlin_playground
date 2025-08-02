<?php

declare(strict_types=1);

namespace App\Repositories;

interface PromptRepository
{
    public function findBySlug(string $slug): object;
}

