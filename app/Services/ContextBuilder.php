<?php

namespace App\Services;

class ContextBuilder
{
    public function build(int $userId): array
    {
        return ['user_id' => $userId];
    }
}
