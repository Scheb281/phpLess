<?php
session_start();

include 'api.php';

if(isset($_POST['log_in'])) {
    ?>
    <form action='api.php' method='post'>
        <input type='text' name='login' placeholder='Логин'>
        <input type='password' name='password' placeholder='Пароль'>
        <input type='submit' name='login_request' value='Войти'>
    </form>
    <?php
}
else if(isset($_POST['sign_up'])) {
    ?>
    <form action='api.php' method='post'>
        <input type='text' name='login' placeholder='Логин'>
        <input type='password' name='password' placeholder='Пароль'>
        <input type='submit' name='registration_request' value='Зарегистрироваться'>
    </form>
    <?php
}
else {
    if(isset($_POST['main_menu'])) {
        $_SESSION['username'] = null;
        DeleteCookie();
    }
    else if(inter()) {
        return;
    };

    ?>
    <h1>Paint website</h1>
    <form action='index.php' method='post'>
        <input type='submit' name='log_in' value='Войти'>
        <input type='submit' name='sign_up' value='Зарегистрироваться'>
    </form>
    <?php
}


?>