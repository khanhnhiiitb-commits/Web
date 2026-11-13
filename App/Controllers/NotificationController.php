<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\NotificationModel;

class NotificationController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $model = $this->model('NotificationModel');
        $this->view('notifications/index', [
            'notifications' => $model->getForUser((int)$user['id'])
        ]);
    }
}
