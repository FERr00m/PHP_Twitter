<?php
include_once 'functions.php';

if (!is_logged()) {
    redirect();
}
if (isset($_GET['id']) && !empty($_GET['id'])) {
    if (!delete_post($_GET['id'])) {
        $_SESSION['error'] = 'Во время удаления поста что-то пошло не так';
    }
}

redirect(get_url('user_posts.php'));