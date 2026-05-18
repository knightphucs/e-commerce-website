<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('shop.index') }}" class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</a>
            <nav class="flex items-center gap-2">
                <a href="{{ route('shop.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">Sản phẩm</a>
                <a href="{{ route('cart.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
                    Giỏ hàng ({{ array_sum(session('cart', [])) }})
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @session('success')
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ $value }}</div>
        @endsession
        @session('error')
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $value }}</div>
        @endsession
        @yield('content')
    </main>

    <div x-data="customerChatbot()" class="fixed right-3 bottom-3 z-50 sm:right-5 sm:bottom-5">
        <div class="w-[calc(100vw-1.5rem)] max-w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl sm:w-80">
            <div class="flex items-center justify-between bg-gray-900 px-4 py-3 text-white">
                <div>
                    <p class="text-sm font-semibold">Hỗ trợ khách hàng</p>
                    <p class="text-xs text-white/70">Trực tuyến</p>
                </div>
                <button type="button" @click="isMinimized = !isMinimized"
                    class="rounded-md p-1 text-white/70 transition hover:bg-white/10 hover:text-white"
                    :aria-label="isMinimized ? 'Mở rộng chatbot' : 'Thu gọn chatbot'">
                    <svg x-show="!isMinimized" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                    </svg>
                    <svg x-show="isMinimized" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="display: none;">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                </button>
            </div>
            <div x-show="!isMinimized" x-transition>
                <div x-ref="messagesContainer" class="h-72 space-y-3 overflow-y-auto p-4">
                    <template x-for="(message, index) in messages" :key="index">
                        <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="message.role === 'user' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800'"
                                class="max-w-xs rounded-xl px-3 py-2 text-sm" x-text="message.content"></div>
                        </div>
                    </template>
                    <div x-show="isLoading" class="flex justify-start" style="display: none;">
                        <div class="rounded-xl bg-gray-100 px-3 py-2">
                            <div class="flex items-center gap-1">
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400"></span>
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay: 120ms"></span>
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay: 240ms"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="sendMessage()" class="flex gap-2 border-t border-gray-200 p-3">
                    <input x-model="input" type="text" placeholder="Nhập câu hỏi..."
                        :disabled="isLoading"
                        class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none">
                    <button type="submit" :disabled="isLoading || !input.trim()"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-900 text-white disabled:opacity-50"
                        aria-label="Gửi tin nhắn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m22 2-7 20-4-9-9-4Z" />
                            <path d="M22 2 11 13" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function customerChatbot() {
            return {
                isMinimized: false,
                input: '',
                isLoading: false,
                messages: [{ role: 'assistant', content: 'Xin chào! Tôi có thể hỗ trợ bạn về sản phẩm, đơn hàng hoặc thanh toán.' }],
                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        }
                    });
                },
                async sendMessage() {
                    if (!this.input.trim() || this.isLoading) {
                        return;
                    }

                    const userMessage = this.input.trim();
                    this.input = '';
                    this.messages.push({ role: 'user', content: userMessage });
                    this.isLoading = true;
                    this.scrollToBottom();

                    try {
                        const response = await axios.post('{{ route('customer-chatbot.send') }}', {
                            message: userMessage,
                            history: this.messages.slice(-10, -1),
                        });
                        this.messages.push({ role: 'assistant', content: response.data.reply });
                    } catch (error) {
                        this.messages.push({ role: 'assistant', content: 'Xin lỗi, hiện không thể xử lý yêu cầu. Vui lòng thử lại sau.' });
                    } finally {
                        this.isLoading = false;
                        this.scrollToBottom();
                    }
                },
            };
        }
    </script>
</body>

</html>
