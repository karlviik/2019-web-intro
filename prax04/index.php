<?php

include ('includes/auth.php');
include ('includes/db.php');
include ('includes/utils.php');

if (user_logged_in()) {
	$page = 'home.php';
} else {
	$page = 'login.php';
}

header('Location: ' . $page);
