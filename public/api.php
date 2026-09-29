<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use OpenAI;
use Ramsey\Uuid\Uuid;

include 'paint.php'

//1. Вход и регистрация

$neon_string = 'postgresql://neondb_owner:npg_i3rdHYxUJCe1@ep-hidden-grass-b5lwbny2-pooler.c-7.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$dsn = 'pgsql:host=ep-hidden-grass-b5lwbny2-pooler.c-7.us-east-2.aws.neon.tech;port=5432;dbname=neondb;sslmode=require';

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

function AI_Request() {
    $auth_key="MDE5Yzc2YmYtNGI0Yi03YWU1LTk5Y2QtZDAwMzVkZDkyZjIyOmI4ZWQwZmNmLTJlNDgtNGFiZS05ZWFlLTYyYzg1ZGU4NTdiYg==";
    $scope   = 'GIGACHAT_API_PERS';

    $ch = curl_init();
}

function GetToken() {
    $id = Uuid::uuid4()->toString();

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://sberbank.ru',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'RqUID: ' . $id,
            'Authorization: Basic ' . $this->authKey
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

function getClient(): \OpenAI\Client
{
    $token = $this->GetToken();

    return OpenAI::factory()->withApiKey($token)->withBaseUri('https://giga.chat')->make();
}

?>