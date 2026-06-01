<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerChatbotController extends Controller
{
    public function send(Request $request, GeminiChatClient $gemini): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array'],
        ]);

        if (! $gemini->isConfigured()) {
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

        $reply = $gemini->send($systemPrompt, $request->string('message')->toString(), $request->history ?? []);

        if (! $reply) {
            return response()->json(['reply' => 'Xin lỗi, hiện không thể xử lý yêu cầu. Vui lòng thử lại sau.'], 500);
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
