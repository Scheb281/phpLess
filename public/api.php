<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use Ramsey\Uuid\Uuid;

include_once 'paint.php';

//1. Вход и регистрация

$neon_string = 'postgresql://neondb_owner:npg_i3rdHYxUJCe1@ep-hidden-grass-b5lwbny2-pooler.c-7.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$dsn = 'pgsql:host=ep-hidden-grass-b5lwbny2-pooler.c-7.us-east-2.aws.neon.tech;port=5432;dbname=neondb;sslmode=require';

$gc = new GigaChat();

try{
    $pdo = new PDO($dsn, "neondb_owner", "npg_i3rdHYxUJCe1", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
catch (\PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if(isset($_POST['login_request'])) {
    if($_POST['login'] != null && $_POST['password'] != null) {
        $hash = md5($_POST['password']);

        $stmt = $pdo->prepare('SELECT * FROM "Users" WHERE "Login" = :login AND "Password" = :password');
        $stmt->execute([
            'login'    => $_POST['login'],
            'password' => $hash
        ]);

        $user = $stmt->fetch();

        if($user) {
            CreateCookie($_POST['login'], $hash);

            ?>
            <form action='paint.php' method='post'>
                <h2>С возвращением, <?echo $user['Login']?>!</h2>
                <input type='submit' name='paint' value='Начать рисовать'>
            <form>

            <form action='index.php' method='post'>
                <input type='submit' name='main_menu' value='Сменить аккаунт'>
            <form>
            <?php

            $_SESSION['username'] = $_POST['login'];
        }
        else {
            ?>
            <form action='index.php' method='post'>
                <h2>Аккаунт не найден</h2>
                <input type='submit' name='log_in' value='Войти'>
                <input type='submit' name='main_menu' value='Вернуться в главное меню'>
            <form>
            <?php
        }
    }
    else {
        ?>
        <form action='index.php' method='post'>
            <h2>Ошибка входа</h2>
            <h3>Были переданы пустые поля</h3>
            <input type='submit' name='log_in' value='Войти'>
            <input type='submit' name='main_menu' value='Вернуться в главное меню'>
        <form>
        <?php
    }
}
else if(isset($_POST['registration_request'])) {
    if($_POST['login'] != null && $_POST['password'] != null && IsLoginAvailable($_POST['login'], $pdo)) {
        $hash = md5($_POST['password']);

        $new_user = $pdo->prepare('INSERT INTO "Users" ("Login", "Password") VALUES (:Login, :Password)');
        $new_user->execute([
            'Login' => $_POST['login'],
            'Password' => $hash
        ]);

        $_SESSION['user'] = $_POST['login'];

        CreateCookie($_POST['login'], $hash);
        ?>
            <form action='paint.php' method='post'>
                <h2>Добро пожаловать, <?echo $_POST['login']?>!</h2>
                <input type='submit' name='paint' value='Начать рисовать'>
            <form>

            <form action='index.php' method='post'>
                <input type='submit' name='main_menu' value='Сменить аккаунт'>
            <form>
        <?php
    }
    else {
        ?>
        <form action='index.php' method='post'>
            <h2>Ошибка регистрации</h2>
            <h3>Возможные ошибки</h3>
            <ul>
                <li>Были переданы пустые поля</li>
                <li>Логин занят</li>
            </ul>
            <input type='submit' name='sign_up' value='Зарегистрироваться'>
            <input type='submit' name='main_menu' value='Вернуться в главное меню'>
        <form>
        <?php
    }
}

function IsLoginAvailable($login, $pdo) {
    $stmt = $pdo->prepare('SELECT "Login" FROM "Users" WHERE "Login" = :login');
    $stmt->execute([
        'login' => $login,
    ]);

    $users = $stmt->fetchAll();

    if($users) {
        return false;
    }

    return true;
}

function CreateCookie($login, $hash) {
    $time = time() + 2592000; //30 дней

    setcookie('user', $login . ':' . $hash, $time, "/");
}

function DeleteCookie() {
    setcookie('user', "", time() - 2592000, "/");
}

function inter() {
    if(isset($_COOKIE['user'])) {
        $data = explode(":", $_COOKIE['user']);

        $dsn = 'pgsql:host=ep-hidden-grass-b5lwbny2-pooler.c-7.us-east-2.aws.neon.tech;port=5432;dbname=neondb;sslmode=require';
        $pdo = new PDO($dsn, "neondb_owner", "npg_i3rdHYxUJCe1", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare('SELECT * FROM "Users" WHERE "Login" = :login AND "Password" = :password');
        $stmt->execute([
            'login'    => $data[0],
            'password' => $data[1]
        ]);

        $user = $stmt->fetch();

        if($user) {
            CreateCookie($data[0], $data[1]);

            ?>
            <form action='paint.php' method='post'>
                <h2>С возвращением, <?echo $user['Login']?>!</h2>
                <input type='submit' name='paint' value='Начать рисовать'>
            <form>

            <form action='index.php' method='post'>
                <input type='submit' name='main_menu' value='Сменить аккаунт'>
            <form>
            <?php

            $_SESSION['username'] = $data[0];
        }

        return true;
    }
    else {
        return false;
    }
}

//2. ИИ

if (isset($_POST['action']) && $_POST['action'] === 'gigachat_query') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    try {
        $prompt = $_POST['prompt'] ?? '';
        
        $gc = new GigaChat(); 
        $response = $gc->AI_Request($prompt);

        echo $response;
    } catch (\Throwable $e) {
        echo "Поймали ошибку: " . $e->getMessage() . " в файле " . $e->getFile() . " на строке " . $e->getLine();
    }
    exit;
}

class GigaChat {
    private string $auth_key = "MDE5Yzc2YmYtNGI0Yi03YWU1LTk5Y2QtZDAwMzVkZDkyZjIyOmI4ZWQwZmNmLTJlNDgtNGFiZS05ZWFlLTYyYzg1ZGU4NTdiYg==";
    private string $scope   = 'GIGACHAT_API_PERS';

    public function AI_Request($text) {
        try {
            $postData = [
                'model' => 'GigaChat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Ты умный помощник. Человек задает тебе вопрос по рисованию, в ответ ты даешь идеии, что можно нарисовать или рассказываешь, как нарисовать объект из запроса. Если человек задаст вопрос о сайте, то в ответе опиши сайт, что тут есть регистрациия и вход, ии помощник, холст для рисования и история рисунков. Если человек задаст вопрос, который не относится ни к рисованию, ни к сайту, то в ответе скажи, что лучше задайте вопрос о сайте или рисовании.'],
                    ['role' => 'user', 'content' => $text]
                ],
                'temperature' => 0.7,
            ];

            $token = $this->GetToken();
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://gigachat.devices.sberbank.ru/api/v1/chat/completions',
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false, 
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $token
                ],
                CURLOPT_POSTFIELDS => json_encode($postData, JSON_UNESCAPED_UNICODE)
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $arr = json_decode($response, true);
            if (isset($arr['choices'][0]['message']['content'])) {
                return $arr['choices'][0]['message']['content'];
            }
            
            return "Не удалось прочитать ответ ИИ: " . $response;
        } 
        catch (\Exception $e) {
            return "Ошибка выполнения запроса: " . $e->getMessage();
        }
    }

    private function GetToken() {
        $id = Uuid::uuid4()->toString();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                'RqUID: ' . $id,
                'Authorization: Basic ' . $this->auth_key
            ],
            CURLOPT_POSTFIELDS => 'scope=' . $this->scope
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        if (!isset($data['access_token'])) {
            throw new Exception("Ошибка OAuth GigaChat: " . $response);
        }

        return $data['access_token'];
    } 
}

?>