<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Throwable;

class GeminiChatClient
{
    public function isConfigured(): bool
    {
        return filled(config('ai.providers.gemini.key'));
    }

    /**
     * @param  class-string<object>  $agent
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function send(string $agent, string $message, array $history = []): ?string
    {
        try {
            /** @var Promptable $chatbot */
            $chatbot = $agent::make(history: $history);

            $response = $chatbot->prompt(
                $message,
                provider: Lab::Gemini,
                model: config('ai.providers.gemini.models.text.default', 'gemini-2.0-flash'),
                timeout: 30,
            );

            return $response->text;
        } catch (Throwable $e) {
            Log::error('GeminiChatClient failed', ['exception' => $e]);

            return null;
        }
    }
}
