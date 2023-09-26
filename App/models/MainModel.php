<?php
namespace App\models;
use App\core\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MainModel extends Model
{
    // получение данных пользователя по логину
    public function getUser($login)
    {
        $sql = "SELECT * FROM users WHERE email = :email";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":email", $login, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $user = $res;
        return $user;        
    }

    // получение инфы о наличии такого пользователя
    public function checkUser($login)
    {
        $user = $this->getUser($login)['email'];

        if($user === $login) {
            return true;
        } else {            
            return false;
        }
    }

    // проверка на верификацию пользователя
    public function veryUser($login)
    {
        $user = $this->getUser($login)['verification'];

        if($user == 1) {
            return true;
        } else {            
            return false;
        }
    }

    // первый этап регистрации
    public function regUser($login, $password)
    {
        $password = password_hash($password, PASSWORD_ARGON2ID);
        $token = password_hash($login, PASSWORD_ARGON2ID);       
        $opt = random_int(100000, 999999);

        $sql = "INSERT INTO users (email, password, token, OPT)
                VALUES (:email, :password, :token, :OPT);
        
                INSERT INTO profile (user_id, nick, avatar, hideEmail)
                VALUES (LAST_INSERT_ID(), :email, DEFAULT, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $login, \PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, \PDO::PARAM_STR);
        $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
        $stmt->bindParam(':OPT', $opt, \PDO::PARAM_INT);        

        if(!$this->checkUser($login)){
            $_SESSION['email'] = $login;
            $stmt->execute();
            $this->sendMail($login, $opt);
            header("Location: /Very");            
        } else {
            $_SESSION['message'] = "Такой пользователь уже существует!";
            header("Location: /");
        }
    }

    // второй этап регистрации
    public function veryRegUser($login, $code)
    {
        $id = $this->getUser($login)['user_id'];
        $opt = $this->getUser($login)['OPT']; 
        if(intval($opt) === intval($code)){
            $optNew = 0;
            $very = 1;

            $sql = "UPDATE users 
                    SET OPT = :OPT, verification = :verification
                    WHERE users.user_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':OPT', $optNew, \PDO::PARAM_INT);
            $stmt->bindParam(':verification', $very, \PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            $stmt->execute();           
            $_SESSION['message'] = "Регистрация успешно заверншена!";
            header("Location: /");            
        } else {
            $_SESSION['message'] = "Неверно указан код из письма!";
            header("Location: /Very");
        }       
    }

    // Вход пользователя
    public function loginUser($login, $password)
    {
        $user = $this->getUser($login);
        $userPass = $user['password'];        
        if (empty($user) || empty($userPass) || !password_verify($password, $userPass)) {            
            $_SESSION['message'] = "Неверно указан логин или пароль!";            
            header("Location: /");      
        } elseif (password_verify($password, $userPass) && $this->veryUser($login)) {
            $_SESSION['user_id'] = $user['user_id'];           
            header("Location: /Chat");
        } elseif (password_verify($password, $userPass) && !$this->veryUser($login)) {
            $_SESSION['email'] = $login;
            $_SESSION['message'] = "Необходимо завершить регистрацию!";
            header("Location: /Very");
        } 
    }

    // Отправка почты на указанный емейл с кодом
    public function sendMail($login, $code)
    {
        $mail = new PHPMailer(true);

        try {
            // Настройка сервера отправки почты
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = 'ssl://smtp.yandex.ru'; // Укажите SMTP-сервер, который вы используете
            $mail->SMTPAuth = true;
            $mail->Username = 'RZL1985@yandex.ru'; // Укажите вашу почту
            $mail->Password = 'Ghbvthujdyf1985'; // Укажите ваш пароль
            $mail->Port = 465; // Укажите порт для SMTP-сервера

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
    
            // Установка получателя, отправителя и темы письма
            $mail->setFrom('RZL1985@yandex.ru', 'Vladimir'); // Укажите вашу почту и имя отправителя
            $mail->addAddress($login, 'Пользователь'); // Укажите адрес получателя
            $mail->Subject = 'Подтверждение Пароля'; // Укажите тему письма
    
            // Установка содержимого письма
            $mail->Body = "Код подтверждения: $code"; // Укажите текст письма
    
            // Отправка письма
            $mail->send();

            $_SESSION['message'] = "Письмо успешно отправлено на указынный адрес!";          

        } catch (Exception $e) {

            $_SESSION['message'] = "Произошла ошибка при отправке письма: {$mail->ErrorInfo}";           
        }
    }
}