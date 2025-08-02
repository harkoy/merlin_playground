<?php

use PHPUnit\Framework\TestCase;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\ChatService;
use App\Services\PromptLoader;
use App\Repositories\BrandBriefRepository;
use App\Enums\ConversationPhase;

class BusinessConsultantTest extends TestCase
{
    public function test_phase_advances_and_prompt_filled(): void
    {
        $conversation = new class extends Conversation {
            public $id = 1;
            public function save(array $opts = []) {}
        };

        $user = new class extends User {
            public $id = 10;
        };

        $briefRepo = new BrandBriefRepository(__DIR__ . '/tmp');

        $promptRepo = new class {
            public function findBySlug($slug)
            {
                return (object) ['content' => 'BRIEF {brand_brief_json} USER {user_context_json}'];
            }
        };

        $contextBuilder = new class {
            public function build($userId)
            {
                return ['id' => $userId];
            }
        };

        $promptLoader = new PromptLoader($briefRepo, $promptRepo, $contextBuilder);
        $convService = new ConversationService();
        $chatService = new ChatService($convService, $promptLoader, $briefRepo);

        $payload = ['foo' => 'bar'];
        $chatService->handleAssistantResponse($conversation, 'MERLIN_COMPLETED', $payload);

        $this->assertSame(ConversationPhase::CONSULTING->value, $conversation->phase);

        $messages = $chatService->buildMessages($user, $conversation, 'hola');
        $systemContent = $messages[0]['content'];

        $this->assertStringContainsString(json_encode($payload), $systemContent);
        $this->assertStringContainsString(json_encode(['id' => $user->id]), $systemContent);
    }
}
