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

function get_posts($user_id = 0)
{
    if($user_id > 0) {
        return db_query("SELECT posts.*, users.name, users.login, users.avatar 
            FROM `posts` 
            JOIN `users` 
            ON users.id = posts.user_id
            WHERE user_id = $user_id;")->fetchAll();
    }
    return db_query("SELECT posts.*, users.name, users.login, users.avatar 
        FROM `posts` 
        JOIN `users` 
        ON users.id = posts.user_id;")->fetchAll();
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
        header('Location: ' . get_url('register.php'));
        die();
    }

    if ($auth_data['pass'] !== $auth_data['pass_again']) {
        $_SESSION['error'] = "Пароли не совпадают";
        header('Location: ' . get_url('register.php'));
        die();
    }

    if (add_user($auth_data['login'], $auth_data['pass'])) {
        header('Location: ' . get_url());
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
        header('Location: ' . get_url());
        die();
    }

    if (password_verify($auth_data['pass'], $user['pass'])) {
        $_SESSION['user'] = $user;
        $_SESSION['error'] = '';
        header('Location: ' . get_url('user_posts.php?id=' . $user['id']));
    } else {
        $_SESSION['error'] = "Пароль неверный";
        header('Location: ' . get_url());
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