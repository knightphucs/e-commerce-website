<?php

test('customer chatbot returns a configuration message when api key is missing', function () {
    config()->set('services.openai.api_key', null);

    $this->postJson(route('customer-chatbot.send'), [
        'message' => 'Tôi muốn hỏi về thanh toán',
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Chatbot hiện chưa được cấu hình. Vui lòng liên hệ cửa hàng để được hỗ trợ.');
});
