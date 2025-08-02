<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\ConversationRepository;
use App\Repositories\PromptRepository;
use App\Repositories\UserRepository;
use App\Services\PromptLoader;
use App\Services\TokenCounter;
use PHPUnit\Framework\TestCase;

class PromptLoaderTest extends TestCase
{
    public function test_includes_master_prompt_and_context(): void
    {
        $userRepo = new class implements UserRepository {
            public function getProfile(int $userId): array
            {
                return ['name' => 'Alice'];
            }
        };

        $conversationRepo = new class implements ConversationRepository {
            public function recent(int $userId, int $tokenBudget): array
            {
                return [
                    ['role' => 'user', 'content' => 'hola'],
                ];
            }
        };

        $promptRepo = new class implements PromptRepository {
            public function findBySlug(string $slug): object
            {
                return (object) ['content' => 'PROMPT_MASTER'];
            }
        };

        $loader = new PromptLoader($promptRepo, $userRepo, $conversationRepo);
        $messages = $loader->buildMerlinPrompt(new User(1));

        $this->assertSame('PROMPT_MASTER', $messages[0]['content']);

        $contextJson = substr($messages[1]['content'], strlen('CONTEXTO_USUARIO\n'));
        $context = json_decode($contextJson, true);
        $this->assertSame('Alice', $context['profile']['name']);
        $this->assertSame('hola', $context['recent_conversation'][0]['content']);
    }

    public function test_truncates_recent_conversation_to_token_budget(): void
    {
        $allMessages = [];
        for ($i = 1; $i <= 2000; $i++) {
            $allMessages[] = ['role' => 'user', 'content' => 'msg' . $i];
        }

        $conversationRepo = new class($allMessages) implements ConversationRepository {
            public function __construct(private array $messages)
            {
            }

            public function recent(int $userId, int $tokenBudget): array
            {
                $counter = new TokenCounter();
                $result = [];
                $tokens = 0;
                for ($i = count($this->messages) - 1; $i >= 0; $i--) {
                    $msg = $this->messages[$i];
                    $msgTokens = $counter->count([$msg]);
                    if ($tokens + $msgTokens > $tokenBudget) {
                        break;
                    }
                    array_unshift($result, $msg);
                    $tokens += $msgTokens;
                }
                return $result;
            }
        };

        $userRepo = new class implements UserRepository {
            public function getProfile(int $userId): array
            {
                return ['name' => 'Bob'];
            }
        };

        $promptRepo = new class implements PromptRepository {
            public function findBySlug(string $slug): object
            {
                return (object) ['content' => 'PROMPT'];
            }
        };

        $loader = new PromptLoader($promptRepo, $userRepo, $conversationRepo);
        $messages = $loader->buildMerlinPrompt(new User(1));
        $contextJson = substr($messages[1]['content'], strlen('CONTEXTO_USUARIO\n'));
        $context = json_decode($contextJson, true);
        $recent = $context['recent_conversation'];

        $counter = new TokenCounter();
        $tokenCount = $counter->count($recent);
        $this->assertLessThanOrEqual(1800, $tokenCount);
        $this->assertSame('msg201', $recent[0]['content']);
        $this->assertSame('msg2000', $recent[count($recent) - 1]['content']);
    }
}

