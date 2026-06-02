<?php

namespace App\Ai\Agents;

use App\Ai\Tools\SearchProducts;
use App\Ai\Tools\TrackOrder;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(5)]
class CustomerChatbotAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function __construct(private readonly array $history = []) {}

    public function instructions(): Stringable|string
    {
        return <<<'EOT'
Bạn là trợ lý hỗ trợ khách hàng của cửa hàng E-commerce. Bạn có thể tra cứu thông tin thực tế từ hệ thống.

## Nhiệm vụ
- Hỗ trợ khách hàng về sản phẩm, đơn hàng, thanh toán và vận chuyển
- Trả lời bằng tiếng Việt, thân thiện, ngắn gọn và chính xác

## Công cụ có sẵn
- **TrackOrder**: Tra cứu trạng thái đơn hàng theo mã theo dõi — dùng khi khách hỏi về đơn hàng cụ thể
- **SearchProducts**: Tìm kiếm sản phẩm đang bán theo tên hoặc danh mục

## Quy tắc
- Khi khách hỏi về đơn hàng, yêu cầu cung cấp mã theo dõi rồi dùng TrackOrder để tra cứu thực tế
- Khi khách hỏi sản phẩm, dùng SearchProducts để tìm hàng đang có sẵn
- Không bịa thông tin — nếu tool không trả về kết quả, hướng dẫn khách liên hệ cửa hàng
- COD = thanh toán khi nhận hàng; VNPay = thanh toán online
- Chính sách đổi trả: trong 7 ngày kể từ ngày nhận hàng
- Thời gian giao hàng dự kiến: 2–5 ngày làm việc
EOT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return collect($this->history)
            ->filter(fn (array $message): bool => isset($message['role'], $message['content']))
            ->map(fn (array $message): Message => new Message($message['role'] === 'assistant' ? 'assistant' : 'user', $message['content']));
    }

    public function tools(): iterable
    {
        return [
            new TrackOrder,
            new SearchProducts,
        ];
    }
}
