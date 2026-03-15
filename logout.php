<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Eliminar cookies personalizadas: recordarme + últimas visitas + usuario actual
$cookies_a_borrar = ['usuario_recordado', 'ultima_visita', 'usuario_actual', 'ultimos_anuncios'];

foreach ($cookies_a_borrar as $c) {
    setcookie($c, '', time() - 3600, "/");
}

// Destruir la sesión en el servidor
session_destroy();

// Redirigir al inicio (index.php)
header("Location: index.php");
exit();
