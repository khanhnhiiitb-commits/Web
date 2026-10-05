<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
    $categoryModel = $this->model('CategoryModel');
    $categories = $categoryModel->getAll(); 

    echo '<pre>';
    print_r($categories);
    die(); 
}
}