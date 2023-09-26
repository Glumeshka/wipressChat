<?php
namespace App\controllers;

use App\core\Controller;
use App\models\MainModel;
use App\core\View;

class Very extends Controller
{
    public function __construct()
    {
        $this->model = new MainModel();
        $this->view = new View();
    }

    public function index()
    {
        if(empty($_SESSION['email'])){
            header("Location: /");
        }

        $this->pageData['title'] = "Подтверждение Регистрации";
        $this->pageData['js'] = "/js/login.js";        
        $this->pageData['email'] = $_SESSION['email'];

        if (!empty($_SESSION['message'])) {
            $this->pageData['message'] = $_SESSION['message'];
        }
        
        $this->view->render('very.phtml', 'template.phtml', $this->pageData);
        unset($_SESSION['message']);        
    }

    public function confirm()
    {
        $login = $_SESSION['email'];
        $code = $_POST['code'];
        $this->model->veryRegUser($login, $code);        
    }
}