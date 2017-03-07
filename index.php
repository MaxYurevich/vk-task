<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>vk-task</title>
</head>
<body>

<form action="" method="get">
    <label>Вставьте полную ссылку на пост:</label><br/>
    <input type="text" name="url"/>
    <input type="submit" name="submit"/>
</form>

</body>
</html>


<?php

// Функция возвращает id (строку) поста, либо пустую строку, если он не найден
function getPostId($url)
{
    $pattern = '#wall[\d,_,-]+#';
    if (preg_match($pattern, $url, $matches)) {
        $post_pattern = '#[\d,_,-]+#';
        preg_match($post_pattern, $matches[0], $posts);
        return $posts[0];
    } else {
        return '';
    }
}


if (isset($_GET['submit'])) {
    $url = $_GET['url'];

    $posts = getPostId($url);

    if (!empty($posts)) {
        $request_params = [
            'posts' => $posts,
        ];

        $get_params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/wall.getById?' . $get_params));
        if (isset($result->response[0])) {
            $likes = $result->response[0]->likes->count;
            $reposts = $result->response[0]->reposts->count;
            echo 'URL post: ' . $url . '<br>' .
                 'Likes: ' . $likes . '<br>' .
                 'Reposts: ' . $reposts;
        } else {
            echo 'Не удалось получить данные. Скорее всего профиль закрыт!';
        }
    } else {
        echo 'Неверная ссылка!';
    }
}