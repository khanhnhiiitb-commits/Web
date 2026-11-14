<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

final class PaymentModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function summary(int $requestId): ?array
    {
        $q = $this->db->prepare("SELECT p.status, p.txn_ref, p.amount_vnd, p.payer_id, p.created_at
            FROM vnpay_payments p WHERE p.request_id = :id ORDER BY p.id DESC LIMIT 1");
        $q->execute([':id'=>$requestId]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function isPaid(int $requestId): bool
    {
        $q = $this->db->prepare("SELECT 1 FROM vnpay_payments WHERE request_id=:id AND status='paid' LIMIT 1");
        $q->execute([':id'=>$requestId]);
        return (bool)$q->fetchColumn();
    }
    public function start(int $requestId, int $userId): array
    {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT ir.status, ir.delivery_method, ir.requester_id,
                   i.user_id AS giver_id, d.shipping_fee_vnd, d.payment_type_id, d.agreed_at,
                   d.tracking_number, d.ghn_status
                   FROM item_requests ir JOIN items i ON i.id=ir.item_id
                   JOIN delivery_details d ON d.request_id=ir.id
                   WHERE ir.id=:id FOR UPDATE");
            $q->execute([':id'=>$requestId]);
            $d=$q->fetch(PDO::FETCH_ASSOC);
            if (!$d || $d['status'] !== 'arranged' || $d['delivery_method'] !== 'shipping' ||
                empty($d['agreed_at']) || !empty($d['tracking_number']) || !empty($d['ghn_status']) ||
                !in_array((int)$d['payment_type_id'], [1,2], true)) {
                throw new RuntimeException('Giao dịch chưa đủ điều kiện thanh toán.');
            }
            $payer=(int)$d['payment_type_id']===1 ? (int)$d['giver_id'] : (int)$d['requester_id'];
            if ($userId !== $payer) throw new RuntimeException('Bạn không phải bên chịu phí vận chuyển.');
            $amount=(int)$d['shipping_fee_vnd'];
            if ($amount < 5000 || $amount > 10000000) throw new RuntimeException('Cước phí phải từ 5.000 đến 10.000.000 đồng để dùng VNPAY Sandbox.');
            $q=$this->db->prepare('SELECT id, status FROM vnpay_payments WHERE request_id=:id ORDER BY id DESC LIMIT 1 FOR UPDATE');
            $q->execute([':id'=>$requestId]);
            $last=$q->fetch(PDO::FETCH_ASSOC);
            if ($last && $last['status']==='paid') throw new RuntimeException('Giao dịch đã thanh toán.');
            if ($last && $last['status']==='pending') {
                // Do not create a second transaction while a callback for the first is possible.
                throw new RuntimeException('Có giao dịch VNPAY đang chờ kết quả. Hãy kiểm tra hoặc đối soát trước khi thanh toán lại.');
            }
            $ref='GT' . $requestId . bin2hex(random_bytes(8));
            $q=$this->db->prepare("INSERT INTO vnpay_payments (request_id,payer_id,amount_vnd,txn_ref,status)
                                      VALUES (:request,:payer,:amount,:ref,'pending')");
            $q->execute([':request'=>$requestId,':payer'=>$payer,':amount'=>$amount,':ref'=>$ref]);
            $this->db->commit();
            return ['txn_ref'=>$ref,'amount_vnd'=>$amount];
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }
    public function applyCallback(array $input): array
    {
        $ref=(string)($input['vnp_TxnRef'] ?? '');
        $amount=(string)($input['vnp_Amount'] ?? '');
        if (!preg_match('/^[A-Za-z0-9]{1,100}$/D',$ref) || !ctype_digit($amount)) return ['RspCode'=>'99','Message'=>'Invalid request'];
        $this->db->beginTransaction();
        try {
            $q=$this->db->prepare('SELECT * FROM vnpay_payments WHERE txn_ref=:ref FOR UPDATE');
            $q->execute([':ref'=>$ref]);
            $p=$q->fetch(PDO::FETCH_ASSOC);
            if (!$p) { $this->db->rollBack(); return ['RspCode'=>'01','Message'=>'Order not found']; }
            if ((string)((int)$p['amount_vnd']*100)!==$amount) { $this->db->rollBack(); return ['RspCode'=>'04','Message'=>'Invalid amount']; }
            if ($p['status']!=='pending') { $this->db->rollBack(); return ['RspCode'=>'02','Message'=>'Order already confirmed']; }
            $success=($input['vnp_ResponseCode'] ?? '')==='00' && ($input['vnp_TransactionStatus'] ?? '')==='00';
            $status=$success ? 'paid' : 'failed';
            // Prevent two successful payments for the same request from being recorded.
            if ($success) {
                $q=$this->db->prepare("SELECT 1 FROM vnpay_payments WHERE request_id=:id AND status='paid' AND id<>:pid LIMIT 1");
                $q->execute([':id'=>$p['request_id'],':pid'=>$p['id']]);
                if ($q->fetchColumn()) { $this->db->rollBack(); return ['RspCode'=>'02','Message'=>'Order already paid']; }
            }
            $q=$this->db->prepare('UPDATE vnpay_payments SET status=:status, vnp_transaction_no=:no, vnp_response_code=:response, paid_at=IF(:is_paid=1,NOW(),NULL), updated_at=NOW() WHERE id=:id');
            $q->execute([':status'=>$status, ':no'=>substr((string)($input['vnp_TransactionNo'] ?? ''),0,100), ':response'=>substr((string)($input['vnp_ResponseCode'] ?? ''),0,10), ':is_paid'=>(int)$success, ':id'=>$p['id']]);
            $this->db->commit();
            return ['RspCode'=>'00','Message'=>'Confirm Success'];
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); error_log('VNPAY callback: '.$e->getMessage()); return ['RspCode'=>'99','Message'=>'Unknown error']; }
    }
    public function byReference(string $reference): ?array
    {
        $q=$this->db->prepare('SELECT request_id, payer_id, amount_vnd, status FROM vnpay_payments WHERE txn_ref=:ref LIMIT 1');
        $q->execute([':ref'=>$reference]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
