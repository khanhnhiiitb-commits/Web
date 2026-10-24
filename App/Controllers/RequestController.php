<?php

class RequestController
{
    private $requestModel;

    public function __construct()
    {
        require_once "./App/Models/RequestModel.php";
        $this->requestModel = new \App\Models\RequestModel();
    }

    // Danh sách đồ mình đi xin
    public function myRequests()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header("Location: " . BASE_URL . "user/login");
            exit();
        }

        $requests = $this->requestModel->getMyRequests($userId);

        require_once "./views/requests/my_requests.php";
    }

    // Danh sách người xin đồ của mình
    public function manageMyItems()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            header("Location: " . BASE_URL . "user/login");
            exit();
        }

        $requests = $this->requestModel->getRequestsForMyItems($userId);

        require_once "./views/requests/manage_my_items.php";
    }

    // Đồng ý cho
    public function accept($requestId = null)
    {
        $this->checkPost();

        $userId = $this->getUserId();

        if (!$userId || !$requestId) {
            die("Yêu cầu không hợp lệ.");
        }

        $success = $this->requestModel->acceptRequest(
            (int) $requestId,
            (int) $userId
        );

        $_SESSION['request_message'] = $success
            ? "Đã chấp nhận yêu cầu."
            : "Không thể chấp nhận yêu cầu. Vui lòng kiểm tra lại.";

        header("Location: " . BASE_URL . "request/manageMyItems");
        exit();
    }

    // Từ chối
    public function reject($requestId = null)
    {
        $this->checkPost();

        $userId = $this->getUserId();

        if (!$userId || !$requestId) {
            die("Yêu cầu không hợp lệ.");
        }

        $success = $this->requestModel->rejectRequest(
            (int) $requestId,
            (int) $userId
        );

        $_SESSION['request_message'] = $success
            ? "Đã từ chối yêu cầu."
            : "Không thể từ chối yêu cầu.";

        header("Location: " . BASE_URL . "request/manageMyItems");
        exit();
    }

    private function getUserId()
    {
        return $_SESSION['user']['id'] ?? null;
    }

    private function checkPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Phương thức không được hỗ trợ.");
        }
    }
}