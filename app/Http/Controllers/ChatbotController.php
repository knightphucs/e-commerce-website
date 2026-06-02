<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AdminChatbotAgent;
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

        $reply = $gemini->send(AdminChatbotAgent::class, $request->string('message')->toString(), $request->history ?? []);

        if (! $reply) {
            return response()->json(['reply' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau.'], 500);
        }

        return response()->json(['reply' => $reply]);
    }
}
