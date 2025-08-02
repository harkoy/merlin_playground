<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conversation;
use App\Repositories\BrandBriefRepository;
use App\Repositories\PromptRepository;

class PromptLoader
{
    public function __construct(
        protected BrandBriefRepository $briefRepo,
        protected PromptRepository $promptRepo,
        protected ContextBuilder $contextBuilder,
    ) {
    }

    public function buildMerlinPrompt(User $user): array
    {
        // Placeholder for existing method
        $prompt = $this->promptRepo->findBySlug('merlin_master')->content ?? '';
        return [['role' => 'system', 'content' => $prompt]];
    }

    public function buildBusinessPrompt(User $user, Conversation $conv): array
    {
        $brief = $this->briefRepo->get($conv->id);
        $master = $this->promptRepo->findBySlug('business_consultant_master')->content ?? '';

        $context = $this->contextBuilder->build($user->id);

        $filled = str_replace(
            ['{brand_brief_json}', '{user_context_json}'],
            [json_encode($brief, JSON_UNESCAPED_UNICODE), json_encode($context, JSON_UNESCAPED_UNICODE)],
            $master
        );

        return [['role' => 'system', 'content' => $filled]];
    }
}
