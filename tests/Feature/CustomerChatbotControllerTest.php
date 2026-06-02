<?php

use App\Ai\Agents\CustomerChatbotAgent;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Prompts\AgentPrompt;

test('customer chatbot returns a configuration message when api key is missing', function () {
    config()->set('ai.providers.gemini.key', null);

    $this->postJson(route('customer-chatbot.send'), [
        'message' => 'Tôi muốn hỏi về thanh toán',
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Chatbot hiện chưa được cấu hình. Vui lòng liên hệ cửa hàng để được hỗ trợ.');
});

test('customer chatbot sends messages to gemini', function () {
    config()->set('ai.providers.gemini.key', 'test-gemini-key');
    config()->set('ai.providers.gemini.models.text.default', 'gemini-test-model');

    CustomerChatbotAgent::fake(['Gemini reply'])->preventStrayPrompts();

    $this->postJson(route('customer-chatbot.send'), [
        'message' => 'Tôi muốn hỏi về thanh toán',
        'history' => [
            ['role' => 'assistant', 'content' => 'Xin chào'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Gemini reply');

    CustomerChatbotAgent::assertPrompted(function (AgentPrompt $prompt): bool {
        return $prompt->prompt === 'Tôi muốn hỏi về thanh toán'
            && $prompt->model === 'gemini-test-model'
            && $prompt->agent->messages()->contains(fn (Message $message): bool => $message->role->value === 'assistant'
                && $message->content === 'Xin chào');
    });
});
