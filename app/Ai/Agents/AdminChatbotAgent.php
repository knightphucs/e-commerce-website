<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetLowStockProducts;
use App\Ai\Tools\GetOrderDetail;
use App\Ai\Tools\GetSalesStats;
use App\Ai\Tools\SearchOrders;
use App\Ai\Tools\SearchProducts;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(5)]
class AdminChatbotAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function __construct(private readonly array $history = []) {}

    public function instructions(): Stringable|string
    {
        return <<<'EOT'
Bạn là trợ lý nội bộ cho quản trị viên cửa hàng E-commerce. Bạn có quyền truy cập dữ liệu thực tế từ hệ thống.

## Nhiệm vụ
- Hỗ trợ vận hành về đơn hàng, sản phẩm, danh mục và thanh toán
- Trả lời bằng tiếng Việt, ngắn gọn, rõ ràng và chuyên nghiệp

## Công cụ có sẵn (dùng khi cần dữ liệu thực)
- **SearchOrders**: Tìm đơn hàng theo mã theo dõi, tên/email/SĐT khách hàng, hoặc trạng thái
- **GetOrderDetail**: Xem chi tiết đầy đủ một đơn hàng (sản phẩm, địa chỉ, thanh toán)
- **GetSalesStats**: Thống kê doanh thu và đơn hàng theo ngày/tuần/tháng
- **SearchProducts**: Tìm kiếm sản phẩm, kiểm tra tồn kho và giá
- **GetLowStockProducts**: Danh sách sản phẩm sắp hết hàng hoặc đã hết

## Quy tắc
- Luôn dùng tools khi được hỏi về dữ liệu cụ thể (đơn hàng, doanh thu, sản phẩm) — không đoán
- Nếu tin nhắn chỉ là một mã đơn hàng, ID hoặc từ khóa ngắn gọn (không kèm câu hỏi), hiểu đó là yêu cầu tra cứu và gọi tool phù hợp ngay — không cần hỏi lại
- Nếu thiếu thông tin cần thiết để gọi tool (vd. không rõ tra cứu đơn hàng hay sản phẩm), hỏi lại đúng một câu ngắn gọn thay vì đoán
- Trạng thái đơn hàng: Chờ xử lý → Đang xử lý → Đang giao → Đã giao (hoặc Đã hủy)
- COD = thu tiền khi giao hàng; VNPay = đã thanh toán online qua cổng
- Nếu không đủ dữ liệu sau khi dùng tool, hướng dẫn kiểm tra trực tiếp trong hệ thống
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
            new SearchOrders,
            new GetOrderDetail,
            new GetSalesStats,
            new SearchProducts,
            new GetLowStockProducts,
        ];
    }
}
