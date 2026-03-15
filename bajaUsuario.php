<?php
session_start();
require_once("conexionBD.php");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: formularioAcceso.php");
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$contrasenya = $_POST['contrasenya'] ?? '';

/* ==========================================================
   Verificar contraseña
========================================================== */
$stmt = $conexion->prepare("SELECT Clave, Foto FROM usuarios WHERE IdUsuario = ?");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario || !password_verify($contrasenya, $usuario['Clave'])) {
    $_SESSION['flash_error'] = "Contraseña incorrecta. No se puede eliminar el usuario.";
    header("Location: confirmarBaja.php");
    exit();
}

/* ==========================================================
   BORRAR FOTO DE PERFIL DEL USUARIO
========================================================== */
$carpetaUsuarios = "imagenes_usuarios/";

if (!empty($usuario['Foto']) && $usuario['Foto'] !== "default.png") {
    $rutaPerfil = $carpetaUsuarios . $usuario['Foto'];
    if (file_exists($rutaPerfil)) {
        unlink($rutaPerfil);
    }
}

/* ==========================================================
   BORRAR FOTOS DE LOS ANUNCIOS 
========================================================== */

$carpetaAnuncios = "fotos_anuncios/";

// Primero las fotos extra de la tabla FOTOS
$sql_fotos = "SELECT f.Foto 
              FROM fotos f
              INNER JOIN anuncios a ON f.Anuncio = a.IdAnuncio
              WHERE a.Usuario = ?";

$stmt = $conexion->prepare($sql_fotos);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$res = $stmt->get_result();

while ($fila = $res->fetch_assoc()) {
    $ruta = $carpetaAnuncios . $fila['Foto'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}
$stmt->close();

// Segundo la foto principal de cada anuncio (FPrincipal)
$sql_fp = "SELECT FPrincipal FROM anuncios WHERE Usuario = ?";
$stmt = $conexion->prepare($sql_fp);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$res = $stmt->get_result();

while ($fila = $res->fetch_assoc()) {
    if (!empty($fila['FPrincipal'])) {
        $rutaP = $carpetaAnuncios . $fila['FPrincipal'];
        if (file_exists($rutaP)) {
            unlink($rutaP);
        }
    }
}
$stmt->close();

/* ==========================================================
   BORRAR EN BD
========================================================== */
// Borrar mensajes donde el usuario es origen o destino
$stmtMensajes = $conexion->prepare("
    DELETE FROM mensajes 
    WHERE UsuOrigen = ? OR UsuDestino = ?
");
$stmtMensajes->bind_param("ii", $usuarioId, $usuarioId);
$stmtMensajes->execute();
$stmtMensajes->close();

// Borrar solicitudes asociadas a los anuncios del usuario
$stmtSol = $conexion->prepare("
    DELETE s FROM solicitudes s
    INNER JOIN anuncios a ON s.Anuncio = a.IdAnuncio
    WHERE a.Usuario = ?
");
$stmtSol->bind_param("i", $usuarioId);
$stmtSol->execute();
$stmtSol->close();

// Fotos asociadas a anuncios
$stmtFotos = $conexion->prepare("DELETE f FROM fotos f 
                                 INNER JOIN anuncios a ON f.Anuncio = a.IdAnuncio 
                                 WHERE a.Usuario = ?");
$stmtFotos->bind_param("i", $usuarioId);
$stmtFotos->execute();
$stmtFotos->close();

// Anuncios
$stmtAnuncios = $conexion->prepare("DELETE FROM anuncios WHERE Usuario = ?");
$stmtAnuncios->bind_param("i", $usuarioId);
$stmtAnuncios->execute();
$stmtAnuncios->close();

// Usuario
$stmtUsuario = $conexion->prepare("DELETE FROM usuarios WHERE IdUsuario = ?");
$stmtUsuario->bind_param("i", $usuarioId);
$stmtUsuario->execute();
$stmtUsuario->close();

/* ==========================================================
   Cerrar sesión
========================================================== */

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
session_unset();


/* ==========================================================
   Redirigir
========================================================== */
header("Location: bajaCompletada.php");
exit();
?>
