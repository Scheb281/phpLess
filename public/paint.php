<?php
session_start();

if(isset($_POST['paint'])) {
    ?>
    <form action='index.php' method='post'>
        <input type='submit' name='main_menu' value='Выйти'>
    <form>
    <?php

    //рисовалка

    ?>
    <form action='paint.php' method='get'>
        <h1>ИИ</h1>
        <h3 id='answer'></h3>
        <textarea placeholder='Задайте вопрос ИИ'>
        <button onclick="AI()">Задать вопрос</button>
    <form>

    <script>
        function AI() {
            document.getElementById('answer').innerText = "";
        }
    </script>
    <?php
}




?>