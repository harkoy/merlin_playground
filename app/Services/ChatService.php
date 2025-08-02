<?php

namespace App\Services;

use App\Enums\ConversationPhase;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\BrandBriefRepository;

class ChatService
{
    public function __construct(
        protected ConversationService $conversationService,
        protected PromptLoader $promptLoader,
        protected BrandBriefRepository $briefRepo,
    ) {
    }

    public function handleAssistantResponse(Conversation $conversation, string $assistantMessage, array $payload = []): void
    {
        if ($assistantMessage === 'MERLIN_COMPLETED') {
            $this->briefRepo->save($conversation->id, $payload);
            $this->conversationService->advancePhase($conversation, ConversationPhase::CONSULTING);
        }
    }

    public function buildMessages(User $user, Conversation $conversation, string $userInput): array
    {
        if ($conversation->phase === ConversationPhase::BRIEFING->value) {
            $messages = $this->promptLoader->buildMerlinPrompt($user);
        } else {
            $messages = $this->promptLoader->buildBusinessPrompt($user, $conversation);
        }
        $messages[] = ['role' => 'user', 'content' => $userInput];
        return $messages;
    }
}
