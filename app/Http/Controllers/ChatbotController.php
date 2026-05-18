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

        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
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

        $messages = collect($request->history ?? [])
            ->map(fn ($msg) => ['role' => $msg['role'], 'content' => $msg['content']])
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
            return response()->json(['reply' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau.'], 500);
        }

        $reply = $response->json('choices.0.message.content', 'Xin lỗi, không thể xử lý yêu cầu của bạn.');

        return response()->json(['reply' => $reply]);
    }
}
