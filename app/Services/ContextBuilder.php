<?php
namespace App\Services;

class ContextBuilder
{
    public function __construct(private readonly UserRepository $users) {}

    public function build(int $userId): array
    {
        $profile = $this->users->getProfile($userId);
        $profile['first_name'] = $profile['first_name'] ?? ($profile['name'] ?? null);
        return $profile;
    }
}
