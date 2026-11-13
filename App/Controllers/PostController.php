<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\PostModel;

class PostController extends Controller
{
    private PostModel $postModel;

    public function __construct()
    {
        $this->postModel = $this->model('PostModel');
    }

    public function index(): void
    {
        $posts = $this->postModel->getPublishedPosts();
        $commentsByPost = [];
        foreach ($posts as $post) {
            $commentsByPost[(int)$post['id']] = $this->postModel->getComments((int)$post['id']);
        }
        $_SESSION['post_csrf'] ??= bin2hex(random_bytes(32));
        $this->view('posts/index', [
            'posts' => $posts,
            'commentsByPost' => $commentsByPost,
            'postCsrf' => $_SESSION['post_csrf'],
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $_SESSION['post_csrf'] ??= bin2hex(random_bytes(32));
        $this->view('posts/create', ['postCsrf' => $_SESSION['post_csrf']]);
    }

    public function store(): void
    {
        $user = $this->requireLogin();
        $this->verifyPostCsrf();
        $title = trim((string)($_POST['title'] ?? ''));
        $body = trim((string)($_POST['content'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255 || $body === '') {
            $_SESSION['post_error'] = 'Vui lòng nhập tiêu đề và nội dung hợp lệ.';
            $this->redirect('index.php?controller=Post&action=create');
        }
        try {
            $this->postModel->createPost((int)$user['id'], $title, $body);
            $_SESSION['post_success'] = 'Đăng bài viết thành công.';
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['post_error'] = 'Không thể đăng bài viết.';
        }
        $this->redirect('index.php?controller=Post&action=index');
    }

    public function comment(): void
    {
        $user = $this->requireLogin();
        $this->verifyPostCsrf();
        $postId = (int)($_POST['post_id'] ?? 0);
        $comment = trim((string)($_POST['comment_text'] ?? ''));
        if ($postId <= 0 || $comment === '' || mb_strlen($comment) > 2000) {
            $_SESSION['post_error'] = 'Bình luận không hợp lệ (tối đa 2000 ký tự).';
            $this->redirect('index.php?controller=Post&action=index');
        }
        try {
            if (!$this->postModel->addComment($postId, (int)$user['id'], $comment)) {
                $_SESSION['post_error'] = 'Bài viết không tồn tại hoặc chưa được xuất bản.';
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['post_error'] = 'Không thể gửi bình luận.';
        }
        $this->redirect('index.php?controller=Post&action=index');
    }

    private function verifyPostCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !hash_equals((string)($_SESSION['post_csrf'] ?? ''), (string)($_POST['csrf'] ?? '')) ||
            empty($_SESSION['post_csrf'])) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }
    }
}
