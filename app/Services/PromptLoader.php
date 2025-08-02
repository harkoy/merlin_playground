<?php

namespace App\Services;

use App\Models\User;

class PromptLoader
{
    public function __construct(
        private readonly PromptRepository $promptRepo,
        private readonly ContextBuilder $contextBuilder
    ) {}

    public function buildMerlinPrompt(User $user): array
    {
        $master = $this->promptRepo->findBySlug('merlin_master')->content ?? '';
        $master = str_replace('{nombre_del_usuario}', $user->first_name ?? $user->name, $master);

        $context = $this->contextBuilder->build($user->id);

        return [
            ['role' => 'system', 'content' => $master],
            [
                'role'    => 'system',
                'content' => "CONTEXTO_USUARIO\n" . json_encode($context, JSON_UNESCAPED_UNICODE)
            ]
        ];
    }
}

