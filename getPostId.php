<?php

/**
 * Возвращает id (строку) поста, либо пустую строку, если он не найден
 *
 * @param string $url
 * @return string
 */
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
