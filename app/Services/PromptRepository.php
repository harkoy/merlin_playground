<?php
namespace App\Services;

class PromptRepository
{
    public function findBySlug(string $slug): object
    {
        return (object)['content' => ''];
    }
}
