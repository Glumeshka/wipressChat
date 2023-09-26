<?php
namespace App\controllers;

use App\core\Controller;
use App\models\ChatModel;
use App\core\View;

class Chat extends Controller
{
    public function __construct()
    {
        $this->model = new ChatModel();
        $this->view = new View();
    }

    public function index()
    {        
        if(empty($_SESSION['user_id'])){
            header("Location: /");
        }

        $this->pageData['title'] = "Чат";
        $this->pageData['js'] = "/js/chat.js";       
        $this->view->render('chat.phtml', 'template.phtml', $this->pageData);
    }

    public function profile()
    {
        //Для тестов
    }

    public function logout()
    {
        session_destroy();
        header("Location: /");
    }
}