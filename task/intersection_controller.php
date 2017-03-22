<?php

require_once 'VKApp.php';

if (isset($_GET['submit'])) {
    if (isset($_GET['url1']) && !empty($_GET['url1']) && isset($_GET['url2']) && !empty($_GET['url2'])) {

        $vk = new VKApp();
        $url1 = $_GET['url1'];
        $url2 = $_GET['url2'];

        if ($url1 === $url2) {
            exit ('Одинаковые ссылки!');
        }

        if ($vk->getPostByURL($url1) == null) {
            if ($vk->add($url1)) {
                echo 'Запись 1 добавлена!<br>';
            } else {
                exit('Не удалось получить данные из поля №1. Неверная ссылка !<br>');
            }
        }
        if ($vk->getPostByURL($url2) == null) {
            if ($vk->add($url2)) {
                echo 'Запись 2 добавлена!<br><br>';
            } else {
                exit('Не удалось получить данные из поля №2. Неверная ссылка !<br>');
            }
        }

        $joint = $vk->getJointLikes($url1, $url2);

        if (!empty($joint)) {
            echo 'Список пользователей, которые лайкнули оба поста:<br>';
            foreach ($joint as $row) {
                echo $row['sname'] . '<br>';
            }
            echo 'Всего пользователей: ' . count($joint) . '<br>';
        } else {
            echo 'Пересечений нет!<br>';
        }
    } else {
        echo 'Заполните поля!<br>';
    }
}

?>

<a href="view/intersection.php">Назад</a>
