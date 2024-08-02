<?php

session_start();

$csrf_token_logout_php = htmlspecialchars($_SESSION['csrf_token_logout_php'], ENT_QUOTES, 'UTF-8');
$csrf_token_logout_js = htmlspecialchars($_POST['csrf_token_logout_js'], ENT_QUOTES, 'UTF-8');


if (isset($_SESSION['username']) && !empty($csrf_token_logout_php) && !empty($csrf_token_logout_js) && $csrf_token_logout_php === $csrf_token_logout_js) {    
    session_unset();
	session_destroy();
	header("Location: index.php");
	exit();
} else {
	header("Location: dashboard.php");
    //echo"csrf_token_logout_php: " . $csrf_token_logout_php . " csrf_token_logout_js: " . $csrf_token_logout_js;
	exit();
}

?>