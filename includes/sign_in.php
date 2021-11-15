<?php
include_once 'functions.php';

if (isset($_POST['login']))
{
    login_user($_POST);
}
