<?php

use App\Ai\Agents\AdminChatbotAgent;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;

test('admin chatbot returns a configuration message when api key is missing', function () {
    $admin = User::factory()->admin()->create();

    config()->set('ai.providers.gemini.key', null);

    $this->actingAs($admin)
        ->postJson(route('chatbot.send'), [
            'message' => 'Kiểm tra trạng thái',
        ])
        ->assertOk()
        ->assertJsonPath('reply', 'Trợ lý quản trị hiện chưa được cấu hình. Vui lòng kiểm tra cấu hình hệ thống.');
});

test('admin chatbot sends messages to gemini', function () {
    $admin = User::factory()->admin()->create();

    config()->set('ai.providers.gemini.key', 'test-gemini-key');
    config()->set('ai.providers.gemini.models.text.default', 'gemini-test-model');

    AdminChatbotAgent::fake(['Admin Gemini reply'])->preventStrayPrompts();

    $this->actingAs($admin)
        ->postJson(route('chatbot.send'), [
            'message' => 'Kiểm tra trạng thái thanh toán',
        ])
        ->assertOk()
        ->assertJsonPath('reply', 'Admin Gemini reply');

    AdminChatbotAgent::assertPrompted(function (AgentPrompt $prompt): bool {
        return $prompt->prompt === 'Kiểm tra trạng thái thanh toán'
            && $prompt->model === 'gemini-test-model';
    });
});
