<?php
namespace App\Services;

class UserRepository
{
    public function getProfile(int $userId): array
    {
        return [
            'id' => $userId,
            'first_name' => null,
            'name' => 'Anon'
        ];
    }
}
