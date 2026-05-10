<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array'],
        ]);

        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            return response()->json(['reply' => 'Chatbot hiện chưa được cấu hình. Vui lòng liên hệ quản trị viên.']);
        }

        $systemPrompt = <<<'EOT'
Bạn là trợ lý hỗ trợ khách hàng của cửa hàng E-commerce. Nhiệm vụ của bạn là:
- Hỗ trợ khách hàng về thông tin sản phẩm, đơn hàng, thanh toán và vận chuyển
- Trả lời bằng tiếng Việt, thân thiện và chuyên nghiệp
- Hướng dẫn khách hàng đặt hàng, theo dõi đơn hàng
- Giải thích các phương thức thanh toán: COD (thanh toán khi nhận hàng) và VNPay
- Chính sách đổi trả: trong 7 ngày kể từ ngày nhận hàng
- Thời gian giao hàng: 2-5 ngày làm việc
- Nếu không biết thông tin cụ thể, hướng dẫn khách hàng liên hệ qua email hoặc điện thoại
EOT;

        $messages = collect($request->history ?? [])
            ->map(fn ($msg) => ['role' => $msg['role'], 'content' => $msg['content']])
            ->push(['role' => 'user', 'content' => $request->message])
            ->values()
            ->toArray();

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 500,
            'system' => $systemPrompt,
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            return response()->json(['reply' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau.'], 500);
        }

        $reply = $response->json('content.0.text', 'Xin lỗi, không thể xử lý yêu cầu của bạn.');

        return response()->json(['reply' => $reply]);
    }
}
