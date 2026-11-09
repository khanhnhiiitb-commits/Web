<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\DeliveryModel;

class DeliveryController extends Controller
{
    private DeliveryModel $delivery;
    public function __construct() { $this->delivery = new DeliveryModel(); }
    private function uid(): int { return (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0); }
    private function id(): int { return max(0,(int)($_GET['id'] ?? 0)); }
    private function redirectToTracking(int $id): void
{
    header(
        'Location: index.php?controller=Delivery&action=tracking&id=' . $id
    );
    exit;
}
    private function reject(string $message, int $status=400): void
    {
        http_response_code($status); echo htmlspecialchars($message,ENT_QUOTES,'UTF-8'); exit;
    }
    private function verifyPost(): void
    {
        if ($_SERVER['REQUEST_METHOD']!=='POST') $this->reject('Chỉ hỗ trợ POST.',405);
        $token=(string)($_POST['csrf_token']??'');
        if (!$token || !hash_equals((string)($_SESSION['delivery_csrf']??''),$token))
            $this->reject('CSRF không hợp lệ. Tải lại trang.',403);
    }
    public function tracking(): void
    {
        $uid=$this->uid(); if (!$uid) { header('Location: index.php?controller=Auth&action=login'); exit; }
        $delivery=$this->delivery->getForParticipant($this->id(),$uid);
        if (!$delivery) $this->reject('Không tìm thấy giao dịch hoặc không có quyền.',403);
        if (empty($_SESSION['delivery_csrf'])) $_SESSION['delivery_csrf']=bin2hex(random_bytes(32));
        $this->view('delivery/tracking', ['delivery'=>$delivery,'userId'=>$uid,
            'csrfToken'=>$_SESSION['delivery_csrf']]);
    }
    public function propose(): void
    {
        $this->verifyPost(); $id=$this->id(); $uid=$this->uid();
        if (!$uid || $id<=0) $this->reject('Không hợp lệ.',403);
        $method=(string)($_POST['method']??'');
        $note=trim((string)($_POST['note']??''));
        $meetingTime=null; $meetingLocation=null; $carrier=null; $address=null; $fee=0;
        if (!in_array($method,['pickup','shipping'],true) || mb_strlen($note)>2000)
            $this->reject('Phương thức hoặc ghi chú không hợp lệ.',422);
        if ($method==='pickup') {
            $meetingLocation=trim((string)($_POST['meeting_location']??''));
            $raw=trim((string)($_POST['meeting_time']??''));
            $dt=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$raw);
            if (!$dt || $dt->format('Y-m-d\TH:i')!==$raw || $dt<=new \DateTimeImmutable()
                || !$meetingLocation || mb_strlen($meetingLocation)>255)
                $this->reject('Cần nhập địa điểm và thời gian hẹn hợp lệ trong tương lai.',422);
            $meetingTime=$dt->format('Y-m-d H:i:s');
        } else {
            $carrier=trim((string)($_POST['carrier']??''));
            $address=trim((string)($_POST['shipping_address']??''));
            $raw=trim((string)($_POST['shipping_fee_vnd']??''));
            if (!preg_match('/^(0|[1-9][0-9]*)$/D',$raw)) $this->reject('Phí ship phải là số nguyên VND.',422);
            $fee=(int)$raw;
            if (!$carrier || mb_strlen($carrier)>150 || !$address || mb_strlen($address)>500
                || $fee<0 || $fee>10000000)
                $this->reject('Thông tin vận chuyển không hợp lệ (phí tối đa 10 triệu VND).',422);
        }
        $ok=$this->delivery->propose($id,$uid,[
            'meeting_time'=>$meetingTime,'meeting_location'=>$meetingLocation,'carrier'=>$carrier,
            'address'=>$address,'note'=>$note,'fee'=>$fee]);
        $_SESSION['delivery_notice']=$ok ? 'Đã gửi đề xuất. Chờ người nhận đồng ý.' : 'Không thể đề xuất ở trạng thái hiện tại.';
        $this->redirectToTracking($id);
    }
    public function agree(): void
    {
        $this->verifyPost(); $id=$this->id(); $uid=$this->uid();
        if (!$uid || $id<=0) $this->reject('Không hợp lệ.',403);
        $ok=$this->delivery->agree($id,$uid);
        $_SESSION['delivery_notice']=$ok ? 'Đã đồng ý phương thức giao nhận. Nếu có phí ship, chờ thanh toán VNPay ở Bước 3B.' : 'Không thể đồng ý. Kiểm tra lại trạng thái.';
        $this->redirectToTracking($id);    
    }
}
