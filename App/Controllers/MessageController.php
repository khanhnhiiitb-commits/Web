<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\MessageModel;

class MessageController extends Controller
{
    private MessageModel $messages;

    public function __construct() { $this->messages = new MessageModel(); }

    private function userId(): int { return (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0); }

    private function requestId(): int { return max(0, (int)($_GET['id'] ?? 0)); }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    private function authorized(): array
    {
        $userId = $this->userId();
        if (!$userId) $this->json(['ok' => false, 'error' => 'Vui lòng đăng nhập.'], 401);
        $conversation = $this->messages->getConversation($this->requestId(), $userId);
        if (!$conversation) $this->json(['ok' => false, 'error' => 'Không tìm thấy cuộc trò chuyện.'], 403);
        return $conversation;
    }

    public function chat(): void
    {
        $userId = $this->userId();
        if (!$userId) {
            header('Location: index.php?controller=Auth&action=login'); exit;
        }
        $conversation = $this->messages->getConversation($this->requestId(), $userId);
        if (!$conversation) { http_response_code(403); echo 'Bạn không có quyền xem cuộc trò chuyện này.'; return; }
        if (empty($_SESSION['chat_csrf'])) $_SESSION['chat_csrf'] = bin2hex(random_bytes(32));
        $this->view('messages/chat', [
            'conversation' => $conversation,
            'userId' => $userId,
            'canSend' => $this->messages->isWritable($conversation['status']),
            'csrfToken' => $_SESSION['chat_csrf'],
        ]);
    }

    public function fetch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        $conversation = $this->authorized();
        $afterId = max(0, (int)($_GET['after_id'] ?? 0));
        $userId = $this->userId();
        $messages = $this->messages->getMessages((int)$conversation['request_id'], $afterId);
        $this->messages->markRead((int)$conversation['request_id'], $userId);
        $this->json(['ok' => true, 'messages' => $messages,
            'can_send' => $this->messages->isWritable($conversation['status']),
            'status' => $conversation['status']]);
    }

    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        $conversation = $this->authorized();
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!$token || !hash_equals((string)($_SESSION['chat_csrf'] ?? ''), $token)) {
            $this->json(['ok' => false, 'error' => 'Phiên gửi tin không hợp lệ, hãy tải lại trang.'], 403);
        }
        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '' || mb_strlen($content, 'UTF-8') > 2000) {
            $this->json(['ok' => false, 'error' => 'Tin nhắn phải có từ 1 đến 2000 ký tự.'], 422);
        }
        try {
            if (!$this->messages->sendMessage((int)$conversation['request_id'], $this->userId(), $content)) {
                $this->json(['ok' => false, 'error' => 'Cuộc trò chuyện đã đóng hoặc bạn không có quyền gửi.'], 403);
            }
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => 'Không thể gửi tin nhắn, vui lòng thử lại.'], 500);
        }
    }
}
