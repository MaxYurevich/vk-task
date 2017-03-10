<?php

include 'getPostId.php';


if (isset($_GET['submit'])) {
    if (isset($_GET['url']) && !empty($_GET['url'])) {
        $url = $_GET['url'];

        $post_id = getPostId($url);

        if (!empty($post_id)) {
            $request_params = [
                'posts' => $post_id,
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
    } else {
        echo 'Вставьте ссылку!';
    }
}

?>


<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>vk-task</title>
</head>
<body>

<form action="" method="get">
    <label for="url">Вставьте полную ссылку на пост:</label><br/>
    <input type="text" name="url" id="url"/>
    <input type="submit" name="submit"/>
</form>

</body>
</html>


