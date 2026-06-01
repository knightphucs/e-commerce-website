<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiChatClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function send(string $systemPrompt, string $message, array $history = []): ?string
    {
        $contents = collect($history)
            ->filter(fn (array $message): bool => isset($message['role'], $message['content']))
            ->map(fn (array $message): array => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $message['content']],
                ],
            ])
            ->push([
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                ],
            ])
            ->values()
            ->toArray();

        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.api_key'),
            'Content-Type' => 'application/json',
        ])
            ->timeout(15)
            ->connectTimeout(5)
            ->retry([100, 300])
            ->post($this->endpoint(), [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 500,
                ],
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    private function endpoint(): string
    {
        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            config('services.gemini.model', 'gemini-2.0-flash')
        );
    }
}
