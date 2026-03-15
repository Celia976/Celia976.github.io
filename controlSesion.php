<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Comprobar login
    if (empty($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
        header("Location: index.php"); 
        exit();
    }

?>