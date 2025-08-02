<?php
use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Services\ConversationService;

class OpeningGreetingTest extends TestCase
{
    public function test_conversation_starts_with_greeting_and_name(): void
    {
        $user = User::factory()->create(['first_name' => 'María']);
        $service = app(ConversationService::class);
        $conv = $service->bootConversation($user);

        $first = $conv->messages()->assistant()->first();
        $this->assertStringContainsString('Saludos, María', $first->content);
    }
}
