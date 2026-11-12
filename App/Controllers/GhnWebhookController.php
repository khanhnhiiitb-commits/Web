<?php
namespace App\Controllers;

use App\Models\GhnEventModel;
use RuntimeException;
use Throwable;

/** Endpoint cho callback GHN, không yêu cầu cookie/session của người dùng. */
final class GhnWebhookController
{
    private function reply(int $status, string $message): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['success' => $status >= 200 && $status < 300,
            'message' => $message], JSON_UNESCAPED_UNICODE);
    }

    public function receive(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $this->reply(405, 'Method not allowed');
            return;
        }

        // Trên GHN Developer Portal cấu hình custom header:
        // X-GiveTake-Webhook-Token: <giá trị GHN_WEBHOOK_SECRET trên Render>
        $expected = (string) (getenv('GHN_WEBHOOK_SECRET') ?: '');
        $actual = (string) ($_SERVER['HTTP_X_GIVETAKE_WEBHOOK_TOKEN'] ?? '');
        if (strlen($expected) < 32 || $actual === '' || !hash_equals($expected, $actual)) {
            $this->reply(401, 'Unauthorized');
            return;
        }
        if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 65536) {
            $this->reply(413, 'Payload too large');
            return;
        }
        $body = file_get_contents('php://input', false, null, 0, 65537);
        if ($body === false || strlen($body) > 65536) {
            $this->reply(413, 'Payload too large');
            return;
        }
        $payload = json_decode($body, true);
        if (!is_array($payload) || array_is_list($payload)) {
            $this->reply(400, 'Invalid JSON payload');
            return;
        }
        $config = require __DIR__ . '/../Config/ghn.php';
        $shopId = (int) ($config['shop_id'] ?? 0);
        if ($shopId <= 0) {
            $this->reply(503, 'GHN is not configured');
            return;
        }
        try {
            $outcome = (new GhnEventModel())->acceptWebhook($payload, $shopId);
            if ($outcome === 'retry') {
                $this->reply(503, 'Order not yet linked; retry later');
            } else {
                $this->reply(200, $outcome);
            }
        } catch (RuntimeException $e) {
            error_log('[GHN webhook invalid] ' . $e->getMessage());
            $this->reply(422, 'Invalid GHN event');
        } catch (Throwable $e) {
            error_log('[GHN webhook failed] ' . $e->getMessage());
            $this->reply(503, 'Temporary processing error');
        }
    }
}
