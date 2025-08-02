<?php

namespace App\Repositories;

class BrandBriefRepository
{
    protected string $dir;

    public function __construct(string $dir = __DIR__ . '/../../storage/briefs')
    {
        $this->dir = $dir;
    }

    public function save(int $conversationId, array $payload): void
    {
        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
        $file = $this->dir . '/' . $conversationId . '.json';
        file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function get(int $conversationId): array
    {
        $file = $this->dir . '/' . $conversationId . '.json';
        if (! file_exists($file)) {
            return [];
        }
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
}
