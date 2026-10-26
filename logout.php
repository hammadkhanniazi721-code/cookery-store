<?php
session_start();

// Saare session variables hata do
$_SESSION = array();

// Session cookie bhi delete karo
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Session destroy karo
session_destroy();

// Login page pe bhej do
header("Location: login.php");
exit;
?>