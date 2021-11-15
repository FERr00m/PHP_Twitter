<?php
include_once 'functions.php';

if (!is_logged()) {
    redirect();
}
if (isset($_POST['text']) && !empty($_POST['text'])) {
    if (!add_post($_POST['text'], $_POST['image'])) {
        $_SESSION['error'] = 'Во время добавления поста что-то пошло не так';
    }
}

redirect(get_url('user_posts.php'));