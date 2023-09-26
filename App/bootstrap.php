<?php

namespace App;

use App\core\Route;

session_start();

require_once 'core' . DIRECTORY_SEPARATOR . 'Config.php';

Route::start(); // запускаем маршрутизатор