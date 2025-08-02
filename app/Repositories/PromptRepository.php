<?php

namespace App\Repositories;

class PromptRepository
{
    public function findBySlug(string $slug): object
    {
        return (object)['content' => ''];
    }
}
