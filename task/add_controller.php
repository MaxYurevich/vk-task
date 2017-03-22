<?php

require_once 'VKApp.php';

if (isset($_GET['submit'])) {
    if (isset($_GET['url']) && !empty($_GET['url'])) {

        $vk = new VKApp();
        $url = $_GET['url'];

        if ($vk->add($url)) {
            echo 'Запись добавлена!<br>';
        } else {
            echo 'Не удалось получить данные. Неверная ссылка!<br>';
        }
    } else {
        echo 'Вставьте ссылку!<br>';
    }
}

?>

<a href="index.php">Назад</a>
