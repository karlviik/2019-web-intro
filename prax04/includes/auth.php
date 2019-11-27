<?php
session_start();
function user_logged_in() {
    return isset($_SESSION['user_id']);
}

function logout() {
	session_destroy();
}
