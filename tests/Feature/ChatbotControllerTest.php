<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('admin chatbot sends messages to gemini', function () {
    $admin = User::factory()->admin()->create();

    config()->set('services.gemini.api_key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-test-model');

    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Admin Gemini reply'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $this->actingAs($admin)
        ->postJson(route('chatbot.send'), [
            'message' => 'Kiểm tra trạng thái thanh toán',
        ])
        ->assertOk()
        ->assertJsonPath('reply', 'Admin Gemini reply');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test-model:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && $request['contents'][0]['role'] === 'user';
    });
});
