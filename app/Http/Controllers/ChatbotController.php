<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function send(Request $request, GeminiChatClient $gemini): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array'],
        ]);

        if (! $gemini->isConfigured()) {
            return response()->json(['reply' => 'Trợ lý quản trị hiện chưa được cấu hình. Vui lòng kiểm tra cấu hình hệ thống.']);
        }

        $systemPrompt = <<<'EOT'
Bạn là trợ lý nội bộ cho quản trị viên cửa hàng E-commerce. Nhiệm vụ của bạn là:
- Hỗ trợ vận hành về đơn hàng, sản phẩm, danh mục và trạng thái thanh toán
- Trả lời bằng tiếng Việt, ngắn gọn, rõ ràng và chuyên nghiệp
- Giải thích ý nghĩa của các trạng thái đơn hàng và thanh toán trong hệ thống quản trị
- Nhắc quản trị viên rằng COD là thu tiền khi giao hàng, còn VNPay là thanh toán online đã được xác nhận qua cổng thanh toán
- Hỗ trợ soạn câu trả lời cho khách, nhưng không giả vờ đã truy cập dữ liệu ngoài nội dung được cung cấp
- Nếu thiếu dữ liệu cụ thể, yêu cầu quản trị viên kiểm tra trực tiếp đơn hàng hoặc thông tin khách hàng trong hệ thống
EOT;

        $reply = $gemini->send($systemPrompt, $request->string('message')->toString(), $request->history ?? []);

        if (! $reply) {
            return response()->json(['reply' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau.'], 500);
        }

        return response()->json(['reply' => $reply]);
    }
}
