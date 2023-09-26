<?php

namespace App\core;

define("MAX_FILE_SIZE", 1000000);
define('ALLOWED_TYPES', ['image/jpeg']);
define('UPLOAD_DIR', 'avatars');

define("ROOT", dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
define("APP", ROOT . 'App' . DIRECTORY_SEPARATOR);
define("CORE", APP . 'core' . DIRECTORY_SEPARATOR);
define("DATA", APP . 'data' . DIRECTORY_SEPARATOR);
define("MODEL", APP . 'models' . DIRECTORY_SEPARATOR);
define("VIEW", APP . 'views' . DIRECTORY_SEPARATOR);
define("CONTROLLER", APP . 'controllers' . DIRECTORY_SEPARATOR);
define("LAYOUT", VIEW . 'layout' . DIRECTORY_SEPARATOR);
define("LOGS", ROOT . 'logs' . DIRECTORY_SEPARATOR);
define('CONTROLLERS_NAMESPACE', 'App\\controllers\\');

require_once ROOT . '\vendor\autoload.php';