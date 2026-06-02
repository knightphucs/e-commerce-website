<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CustomerChatbotAgent;
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

        $reply = $gemini->send(CustomerChatbotAgent::class, $request->string('message')->toString(), $request->history ?? []);

        if (! $reply) {
            return response()->json(['reply' => 'Xin lỗi, hiện không thể xử lý yêu cầu. Vui lòng thử lại sau.'], 500);
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
