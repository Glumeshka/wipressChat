<?php
namespace App\controllers;

use App\core\Controller;
use App\models\MainModel;
use App\core\View;

class Main extends Controller
{
    public function __construct()
    {
        $this->model = new MainModel();
        $this->view = new View();
    }

    public function index()
    {
        $this->pageData['title'] = "Вход в чат";
        $this->pageData['js'] = "/js/login.js";        
        if (!empty($_SESSION['message'])) {
            $this->pageData['message'] = $_SESSION['message'];
        }
        $this->view->render('main.phtml', 'template.phtml', $this->pageData);
        unset($_SESSION['message']);
        unset($_SESSION['email']);
    }

    public function logon()
    {        
        $login = trim(strtolower($_POST['email']));
        $password = trim($_POST['password']);
        $this->model->loginUser($login, $password);
    }
    
    public function registration()
    {
        unset($_SESSION['email']);
        $login = trim(strtolower($_POST['email']));        
        $password = trim($_POST['password']);
        $password2 = trim($_POST['password2']);
        if($password === $password2){
            $this->model->regUser($login, $password);
        } else {
            $_SESSION['message'] = "Пароли не совпадают!";
            header("Location: /");
        }        
    }
}