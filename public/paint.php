<?php
session_start();

include_once 'api.php';

$gc = new GigaChat();

if(isset($_POST['paint'])) {
    ?>
    <form action='index.php' method='post'>
        <input type='submit' name='main_menu' value='Выйти'>
    </form>


    <form action='paint.php' method='post'>
        <h1>ИИ</h1>
        <h3 id='answer'></h3>
        <textarea id='ai' placeholder='Задайте вопрос ИИ'></textarea>
        <button type='button' onclick="AI()">Задать вопрос</button>
    </form>

    <script>
        function AI() {
            const question = document.getElementById('ai').value;
            const answer = document.getElementById('answer');
            
            if (!question.trim()) {
                answer.innerText = "Введите вопрос!";
                return;
            }

            answer.innerText = "Секунду, GigaChat думает...";

            fetch('api.php', {
                method: 'post',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=gigachat_query&prompt=' + encodeURIComponent(question)
            })
            .then(response => response.text())
            .then(text => {
                if (!text.trim()) {
                    answer.innerText = "Ошибка: Сервер вернул пустой ответ.";
                } 
                else {
                    answer.innerText = text; 
                }
            })
            .catch(error => {
                answer.innerText = "Ошибка при запросе к ИИ.";
                console.error(error);
            });
        }
    </script>
    <?php
}




?>