<?php
require_once 'vendor\autoload.php';
require_once 'App' . DIRECTORY_SEPARATOR .'core' . DIRECTORY_SEPARATOR . 'Config.php';

use Workerman\Worker;
use App\models\ChatModel;

$connectedClients = [];

$ws_Worker = new Worker('websocket://0.0.0.0:2346');

$ws_Worker->count = 4;

//Emitted when new connection come
$ws_Worker->onConnect = function ($connection) use (&$connectedClients)
{
    $clientId = uniqid();
    
    $connectedClients[$clientId] = $connection;

    $methods = new ChatModel();

    // получение первоначальных данных     

    $connection->onMessage = function ($connection, $data) use (&$connectedClients, $methods)
    { 
        $message = json_decode($data, true);        

        if (isset($message['action']))
        {
            $action = $message['action'];
            
            switch ($action) {

                // стартуем интерфейс
                case 'start':

                    $json_data = $message['data'];
        
                    $userData = $methods->userData($json_data);
                    $userData['action'] = "connected";
                    
                    $jsonData = json_encode($userData);
                    $connection->send($jsonData);
                    break;

                // Добавления чата из списка
                case 'chat_add':

                    $user1 = $message['data']['userSender'];
                    $user2 = $message['data']['userRecieved'];

                    if($methods->addChat($user1, $user2)) {

                        $userData = $methods->userData($user1);
                        $userData['action'] = "chat_add";

                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);                          

                    } else {

                        $userData = $methods->userData($user1);
                        
                        $userData['action'] = 'chat_add';
                        $userData['message'] = 'Такой чат уже создан!';
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    }
                    break;
                
                // Окно смены Ника и сокрытия Email
                case 'change_nick':

                    $id = $message['data']['userId'];
                    $nick = trim(strip_tags($message['data']['nickName']));
                    $hideEmail = $message['data']['hideEmail'];
                    
                    if(!$methods->duplicateName($nick)) {

                        $methods->updateNick($id, $nick, $hideEmail);

                        $userData = $methods->userData($id);
                        $userData['action'] = 'change_name';
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else {

                        $userData = $methods->userData($id);

                        $userData['action'] = 'change_name';
                        $userData['message'] = 'Такой ник уже занят!';
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);  

                    }
                    break;
    
                // Окно смены Аватара
                case 'change_avatar':

                    $id   = $message['data']['userId'];
                    $name = trim(strip_tags($message['data']['imageData']['name']));
                    $type = $message['data']['imageData']['type'];
                    $size = intval($message['data']['imageData']['size']);
                    
                    $imageData = $message['data']['imageData']['data'];
                    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                    $imageData = str_replace(' ', '+', $imageData);
                    $image = base64_decode($imageData);
                    $filePath = UPLOAD_DIR . '/' . $name;

                    if ($size > MAX_FILE_SIZE) {

                        $userData = $methods->userData($id);

                        $userData['action'] = 'change_avatar';
                        $userData['message'] = 'Недопустимый размер файла ' . $name;

                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else if (!in_array($type, ALLOWED_TYPES)) {

                        $userData = $methods->userData($id);

                        $userData['action'] = 'change_avatar';
                        $userData['message'] = 'Недопустимый формат файла ' . $name;
                        
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else if (!file_put_contents($filePath, $image)) {

                        $userData = $methods->userData($id);

                        $userData['action'] = 'change_avatar';
                        $userData['message'] = 'Ошибка загрузки файла ' . $name;
                        
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else {

                        if (file_put_contents($filePath, $image)) {

                            $methods->updateAvatar($id, $filePath);
                            $userData = $methods->userData($id);

                            $userData['action'] = 'change_avatar';
                            
                            $jsonData = json_encode($userData);
                            $connection->send($jsonData);

                        } else {

                            $userData = $methods->userData($id);

                            $userData['action'] = 'change_avatar';
                            $userData['message'] = 'Ошибка перемещения файла ' . $name;
                            
                            $jsonData = json_encode($userData);
                            $connection->send($jsonData);
                        }
                    }
                    break;                

                // Окно создания группы
                case 'group_add':

                    $id         = $message['data']['userId'];
                    $nameGroup  = trim(strip_tags($message['data']['groupData']['nameGroup']));
                    $users      = $message['data']['groupData']['members'];

                    if($methods->duplicateGroupName($nameGroup)) {

                        $userData = $methods->userData($id);

                        $userData['action'] = 'group_add';
                        $userData['message'] = 'Такое названиее Г/Ч уже занято';
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else if (!$methods->addGroup($nameGroup, $users)){

                        $userData = $methods->userData($id);
                        $userData['action'] = 'group_add';
                        $userData['message'] = 'Такой Г/Ч уже есть';
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    } else if ($methods->addGroup($nameGroup, $users)){
                        
                        $userData = $methods->userData($id);

                        $userData['action'] = 'group_add';                        
    
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);

                    }
                    break;               

                // Клик по чату или группе для открытия окна с сообщениями
                case 'get_board':

                    $id         = $message['data']['userId'];
                    $chatId     = $message['data']['chatId'];                    

                    $userData1  = $methods->userData($id);
                    $userData2  = $methods->chatData($id, $chatId);
                    $userData = $userData1 + $userData2;

                    $userData['action'] = 'get_board';

                    $jsonData = json_encode($userData);
                    $connection->send($jsonData); 

                    break;

                // Клик по чату или группе для открытия окна с сообщениями
                case 'send_message':
                    $id         = $message['data']['userId'];
                    $chatId     = $message['data']['chatId'];
                    $msgText    = trim(strip_tags($message['data']['msgText']));
                  
                    if(empty($chatId) || empty($id)) {
                        $userData1  = $methods->userData($id);
                        $userData2  = $methods->chatData($id, $chatId);
                        $userData   = $userData1 + $userData2;
                        $userData['action'] = 'get_board';
                        $userData['message'] = 'Выберите пользователя!';
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData); 

                    } else if (empty($msgText)) {
                        $userData1  = $methods->userData($id);
                        $userData2  = $methods->chatData($id, $chatId);
                        $userData   = $userData1 + $userData2;
                        $userData['action'] = 'get_board';
                        $userData['message'] = 'Нельзя отправлять пустую строку';
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData); 

                    } else if ($methods->sendMessage($chatId, $id, $msgText)) {
                        $userData1  = $methods->userData($id);
                        $userData2  = $methods->chatData($id, $chatId);
                        $userData   = $userData1 + $userData2;
                        $userData['action'] = 'get_board';
                        $jsonData = json_encode($userData);
                        $connection->send($jsonData);
                    }    
                    break;
            }
        };
    };    
    echo "New connect user: {$clientId} \n";
};

$ws_Worker->onMessage = function ($connection, $data) {
    $connection->send($data);
};

$ws_Worker->onClose = function ($connection) use (&$connectedClients, &$isFirstConnect)
{
    foreach ($connectedClients as $clientId => $conn) {
        if ($conn === $connection) {
            unset($connectedClients[$clientId]);
            break;
        }
    }    
    echo "Close connect user: {$clientId}\n";
};

// Run worker
Worker::runAll();