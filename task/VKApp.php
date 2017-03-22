<?php

class VKApp
{

    // PDO object
    private $db = null;

    function __construct()
    {
        // Подключение к базе данных, используя PDO
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=task', 'task', 'root', array(
                PDO::ATTR_PERSISTENT => true
            ));
        } catch (PDOException $e) {
            print 'Error!: ' . $e->getMessage() . '<br/>';
            die();
        }
    }

    // ADD METHODS

    /**
     * Добавить пост
     *
     * @param string $url
     * @param int $status
     */
    public function addPost($url, $status = 0)
    {
        $this->db->exec("INSERT INTO post SET url = '$url', status = '$status'");
    }

    /**
     * Добавить пользователя
     *
     * @param string $sname
     */
    public function addUser($sname)
    {
        $this->db->exec("INSERT INTO user SET sname = '$sname'");
    }

    /**
     * Добавить связь
     *
     * @param int $user_id
     * @param int $post_id
     * @param int $is_like
     * @param int $is_repost
     */
    public function addUserPost($user_id, $post_id, $is_like, $is_repost)
    {
        $this->db->exec("INSERT INTO user_post SET user_id = '$user_id', post_id = '$post_id', is_like = '$is_like', is_repost = '$is_repost'");
    }

    // DELETE METHODS

    /**
     * Удаляет связь
     *
     * @param int $user_id
     * @param int $post_id
     */
    public function deleteUserPost($user_id, $post_id)
    {
        $this->db->exec("DELETE FROM user_post WHERE user_id = '$user_id' AND post_id = '$post_id'");
    }

    // GET METHODS

    /**
     * Возвращает id (строку) поста в формате (owner_id)_(item_id), либо пустую строку, если он не найден
     *
     * @param string $url
     * @return string
     */
    public function getPostId($url)
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

    /**
     * Возвращает id владельца поста
     *
     * @param string $post
     * @return string
     */
    public function getOwnerId($post)
    {
        $pattern = '#-?\d+#';
        preg_match($pattern, $post, $owner_id);
        return $owner_id[0];
    }

    /**
     * Возвращает id поста
     *
     * @param string $post
     * @return string
     */
    public function getItemId($post)
    {
        $pattern1 = '#_\d+#';
        $pattern2 = '#\d+#';
        preg_match($pattern1, $post, $item);
        preg_match($pattern2, $item[0], $item_id);
        return $item_id[0];
    }

    /**
     * Возвращает последний добавленный id
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * Возвращает информацию о посте по url
     *
     * @param string $url
     * @return array
     */
    public function getPostByURL($url)
    {
        foreach ($this->db->query("SELECT * FROM post WHERE url = '$url'") as $row) {
            return $row;
        }
    }

    /**
     * Возвращает всех пользователей
     *
     * @return PDOStatement
     */
    public function getAllUsers()
    {
        return $this->db->query("SELECT * FROM user");
    }

    /**
     * Возвращает всех пользователей, которые лайкнули пост
     *
     * @param int $post_id
     * @return array
     */
    public function getUsersWhoLiked($post_id)
    {
        $users = array();
        foreach ($this->db->query("SELECT user_id FROM user_post WHERE post_id = '$post_id'") as $user_id) {
            foreach ($this->db->query('SELECT * FROM user WHERE id = ' . $user_id['user_id']) as $user)
                $users[] = $user;
        }
        return $users;
    }

    /**
     * Возвращает id пользователя
     *
     * @param string $sname
     * @return int
     */
    public function getUserId($sname)
    {
        foreach ($this->db->query("SELECT id FROM user WHERE sname = '$sname'") as $row) {
            return $row['id'];
        }
    }

    /**
     * Возвращает информацию о связи
     *
     * @param string $user_id
     * @param $post_id
     * @return array
     */
    public function getUserPostRelation($user_id, $post_id)
    {
        foreach ($this->db->query("SELECT * FROM user_post WHERE user_id = '$user_id' AND post_id = '$post_id'") as $row) {
            return $row;
        }
    }

    /**
     * Возвращает массив пользователей, которым понравились обе записи
     *
     * @param string $url1
     * @param string $url2
     * @return array
     */
    public function getJointLikes($url1, $url2)
    {
        $post_id1 = null;
        foreach ($this->db->query("SELECT id FROM post WHERE url = '$url1'") as $row) {
            $post_id1 = $row;
        }

        $post_id2 = null;
        foreach ($this->db->query("SELECT id FROM post WHERE url = '$url2'") as $row) {
            $post_id2 = $row;
        }

        $users1 = $this->getUsersWhoLiked($post_id1['id']);
        $users2 = $this->getUsersWhoLiked($post_id2['id']);
        $joint = array();

        foreach ($users1 as $us1) {
            foreach ($users2 as $us2) {
                if ($us1 == $us2) {
                    $joint[] = $us1;
                }
            }
        }

        return $joint;
    }


    /**
     * Добавление поста, пользователей и связи в базу
     *
     * @param string $url
     * @return int
     */
    public function add($url)
    {
        $post = $this->getPostId($url);

        if (!empty($post)) {

            $owner_id = $this->getOwnerId($post);
            $item_id = $this->getItemId($post);

            $request_params = [
                'type' => 'post',
                'owner_id' => $owner_id,
                'item_id' => $item_id,
                'filter' => 'likes',
                'friends_only' => 0,
                'extended' => 0,
                'offset' => 0,
                'count' => 1000,
                'skip_own' => 0
            ];

            $get_params = http_build_query($request_params);
            $result = json_decode(file_get_contents('https://api.vk.com/method/likes.getList?' . $get_params));

            if (isset($result->response)) {

                try {
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Начало транзакции
                    $this->db->beginTransaction();

                    // TODO: Если лайов больше 1000
                    // Проверка есть ли уже такой пост в таблице
                    $row = $this->getPostByURL($url);

                    // Если такого поста нет, то добавляем
                    if (!isset($row)) {
                        $this->addPost($url);
                        $post_id = $this->getLastInsertId();

                        $users = $result->response->users;
                        foreach ($users as $sname) {
                            $user_id = $this->getUserId($sname);
                            if (!isset($user_id)) {
                                $this->addUser($sname);
                                $user_id = $this->getLastInsertId();
                            }
                            $this->addUserPost($user_id, $post_id, 1, 0);
                        }
                    } else {    // Если такой пост есть, то обновляет информацию
                        $users = $result->response->users;

                        foreach ($this->getUsersWhoLiked($row['id']) as $user) {
                            $is_matches = false;
                            foreach ($users as $sname) {
                                if ($user['sname'] == $sname) {
                                    $is_matches = true;
                                    break;
                                }
                            }
                            if (!$is_matches) {
                                $this->deleteUserPost($user['id'], $row['id']);
                            }
                        }

                        foreach ($users as $sname) {
                            $user_id = $this->getUserId($sname);
                            if (!isset($user_id)) {
                                $this->addUser($sname);
                                $user_id = $this->getLastInsertId();
                                $this->addUserPost($user_id, $row['id'], 1, 0);
                            } elseif ($this->getUserPostRelation($user_id, $row['id']) == null) {
                                $this->addUserPost($user_id, $row['id'], 1, 0);
                            }
                        }
                    }

                    $this->db->commit();

                    return 1;

                } catch (PDOException $e) {
                    $this->db->rollBack();
                    print 'Error!: ' . $e->getMessage() . '<br/>';
                    die();
                }

            }
        }

        return 0;
    }

    /**
     * Очищает все таблицы в базе данных
     */
    public function clear()
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Начало транзакции
            $this->db->beginTransaction();

            $sql1 = 'SET FOREIGN_KEY_CHECKS = 0;' .
                'TRUNCATE TABLE user_post;' .
                'TRUNCATE TABLE user;' .
                'TRUNCATE TABLE post;' .
                'SET FOREIGN_KEY_CHECKS = 1;';

            $this->db->exec($sql1);

            $this->db->commit();

        } catch (PDOException $e) {
            $this->db->rollBack();
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Отобразить посты
     */
    public function show()
    {
        try {
            //    $sql1 = 'SELECT * FROM post';
//
//    $stmt = $db->prepare($sql1);
//    $stmt->execute();
//
//    $stmt->bindColumn('id', $post_id);
//    $stmt->bindColumn('ref', $ref);
//    $stmt->bindColumn('status', $status);
//
//    while ($stmt->fetch(PDO::FETCH_BOUND)) {
//        echo 'Post: ' . $ref . '<br>Status: ' . $status . '<br>';
//
//        $sth = $db->prepare('SELECT sum(is_like) as likes, sum(is_repost) as reposts FROM user_post WHERE post_id = ?');
//        $sth->bindParam(1, $post_id, PDO::PARAM_INT);
//        $sth->bindColumn('likes', $likes);
//        $sth->bindColumn('reposts', $reposts);
//        $sth->execute();
//        while ($sth->fetch(PDO::FETCH_BOUND)) {
//            echo 'Likes: ' . $likes . ' Reposts: ' . $reposts . '<br><br>';
//        }
//    }


            // Вывод постов
            foreach ($this->db->query('SELECT * FROM post') as $row) {
                $post_id = $row['id'];
                $url = $row['url'];
                $status = $row['status'];

                echo 'Post: ' . $url . '<br>Status: ' . $status . '<br>';

                // Подсчет лайков\репостов к посту и вывод их
                foreach ($this->db->query("SELECT sum(is_like) as likes, sum(is_repost) as reposts FROM user_post WHERE post_id = '$post_id'") as $lp) {
                    $likes = $lp['likes'];
                    $reposts = $lp['reposts'];
                    echo 'Likes: ' . $likes . ' Reposts: ' . $reposts . '<br><br>';
                }

            }

        } catch (PDOException $e) {
            print 'Error!: ' . $e->getMessage() . '<br/>';
            die();
        }
    }

}
