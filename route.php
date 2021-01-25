<?php

$request = explode("/", $_SERVER['REQUEST_URI']);

$id = empty($request[2]) == "" ? $request[2] : "";
switch ($request[1]) {
    case 'user':
        require __DIR__ . '/user.php';
        break;
    case 'login':
        require __DIR__ . '/login.php';
        break;
    case 'post':
        require __DIR__ . '/post.php';
        break;
    default:
        http_response_code(404);
        // require __DIR__ . '/views/404.php';
        break;
}
