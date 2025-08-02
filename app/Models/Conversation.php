<?php
namespace App\Models;

class Conversation
{
    public int $id;
    public User $user;
    public string $phase;
    /** @var Message[] */
    public array $messages = [];

    private static int $increment = 1;

    public function __construct(array $attributes)
    {
        $this->id = self::$increment++;
        $this->user = $attributes['user'];
        $this->phase = $attributes['phase'];
    }

    public static function create(array $attributes): self
    {
        return new self($attributes);
    }

    public function messages(): MessageCollection
    {
        return new MessageCollection($this->messages);
    }

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
    }
}
