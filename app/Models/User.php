<?php
namespace App\Models;

class User
{
    public function __construct(
        public int $id,
        public ?string $first_name = null,
        public ?string $name = null
    ) {}

    public static function factory(): UserFactory
    {
        return new UserFactory();
    }
}

class UserFactory
{
    public function create(array $attributes): User
    {
        return new User(
            $attributes['id'] ?? rand(1, 1000),
            $attributes['first_name'] ?? null,
            $attributes['name'] ?? null
        );
    }
}
