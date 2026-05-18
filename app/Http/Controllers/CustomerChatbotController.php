<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerChatbotController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array'],
        ]);

        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            return response()->json(['reply' => 'Chatbot hiện chưa được cấu hình. Vui lòng liên hệ cửa hàng để được hỗ trợ.']);
        }

        $systemPrompt = <<<'EOT'
Bạn là trợ lý hỗ trợ khách hàng của cửa hàng E-commerce. Nhiệm vụ của bạn là:
- Hỗ trợ khách hàng về sản phẩm, đơn hàng, thanh toán và vận chuyển
- Trả lời bằng tiếng Việt, thân thiện, ngắn gọn và chính xác
- Giải thích COD là thanh toán khi nhận hàng, còn VNPay là thanh toán trực tuyến
- Hướng dẫn khách tra cứu đơn bằng mã theo dõi sau khi đặt hàng
- Chính sách đổi trả: trong 7 ngày kể từ ngày nhận hàng
- Thời gian giao hàng dự kiến: 2-5 ngày làm việc
- Nếu thiếu dữ liệu cụ thể, hướng dẫn khách liên hệ cửa hàng qua email hoặc hotline
EOT;

        $messages = collect($request->history ?? [])
            ->map(fn ($message) => ['role' => $message['role'], 'content' => $message['content']])
            ->prepend(['role' => 'system', 'content' => $systemPrompt])
            ->push(['role' => 'user', 'content' => $request->message])
            ->values()
            ->toArray();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'max_tokens' => 500,
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            return response()->json(['reply' => 'Xin lỗi, hiện không thể xử lý yêu cầu. Vui lòng thử lại sau.'], 500);
        }

        return response()->json([
            'reply' => $response->json('choices.0.message.content', 'Xin lỗi, hiện không thể xử lý yêu cầu của bạn.'),
        ]);
    }
}
