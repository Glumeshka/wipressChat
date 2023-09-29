<?php
// подключаем все нам необходимое
require_once 'vendor\autoload.php';
require_once 'App' . DIRECTORY_SEPARATOR .'core' . DIRECTORY_SEPARATOR . 'Config.php';

use Workerman\Worker;
use App\models\ChatModel;

// создаем массив с подключениями
$connectedClients = [];

// инициализируем вебсокет сервер
$ws_Worker = new Worker('websocket://0.0.0.0:2346');

// получаем доступ к методам для обработки исходящих данных
$methods = new ChatModel();

// установка количества процессов для обработки входящих сообщений, в данном примере я не могу протестировать больше 2 подключений...
$ws_Worker->count = 4;

//план такой, при рукопожатии записываем id и статус online в текущего пользователя
$ws_Worker->onConnect = function ($connection) use (&$connectedClients)
{
    // 
};

$ws_Worker->onMessage = function ($connection, $data) use (&$connectedClients, $methods)
{
    $message = json_decode($data, true);

    if (isset($message['action']))
    {
        $action = $message['action'];

        switch ($action) {
            // стартуем интерфейс
            case 'start':
                $userId = $message['data'];

                $connection->userId = $userId;
                $connection->online = 'online';

                $connectedClients[$connection->userId] = $connection;                
                echo "New connect userId: {$connection->userId} \n";
    
                $userData = $methods->userData($connection->userId);
                $userData['action'] = "connected";
                $userData['online'] = $connection->online;
                
                $jsonData = json_encode($userData);
                $connection->send($jsonData);

                foreach ($connectedClients as $client) {
                    if ($client->userId !== $connection->userId) {
                        $updatedData = $methods->userData($client->userId);

                        $updatedData['action'] = "updated";
                        $updatedData['online'] = $client->online;
                
                        $updatedJsonData = json_encode($updatedData);
                        $client->send($updatedJsonData);
                    }
                }

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

                    foreach ($connectedClients as $client) {
                        if ($client->userId !== $connection->userId) {
                            $updatedData = $methods->userData($client->userId);

                            $updatedData['action'] = "updated";
                            $updatedData['online'] = $client->online;
    
                            $updatedJsonData = json_encode($updatedData);
                            $client->send($updatedJsonData);
                        }
                    }

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
                    
                    foreach ($connectedClients as $client) {
                        if ($client->userId !== $connection->userId) {
                            $updatedData = $methods->userData($client->userId);

                            $updatedData['action'] = "updated";
                            $updatedData['online'] = $client->online;
    
                            $updatedJsonData = json_encode($updatedData);
                            $client->send($updatedJsonData);
                        }
                    }         

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

                        foreach ($connectedClients as $client) {
                            if ($client->userId !== $connection->userId) {
                                $updatedData = $methods->userData($client->userId);

                                $updatedData['action'] = "updated";
                                $updatedData['online'] = $client->online;
        
                                $updatedJsonData = json_encode($updatedData);
                                $client->send($updatedJsonData);
                            }
                        }   

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

                } else {
                    $methods->addGroup($nameGroup, $users);
                    $userData = $methods->userData($id);

                    $userData['action'] = 'group_add';                        

                    $jsonData = json_encode($userData);
                    $connection->send($jsonData);

                    foreach ($connectedClients as $client) {
                        if ($client->userId !== $connection->userId) {
                            $updatedData = $methods->userData($client->userId);
                            $updatedData['action'] = "updated";
                            $updatedData['online'] = $client->online;
    
                            $updatedJsonData = json_encode($updatedData);
                            $client->send($updatedJsonData);
                        }
                    }   
                }
                break;               

            // Клик по чату или группе для открытия окна с сообщениями
            case 'get_board':

                $id         = $message['data']['userId'];
                $chatId     = $message['data']['chatId'];                    

                $userData1  = $methods->userData($id);
                $userData2  = $methods->chatData($id, $chatId);
                $userData   = $userData1 + $userData2;

                $userData['action'] = 'get_board';

                $jsonData = json_encode($userData);
                $connection->send($jsonData);

                foreach ($connectedClients as $client) {
                    if ($client->userId !== $connection->userId) {
                        $updatedData1 = $methods->userData($client->userId);
                        $updatedData2 = $methods->chatData($client->userId, $chatId);
                        $updatedData = $updatedData1 + $updatedData2;

                        $updatedData['action'] = "update_all";
                        $updatedData['online'] = $client->online;
                
                        $updatedJsonData = json_encode($updatedData);
                        $client->send($updatedJsonData);
                    }
                }
                break;

            // Клик по чату или группе для открытия окна с сообщениями
            case 'send_message':

                $id         = $message['data']['userId'];
                $chatId     = $message['data']['chatId'];
                $edit       = $message['data']['edit'];
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

                } else if ($edit !== false) {
                    $methods->updateMessage($msgText, $edit);
                    $userData1  = $methods->userData($id);
                    $userData2  = $methods->chatData($id, $chatId);
                    $userData   = $userData1 + $userData2;

                    $userData['action'] = 'get_board';

                    $jsonData = json_encode($userData);
                    $connection->send($jsonData);

                    foreach ($connectedClients as $client) {
                        if ($client->userId !== $connection->userId) {
                            $updatedData1 = $methods->userData($client->userId);
                            $updatedData2 = $methods->chatData($client->userId, $chatId);
                            $updatedData = $updatedData1 + $updatedData2;

                            $updatedData['online'] = $client->online;                        
                            $updatedData['action'] = "update_all";

                            $updatedJsonData = json_encode($updatedData);
                            $client->send($updatedJsonData);
                        }
                    }
                    
                } else if ($methods->sendMessage($chatId, $id, $msgText) || $edit === false) {
                    $userData1  = $methods->userData($id);
                    $userData2  = $methods->chatData($id, $chatId);
                    $userData   = $userData1 + $userData2;

                    $userData['action'] = 'get_board';

                    $jsonData = json_encode($userData);
                    $connection->send($jsonData);

                    foreach ($connectedClients as $client) {
                        if ($client->userId !== $connection->userId) {
                            $updatedData1 = $methods->userData($client->userId);
                            $updatedData2 = $methods->chatData($client->userId, $chatId);
                            $updatedData = $updatedData1 + $updatedData2;

                            $updatedData['online'] = $client->online;                        
                            $updatedData['action'] = "update_chat";

                            $updatedJsonData = json_encode($updatedData);
                            $client->send($updatedJsonData);
                        }
                    }                    
                }
                break;

            case 'swap_mute':

                $id         = $message['data']['userId'];
                $chatId     = $message['data']['chatId']; 
                $mute       = $message['data']['mute'];

                $methods->swapMuteChats($chatId, $mute);

                $userData1  = $methods->userData($id);
                $userData2  = $methods->chatData($id, $chatId);
                $userData   = $userData1 + $userData2;

                $userData['action'] = 'get_board';
                $jsonData = json_encode($userData);
                $connection->send($jsonData);

                foreach ($connectedClients as $client) {
                    if ($client->userId !== $connection->userId) {
                        $updatedData1 = $methods->userData($client->userId);
                        $updatedData2 = $methods->chatData($client->userId, $chatId);
                        $updatedData = $updatedData1 + $updatedData2;

                        $updatedData['action'] = "update_all";
                        $updatedData['online'] = $client->online;
                
                        $updatedJsonData = json_encode($updatedData);
                        $client->send($updatedJsonData);
                    }
                }
                break;

            case 'delete_message':

                $id         = $message['data']['userId'];
                $id_message = $message['data']['msgId'];
                $chatId     = $message['data']['chatId'];            
                
                $methods->deleteMessage($id_message);

                $userData1  = $methods->userData($id);
                $userData2  = $methods->chatData($id, $chatId);
                $userData   = $userData1 + $userData2;

                $userData['action'] = 'get_board';

                $jsonData = json_encode($userData);
                $connection->send($jsonData);

                foreach ($connectedClients as $client) {
                    if ($client->userId !== $connection->userId) {
                        $updatedData1 = $methods->userData($client->userId);
                        $updatedData2 = $methods->chatData($client->userId, $chatId);
                        $updatedData = $updatedData1 + $updatedData2;

                        $updatedData['action'] = "update_all";
                        $updatedData['online'] = $client->online;
                
                        $updatedJsonData = json_encode($updatedData);
                        $client->send($updatedJsonData);
                    }
                }
                break;

            case 'update_message':

                $id         = $message['data']['userId'];
                $id_message = $message['data']['msgId'];
                $chatId     = $message['data']['chatId'];            
                
                $methods->deleteMessage($id_message);

                $userData1  = $methods->userData($id);
                $userData2  = $methods->chatData($id, $chatId);
                $userData   = $userData1 + $userData2;

                $userData['action'] = 'get_board';

                $jsonData = json_encode($userData);
                $connection->send($jsonData);

                foreach ($connectedClients as $client) {
                    if ($client->userId !== $connection->userId) {
                        $updatedData1 = $methods->userData($client->userId);
                        $updatedData2 = $methods->chatData($client->userId, $chatId);
                        $updatedData = $updatedData1 + $updatedData2;

                        $updatedData['action'] = "update_all";
                        $updatedData['online'] = $client->online;
                
                        $updatedJsonData = json_encode($updatedData);
                        $client->send($updatedJsonData);
                    }
                }
                break;
    
        }
    };
};  

$ws_Worker->onClose = function ($connection) use (&$connectedClients)
{
    foreach ($connectedClients as $connection->userId => $conn) {
        if ($conn === $connection) {
            unset($connectedClients[$connection->userId]);
            break;
        }
    }    
    echo "Close connect user: {$connection->userId}\n";
};

// Run worker
Worker::runAll();