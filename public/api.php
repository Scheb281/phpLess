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
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Вход</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light">
            <div class="container" style="max-width: 520px; margin-top: 60px;">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">С возвращением, <?= htmlspecialchars($user['Login']) ?>!</h2>
                        <form action="paint.php" method="post" class="d-grid gap-2 mb-2">
                            <button type="submit" name="paint" class="btn btn-primary">Начать рисовать</button>
                        </form>
                        <form action="index.php" method="post" class="d-grid">
                            <button type="submit" name="main_menu" class="btn btn-outline-secondary">Сменить аккаунт</button>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php

            $_SESSION['username'] = $_POST['login'];
        }
        else {
            ?>
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Вход</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light">
            <div class="container" style="max-width: 520px; margin-top: 60px;">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4 text-danger">Аккаунт не найден</h2>
                        <form action="index.php" method="post" class="d-grid gap-2">
                            <button type="submit" name="log_in" class="btn btn-primary">Войти</button>
                            <button type="submit" name="main_menu" class="btn btn-outline-secondary">Вернуться в главное меню</button>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
        }
    }
    else {
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Вход</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
        <div class="container" style="max-width: 520px; margin-top: 60px;">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="card-title text-danger mb-2">Ошибка входа</h2>
                    <p class="text-muted">Были переданы пустые поля</p>
                    <form action="index.php" method="post" class="d-grid gap-2">
                        <button type="submit" name="log_in" class="btn btn-primary">Войти</button>
                        <button type="submit" name="main_menu" class="btn btn-outline-secondary">Вернуться в главное меню</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
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
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Регистрация</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
        <div class="container" style="max-width: 520px; margin-top: 60px;">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="card-title mb-4">Добро пожаловать, <?= htmlspecialchars($_POST['login']) ?>!</h2>
                    <form action="paint.php" method="post" class="d-grid gap-2 mb-2">
                        <button type="submit" name="paint" class="btn btn-primary">Начать рисовать</button>
                    </form>
                    <form action="index.php" method="post" class="d-grid">
                        <button type="submit" name="main_menu" class="btn btn-outline-secondary">Сменить аккаунт</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    else {
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Регистрация</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
        <div class="container" style="max-width: 520px; margin-top: 60px;">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-danger mb-2">Ошибка регистрации</h2>
                    <p class="mb-2">Возможные ошибки:</p>
                    <ul class="mb-3">
                        <li>Были переданы пустые поля</li>
                        <li>Логин занят</li>
                    </ul>
                    <form action="index.php" method="post" class="d-grid gap-2">
                        <button type="submit" name="sign_up" class="btn btn-success">Зарегистрироваться</button>
                        <button type="submit" name="main_menu" class="btn btn-outline-secondary">Вернуться в главное меню</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
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
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>С возвращением</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light">
            <div class="container" style="max-width: 520px; margin-top: 60px;">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">С возвращением, <?= htmlspecialchars($user['Login']) ?>!</h2>
                        <form action="paint.php" method="post" class="d-grid gap-2 mb-2">
                            <button type="submit" name="paint" class="btn btn-primary">Начать рисовать</button>
                        </form>
                        <form action="index.php" method="post" class="d-grid">
                            <button type="submit" name="main_menu" class="btn btn-outline-secondary">Сменить аккаунт</button>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
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