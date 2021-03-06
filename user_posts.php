<?php
include_once 'includes/functions.php';

$error = get_error_message();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_SESSION['user']['id'])) {
    $id = $_SESSION['user']['id'];
} else {
    $id = 0;
}

$posts = get_posts($id);

$title = 'Твиты пользователя ' . '@' . $_SESSION['user']['login'];
if (!empty($posts)) {
    $title = 'Твиты пользователя @' . $posts[0]['login'];
}

include_once 'includes/header.php';
if (is_logged()) {
    include_once 'includes/tweet_form.php';
}
include_once 'includes/posts.php';
include_once 'includes/footer.php';

