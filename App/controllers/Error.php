<?php
namespace App\controllers;

use App\core\Controller;

class Error extends Controller
{
    public function index()
    {
        $this->pageData['title'] = "Ошибка 404";
        $this->view->render('error.phtml', 'template.phtml', $this->pageData);
    }
}