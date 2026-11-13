<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\DisputeModel;
use RuntimeException;
use Throwable;

class DisputeController extends Controller
{
    private DisputeModel $disputes;
    public function __construct() { $this->disputes=new DisputeModel(); }
    private function currentUser(): array { return $this->requireLogin(); }
    private function csrf(): string
    {
        if (empty($_SESSION['dispute_csrf'])) $_SESSION['dispute_csrf']=bin2hex(random_bytes(32));
        return $_SESSION['dispute_csrf'];
    }
    private function verifyPost(): void
    {
        if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { http_response_code(405); exit('Method Not Allowed'); }
        if (!hash_equals($this->csrf(),(string)($_POST['csrf_token']??''))) { http_response_code(403); exit('CSRF không hợp lệ.'); }
    }
    private function fail(string $message, string $url): void
    {
        $_SESSION['dispute_error']=$message;
        $this->redirect($url);
    }
    public function index(): void
    {
        $u=$this->currentUser();
        $this->view('disputes/index',['disputes'=>$this->disputes->myDisputes((int)$u['id'])]);
    }
    public function create(): void
    {
        $u=$this->currentUser(); $id=(int)($_GET['id']??0);
        $request=$this->disputes->getRequest($id,(int)$u['id']);
        if (!$request || !in_array($request['status'],['accepted','arranged','in_progress','awaiting_confirmation'],true)) {
            http_response_code(403); exit('Không thể khiếu nại yêu cầu này.');
        }
        $this->view('disputes/create',['request'=>$request,'csrfToken'=>$this->csrf()]);
    }
    public function store(): void
    {
        $u=$this->currentUser(); $this->verifyPost();
        $id=(int)($_POST['request_id']??0); $reason=trim((string)($_POST['reason']??''));
        $desc=trim((string)($_POST['description']??''));
        if ($id<=0 || mb_strlen($reason)<5 || mb_strlen($reason)>255 || mb_strlen($desc)>5000)
            $this->fail('Lý do phải từ 5–255 ký tự; mô tả tối đa 5000 ký tự.','index.php?controller=Request&action=history');
        try { $disputeId=$this->disputes->open($id,(int)$u['id'],$reason,$desc);
            $_SESSION['dispute_notice']='Đã gửi khiếu nại đến quản trị viên.';
            $this->redirect('index.php?controller=Dispute&action=show&id='.$disputeId);
        } catch (Throwable $e) { $this->fail($e instanceof RuntimeException?$e->getMessage():'Không thể gửi khiếu nại.','index.php?controller=Request&action=history'); }
    }
    public function show(): void
    {
        $u=$this->currentUser(); $admin=($u['role']==='admin'); $id=(int)($_GET['id']??0);
        $dispute=$this->disputes->findAccessible($id,(int)$u['id'],$admin);
        if (!$dispute) { http_response_code(404); exit('Không tìm thấy khiếu nại.'); }
        $this->view('disputes/show',['dispute'=>$dispute,'messages'=>$this->disputes->messages($id),
            'csrfToken'=>$this->csrf(),'isAdmin'=>$admin],$admin?'admin_layout':'main');
    }
    public function message(): void
    {
        $u=$this->currentUser(); $this->verifyPost(); $id=(int)($_POST['dispute_id']??0);
        $admin=($u['role']==='admin'); $dispute=$this->disputes->findAccessible($id,(int)$u['id'],$admin);
        if (!$dispute) { http_response_code(404); exit('Không tìm thấy khiếu nại.'); }
        if (!in_array($dispute['status'],['open','reviewing'],true))
            $this->fail('Khiếu nại đã kết thúc, không thể nhắn thêm.','index.php?controller=Dispute&action=show&id='.$id);
        $body=trim((string)($_POST['content']??''));
        if ($body==='' || mb_strlen($body)>3000)
            $this->fail('Tin nhắn phải có nội dung và tối đa 3000 ký tự.','index.php?controller=Dispute&action=show&id='.$id);
        $this->disputes->addMessage($id,(int)$u['id'],$body);
        $this->redirect('index.php?controller=Dispute&action=show&id='.$id);
    }
    public function admin(): void
    {
        $this->requireAdmin();
        $this->view('disputes/admin',['disputes'=>$this->disputes->allDisputes()], 'admin_layout');
    }
    public function update(): void
    {
        $u=$this->requireAdmin(); $this->verifyPost();
        $id=(int)($_POST['dispute_id']??0);
        $status=(string)($_POST['status']??''); $note=trim((string)($_POST['resolution_note']??''));
        if ($id<=0 || mb_strlen($note)>5000) { http_response_code(422); exit('Dữ liệu không hợp lệ.'); }
        try { $this->disputes->updateByAdmin($id,(int)$u['id'],$status,$note);
            $_SESSION['dispute_notice']='Đã cập nhật khiếu nại.';
        } catch (Throwable $e) { $_SESSION['dispute_error']=$e instanceof RuntimeException?$e->getMessage():'Cập nhật thất bại.'; }
        $this->redirect('index.php?controller=Dispute&action=show&id='.$id);
    }
}
