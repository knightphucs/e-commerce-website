<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('customer chatbot returns a configuration message when api key is missing', function () {
    config()->set('services.gemini.api_key', null);

    $this->postJson(route('customer-chatbot.send'), [
        'message' => 'Tôi muốn hỏi về thanh toán',
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Chatbot hiện chưa được cấu hình. Vui lòng liên hệ cửa hàng để được hỗ trợ.');
});

test('customer chatbot sends messages to gemini', function () {
    config()->set('services.gemini.api_key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-test-model');

    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Gemini reply'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $this->postJson(route('customer-chatbot.send'), [
        'message' => 'Tôi muốn hỏi về thanh toán',
        'history' => [
            ['role' => 'assistant', 'content' => 'Xin chào'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Gemini reply');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test-model:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && $request['contents'][0]['role'] === 'model'
            && $request['contents'][1]['role'] === 'user';
    });
});
