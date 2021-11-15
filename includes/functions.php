<?php

include_once 'config.php';

function debug($data, $stop = 0)
{
    echo '<pre>' . print_r($data, true) . '</pre>';
    if ($stop) die();
}

function get_url($page = ''): string
{
    return HOST . "/$page";
}

function get_page_title($title = ''): string
{
    if (!empty($title)) {
        return SITE_NAME . " - $title";
    } else {
        return SITE_NAME;
    }
}

function redirect($link = HOST)
{
    header("Location: $link");
    die();
}

function db()
{
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS,
            [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $err) {
        die($err->getMessage());
    }
}

function db_query($sql, $exec = false)
{
    if (empty($sql)) return false;

    if ($exec) return db()->exec($sql);

    return db()->query($sql);
}

function get_posts($user_id = 0, $sort = false)
{
    $sorting = 'DESC';
    if ($sort) $sorting = 'ASC';
    if($user_id > 0) {
        return db_query("SELECT posts.*, users.name, users.login, users.avatar 
            FROM `posts` 
            JOIN `users` 
            ON users.id = posts.user_id
            WHERE user_id = $user_id
            ORDER BY `posts`.`date` $sorting;")->fetchAll();
    }
    return db_query("SELECT posts.*, users.name, users.login, users.avatar 
        FROM `posts` 
        JOIN `users` 
        ON users.id = posts.user_id
        ORDER BY `posts`.`date` $sorting;")->fetchAll();
}

function get_user_info($login)
{
    return db_query("SELECT * FROM `users` WHERE `login` = '$login';")->fetch();
}

function add_user($login, $pass)
{
    $login = trim($login);
    $name = ucfirst($login);
    $password = password_hash($pass, PASSWORD_DEFAULT);
    return db_query("INSERT INTO `users` (`id`, `login`, `pass`, `name`) VALUES (NULL, '$login', '$password', '$name');", true);
}

function register_user($auth_data)
{
    if (empty($auth_data) ||
        !isset($auth_data['login']) ||
        empty($auth_data['login']) ||
        !isset($auth_data['pass']) ||
        empty($auth_data['pass']) ||
        !isset($auth_data['pass_again']) ||
        empty($auth_data['pass_again'])) return false;

    $user = get_user_info($auth_data['login']);
    if (!empty($user)) {
        $_SESSION['error'] = "Пользователь {$auth_data['login']} уже существует";
        redirect(get_url('register.php'));
        die();
    }

    if ($auth_data['pass'] !== $auth_data['pass_again']) {
        $_SESSION['error'] = "Пароли не совпадают";
        redirect(get_url('register.php'));
        die();
    }

    if (add_user($auth_data['login'], $auth_data['pass'])) {
        redirect(get_url());
        die();
    }
}

function login_user($auth_data)
{

    if (empty($auth_data) ||
        !isset($auth_data['login']) ||
        empty($auth_data['login']) ||
        !isset($auth_data['pass']) ||
        empty($auth_data['pass'])) return false;

    $user = get_user_info($auth_data['login']);
    if (empty($user)) {
        $_SESSION['error'] = "Пользователь не найден";
        redirect(get_url());
        die();
    }

    if (password_verify($auth_data['pass'], $user['pass'])) {
        $_SESSION['user'] = $user;
        $_SESSION['error'] = '';
        redirect(get_url('user_posts.php?id=' . $user['id']));
    } else {
        $_SESSION['error'] = "Пароль неверный";
        redirect(get_url());
    }
    die();


}

function get_error_message()
{
    $error = '';
    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        $error = $_SESSION['error'];
        $_SESSION['error'] = '';
    }
    return $error;
}

function is_logged()
{
    return (isset($_SESSION['user']) && !empty($_SESSION['user']));
}

function add_post($text, $img)
{
    $text = trim($text);
    if (mb_strlen($text) > 255) {
        $text = mb_substr($text, 0 , 250) . ' ...';
    }

    $user_id = $_SESSION['user']['id'];
    $sql = "INSERT INTO `posts` (`id`, `user_id`, `text`, `image`) VALUES (NULL, $user_id, '$text', '$img');";
    return db_query($sql, true);
}

function delete_post($id)
{
    if (!is_numeric($id)) {
        $_SESSION['error'] = 'В id не число!';
        redirect(get_url('user_posts.php'));
    }
    $user_id = $_SESSION['user']['id'];
    $sql = "DELETE FROM `posts` WHERE `id` = $id AND `user_id` = $user_id;";
    return db_query($sql, true);
}

function get_likes_count($post_id)
{
    if (empty($post_id)) return 0;

    $sql = "SELECT COUNT(*) FROM `likes` WHERE `post_id` = $post_id";
    return db_query($sql)->fetchColumn();
}

function if_post_liked($post_id)
{
    $user_id = $_SESSION['user']['id'];
    if (empty($post_id)) return false;

    $sql = "SELECT * FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id;";
    return db_query($sql)->rowCount() > 0;
}

function add_like($post_id)
{
    $user_id = $_SESSION['user']['id'];
    if (empty($post_id)) return false;

    $sql = "INSERT INTO `likes` (`post_id`, `user_id`) VALUES ($post_id, $user_id);";
    return db_query($sql, true);
}

function delete_like($post_id)
{
    if (!is_numeric($post_id)) {
        $_SESSION['error'] = 'В id не число!';
        redirect(get_url('user_posts.php'));
    }
    $user_id = $_SESSION['user']['id'];
    $sql = "DELETE FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id;";
    return db_query($sql, true);
}