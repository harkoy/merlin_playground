<?php
namespace App\Models;

class Message
{
    public function __construct(
        public string $role,
        public string $content
    ) {}

    /**
     * Scope to retrieve assistant messages from a list.
     *
     * @param Message[] $messages
     * @return Message[]
     */
    public function scopeAssistant(array $messages): array
    {
        return array_filter($messages, fn(self $m) => $m->role === 'assistant');
    }
}

class MessageCollection
{
    /** @param Message[] $messages */
    public function __construct(private array $messages) {}

    public function assistant(): self
    {
        $msg = array_filter($messages = $this->messages, fn($m) => $m->role === 'assistant');
        return new self(array_values($msg));
    }

    public function first(): ?Message
    {
        return $this->messages[0] ?? null;
    }
}
