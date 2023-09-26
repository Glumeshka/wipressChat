<?php
namespace App\models;
use App\core\Model;

class ChatModel extends Model
{
    public function userData($id)
    {
        $userData = [
            'id'        => intval($id),
            'nickName'  => $this->getProfile($id)[0]['nick'],
            'avatar'    => $this->getProfile($id)[0]['avatar'],
            'chats'     => $this->getChats($id),
            'groups'    => $this->getGroups($id),
            'search'    => $this->getSearch($id),
            'online'    => 1,
        ];
       
        return $userData;
    }

    public function chatData($id, $chat_id) // мы хочем получить: свой ник, свой аватар, чужой ник, чужик ники, аватары,
                                            // количество сообщений, свои сообщения, чужие сообщения
    {        
        
        $hes_id = $this->getHeId($id, $chat_id); // массив с цифрами в виде строк

        $hes_members = [];

        foreach ($hes_id as $ids) {
            $member = [];
            $member['id'] = $ids;
            $member['nick'] = $this->getProfile($ids)[0]['nick'];
            $member['avatar'] = $this->getProfile($ids)[0]['avatar'];
            $hes_members[] = $member;
        }

        $userData = [

            'name_chat'       => $this->getNameChat($chat_id),
            'hes_members'     => $hes_members,            
            'messages'        => $this->getBoard($chat_id),                
            'messages_count'  => $this->getCountmsg($chat_id)

        ];
       
        return $userData;
    }

    public function getBoard($chat_id)
    {
        $sql = "SELECT * FROM messages WHERE chat_id = :chat_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":chat_id", intval($chat_id), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $res; // message_id, chat_id, sender_id, message_text, timestamp
    }

    public function getNameChat($chat_id)
    {
        $sql = "SELECT * FROM chats WHERE chat_id = :chat_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":chat_id", intval($chat_id), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);        

        if(!is_null($res['group_name'])){
            $name = $res['group_name'];
            return $name;
        }
        return false;
    }

    public function getUser($id)
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();        
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);        
        $user = $res["email"];
        
        return $user;
    }

    public function getChats($id)
    {           
        $sql =  "SELECT c.chat_id,
                (
                    SELECT u.nick
                    FROM profile u
                    JOIN users ON u.user_id = users.user_id
                    WHERE u.user_id != :id
                    AND JSON_CONTAINS(c.participants, JSON_ARRAY(u.user_id))
                    LIMIT 1
                ) AS name,
                (
                    SELECT u.avatar
                    FROM profile u
                    JOIN users ON u.user_id = users.user_id
                    WHERE u.user_id != :id
                    AND JSON_CONTAINS(c.participants, JSON_ARRAY(u.user_id))
                    LIMIT 1
                ) AS avatar,
                (
                    SELECT u.user_id
                    FROM profile u
                    JOIN users ON u.user_id = users.user_id
                    WHERE u.user_id != :id
                    AND JSON_CONTAINS(c.participants, JSON_ARRAY(u.user_id))
                    LIMIT 1
                ) AS user_id
                FROM chats c
                WHERE c.chat_type = 'Пользовательский'
                AND JSON_CONTAINS(c.participants, JSON_ARRAY(:id))";               

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();

        $chats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $chats; // chat_id, name, avatar, user_id
    }

    public function getSearch($id)
    {
        $sql = "SELECT user_id, result FROM
                (
                    SELECT users.user_id, CASE
                    WHEN profile.hideEmail = 0 THEN users.email
                    WHEN profile.hideEmail = 1 THEN profile.nick
                    END AS result
                    FROM users
                    JOIN profile ON users.user_id = profile.user_id
                    WHERE users.user_id != :user_id

                    UNION ALL

                    SELECT users.user_id, profile.nick AS result
                    FROM users
                    JOIN profile ON users.user_id = profile.user_id
                    WHERE profile.hideEmail = 0
                    AND users.user_id != :user_id
                ) AS temp_table
                GROUP BY user_id, result";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result; // user_id: result
    }

    public function getGroups($id)
    {
        $sql = "SELECT c.chat_id, c.group_name
                FROM chats c 
                WHERE c.chat_type = 'Групповой'
                AND JSON_CONTAINS(c.participants, JSON_ARRAY(:id))";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();
        
        $groups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $groups; // chat_id: group_name
    }

    public function getMessage($id_message)
    {
        $sql = "SELECT * FROM messages WHERE message_id = :message_id";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":message_id", intval($id_message), \PDO::PARAM_INT);
        $stmt->execute();        
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);        
        $msg = $res["message_text"];

        return $msg;
    }

    public function sendMessage($id_chat, $user, $message)
    {
        $sql = "INSERT INTO messages (chat_id, sender_id, message_text, timestamp)
                VALUES (:chat_id, :sender_id, :message_text, :timestamp)";

        $date = (new \DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":chat_id", intval($id_chat), \PDO::PARAM_INT);
        $stmt->bindValue(":sender_id", intval($user), \PDO::PARAM_INT);
        $stmt->bindValue(":message_text", $message, \PDO::PARAM_STR);
        $stmt->bindParam(':timestamp', $date, \PDO::PARAM_STR);
        if (empty(trim(strip_tags($message)))){
            return false;
        } else {
            $stmt->execute();
            return true;
        }        
    }

    public function updateMessage($message, $id_message)
    {   
        $sql = "UPDATE messages SET message_text = :message_text
                WHERE messages.message_id = :message_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":message_text", $message, \PDO::PARAM_STR);
        $stmt->bindValue(":message_id", intval($id_message), \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getProfile($id)
    {
        $sql = "SELECT * FROM users
                RIGHT JOIN profile ON users.user_id = profile.user_id
                WHERE users.user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $res;
    }

    public function updateNick($id, $nick, $hideEmail)
    {        
        $sql = "UPDATE profile SET nick = :nick, hideEmail = :hideEmail
                WHERE profile.user_id = :id";

        $stmt = $this->db->prepare($sql);
        $default = $this->getUser($id);
        $nick = $nick === '' ? $default : $nick;
        $stmt->bindValue(":nick", $nick, \PDO::PARAM_STR);
        $stmt->bindValue(":hideEmail", intval($hideEmail), \PDO::PARAM_INT);
        $stmt->bindValue(":id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateAvatar($id, $avatar)
    {        
        $sql = "UPDATE profile SET avatar = :avatar
                WHERE profile.user_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":avatar", $avatar, \PDO::PARAM_STR);
        $stmt->bindValue(":id", intval($id), \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function duplicateChats($array)
    {
        $sql = "SELECT participants as all_participants FROM chats;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $json = $stmt->fetchAll(\PDO::FETCH_NUM);              
        
        $numericData = array();

        foreach ($json as $innerArray) {
            $string = $innerArray[0];
            $string = trim($string, "[]");
            $numbers = explode(", ", $string);
            $numericData[] = $numbers;
        }          

        foreach ($numericData as $chats) {
            if (empty(array_diff($array, $chats)) &&
                empty(array_diff($chats, $array))) {
                return true;
            }
        }    
        return false;
    }

    public function addChat($user1, $user2)
    {
        $participantsArray = array($user1, $user2);
        $participantsJson = json_encode($participantsArray);    
       
        $sql = "INSERT INTO chats (chat_type, participants, group_name)
                VALUES ('Пользовательский', :participants, NULL)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':participants', $participantsJson, \PDO::PARAM_STR);
        if ($user1 == '0' || $user2 == '0' ||
            is_null($user1) || is_null($user2) ||
            $this->duplicateChats($participantsArray)){
            return false;
        } else if (!$this->duplicateChats($participantsArray)) {
            $stmt->execute();
            return true;
        }
    }

    public function addGroup($nameGroup, $users)
    {
        $participantsJson = json_encode($users);        

        $sql = "INSERT INTO chats (chat_type, participants, group_name)
                VALUES ('Групповой', :participants, :group_name)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':participants', $participantsJson, \PDO::PARAM_STR);
        $stmt->bindParam(':group_name', trim(strip_tags($nameGroup)), \PDO::PARAM_STR);
        if ($this->duplicateChats($users)){
            return false;
        } else if (!$this->duplicateChats($users)) {
            $stmt->execute();
            return true;
        }        
    }

    public function duplicateName($name)
    {
        $sql = "SELECT nick FROM profile";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $names = $stmt->fetchAll(\PDO::FETCH_NUM);        
        
        foreach ($names as $thisName) {
            if ($thisName[0] == $name) {
                return true;
            }
        }    
        return false;
    }

    public function duplicateGroupName($name)
    {
        $sql = "SELECT group_name FROM chats";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $names = $stmt->fetchAll(\PDO::FETCH_NUM);              
        
        foreach ($names as $thisName) {
            if ($thisName[0] == $name) {
                return true;
            }
        }    
        return false;
    }

    public function getHeId($id, $chat_id)
    {
        $sql = "SELECT participants FROM chats 
                WHERE chat_id = :chat_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":chat_id", intval($chat_id), \PDO::PARAM_INT);
        $stmt->execute();
        $json = $stmt->fetchAll(\PDO::FETCH_NUM);

        $numericData = array();

        foreach ($json as $innerArray) {
            $string = $innerArray[0];
            $string = trim($string, "[]");
            $numbers = explode(", ", $string);
            $numericData[] = $numbers;
        }
        $idArray = [strval($id)];

        $result = array_diff($numericData[0], $idArray);

        return $result;
    }

    public function getCountmsg($chat_id)
    {
        $sql = "SELECT COUNT(message_text) AS message_count
                FROM messages
                WHERE chat_id = :chat_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":chat_id", intval($chat_id), \PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetch(\PDO::FETCH_NUM);

        return $count;       
    }
}