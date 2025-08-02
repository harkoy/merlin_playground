<?php
use App\Models\User;
use App\Services\ConversationService;

Route::post('/conversations', function () {
    $user = new User(1, first_name: 'Demo', name: 'Demo');
    return app(ConversationService::class)->bootConversation($user);
});
