<?php
namespace App\Controllers;
use App\Core\Controller;

class PostController extends Controller 
{
    private $postModel;

    public function __construct() 
    {
        $this->postModel = $this->model('PostModel');
    }

    public function index() 
    {
        $posts = $this->postModel->getAllPosts();
        $this->view('posts/index', ['posts' => $posts]);
    }
}