<?php

declare(strict_types=1);

namespace App\Services;

class TokenCounter
{
    /**
     * Very naive token counter based on word count.
     */
    public function count(array $messages): int
    {
        $tokens = 0;
        foreach ($messages as $message) {
            if (! isset($message['content'])) {
                continue;
            }
            $tokens += str_word_count((string) $message['content']);
        }
        return $tokens;
    }
}

