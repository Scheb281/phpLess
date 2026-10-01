<?php
session_start();

include 'api.php';

if (isset($_POST['main_menu'])) {
    $_SESSION['username'] = null;
    DeleteCookie();
    header('Location: index.php');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paint website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container" style="max-width: 420px; margin-top: 80px;">



<?php
if (isset($_POST['log_in'])) {
    ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Вход</h3>
            <form action="api.php" method="post">
                <div class="mb-3">
                    <input type="text" name="login" class="form-control" placeholder="Логин">
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Пароль">
                </div>
               <button type="submit" name="login_request" class="btn btn-primary w-100">Войти</button>
            </form>
            <form action="index.php" method="post" class="mt-2">
                <button type="submit" name="back" class="btn btn-outline-secondary w-100">Назад</button>
            </form>
        </div>
    </div>
    <?php
}
else if (isset($_POST['sign_up'])) {
    ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Регистрация</h3>
            <form action="api.php" method="post">
                <div class="mb-3">
                    <input type="text" name="login" class="form-control" placeholder="Логин">
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Пароль">
                </div>
                <button type="submit" name="registration_request" class="btn btn-success w-100">Зарегистрироваться</button>
            </form>
            <form action="index.php" method="post" class="mt-2">
                <button type="submit" name="back" class="btn btn-outline-secondary w-100">Назад</button>
            </form>
        </div>
    </div>
    <?php
}
else {
    if(inter()) {
        return;
    }
    ?>
    <div class="text-center">
        <h1 class="mb-4">Paint website</h1>
        <form action="index.php" method="post" class="d-grid gap-2">
            <button type="submit" name="log_in" class="btn btn-primary">Войти</button>
            <button type="submit" name="sign_up" class="btn btn-outline-success">Зарегистрироваться</button>
        </form>
    </div>
    <?php
}
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

?>