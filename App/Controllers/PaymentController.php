<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\PaymentModel;
use App\Services\VnpayClient;
use Throwable;

final class PaymentController extends Controller
{
    private function csrf(): void
    {
        if (($_SERVER['REQUEST_METHOD']??'')!=='POST' ||
            !is_string($_POST['csrf_token']??null) ||
            empty($_SESSION['delivery_csrf']) ||
            !hash_equals((string)$_SESSION['delivery_csrf'],$_POST['csrf_token'])) {
            http_response_code(403); exit('Yêu cầu không hợp lệ.');
        }
    }
    private function tracking(int $id): void
    {
        header('Location: index.php?controller=Delivery&action=tracking&id='.$id, true, 303); exit;
    }
    public function create(): void
    {
        $this->csrf();
        $uid=(int)($_SESSION['user_id']??$_SESSION['user']['id']??0);
        $id=max(0,(int)($_GET['id']??0));
        if (!$uid || !$id) { http_response_code(403); exit('Bạn cần đăng nhập.'); }
        try {
            // Verify configuration before recording a pending payment.
            $client=new VnpayClient();
            $payment=(new PaymentModel())->start($id,$uid);
            $url=$client->paymentUrl($payment['txn_ref'],(int)$payment['amount_vnd'],(string)($_SERVER['REMOTE_ADDR']??'127.0.0.1'));
            header('Location: '.$url, true, 303); exit;
        } catch (Throwable $e) {
            error_log('VNPAY create payment request='.$id.': '.$e->getMessage());
            $_SESSION['delivery_notice']='Không thể khởi tạo thanh toán: '.$e->getMessage();
            $this->tracking($id);
        }
    }
    public function ipn(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        try {
            if (($_SERVER['REQUEST_METHOD']??'')!=='GET') throw new \RuntimeException('Wrong method');
            $client=new VnpayClient();
            if (!$client->verify($_GET)) {
                echo json_encode(['RspCode'=>'97','Message'=>'Invalid signature']); return;
            }
            echo json_encode((new PaymentModel())->applyCallback($_GET));
        } catch (Throwable $e) {
            error_log('VNPAY IPN: '.$e->getMessage());
            echo json_encode(['RspCode'=>'99','Message'=>'Internal error']);
        }
    }
    public function returnUrl(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $valid=false;
        try { $valid=(new VnpayClient())->verify($_GET); } catch (Throwable $e) { error_log('VNPAY return: '.$e->getMessage()); }
        $p=null;
        if ($valid && is_string($_GET['vnp_TxnRef']??null)) $p=(new PaymentModel())->byReference($_GET['vnp_TxnRef']);
        $uid=(int)($_SESSION['user_id']??$_SESSION['user']['id']??0);
        $allowed=$p && $uid>0 && $uid===(int)$p['payer_id'];
        if (!$allowed) { http_response_code(403); echo 'Không thể hiển thị thông tin thanh toán. Vui lòng đăng nhập và kiểm tra lại.'; return; }
        $id=(int)$p['request_id'];
        $status=$p['status'];
        $text=$status==='paid' ? 'Đã xác nhận thanh toán thành công.' : ($status==='failed' ? 'Thanh toán không thành công.' : 'Đang chờ VNPAY xác nhận qua IPN, vui lòng kiểm tra lại sau.');
        echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Thanh toán VNPAY</title><main style="font:16px system-ui;max-width:650px;margin:60px auto;padding:24px"><h2>Kết quả VNPAY Sandbox</h2><p>'.htmlspecialchars($text,ENT_QUOTES,'UTF-8').'</p><p>Trạng thái được xác nhận từ máy chủ, không dựa vào URL trả về.</p><a href="index.php?controller=Delivery&amp;action=tracking&amp;id='.$id.'">Trở lại giao nhận</a></main></html>';
    }
}
