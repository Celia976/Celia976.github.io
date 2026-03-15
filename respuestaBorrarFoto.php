<?php
session_start();
require_once("conexionBD.php");
require_once("controlSesion.php");

// ==========================================================
// Comprobar login
// ==========================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: formularioAcceso.php");
    exit();
}

$idFoto = isset($_GET['idFoto']) ? (int)$_GET['idFoto'] : 0;

// Verificar que la foto pertenece al usuario
$stmtVerificar = $conexion->prepare(
    "SELECT IdFoto, Foto, Anuncio FROM Fotos 
     WHERE IdFoto = ? 
     AND Anuncio IN (SELECT IdAnuncio FROM Anuncios WHERE Usuario = ?)"
);
$stmtVerificar->bind_param("ii", $idFoto, $_SESSION['usuario_id']);
$stmtVerificar->execute();
$resultVerificar = $stmtVerificar->get_result();
$foto = $resultVerificar->fetch_assoc();
$stmtVerificar->close();

$mensaje = '';
if (!$foto) {
    $mensaje = "La foto no existe o no tienes permisos para eliminarla.";
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar contraseña
    $contrasenya = $_POST['contrasenya'] ?? '';
    $stmtUser = $conexion->prepare("SELECT Clave FROM Usuarios WHERE IdUsuario = ?");
    $stmtUser->bind_param("i", $_SESSION['usuario_id']);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $usuario = $resUser->fetch_assoc();
    $stmtUser->close();

    if (!$usuario || !password_verify($contrasenya, $usuario['Clave'])) {
        $mensaje = "Contraseña incorrecta. No se pudo eliminar la foto.";
    } else {
        // Borrar la foto
        $stmtDel = $conexion->prepare("DELETE FROM Fotos WHERE IdFoto = ?");
        $stmtDel->bind_param("i", $idFoto);
        $stmtDel->execute();
        $stmtDel->close();

        $mensaje = "Foto eliminada correctamente.";
    }
}

$encabezado = "Resultado eliminación de foto";
$style = "perfil.css";
require_once("cabecera.php");
?>

<main>
<section class="resultadoBorrarFoto">
    <h2>Eliminar foto</h2>

    <?php if ($mensaje): ?>
        <p><?= htmlspecialchars($mensaje) ?></p>
        <p><a href="verFotos.php?id=<?= $foto['Anuncio'] ?? 0 ?>" class="boton">Volver a las fotos</a></p>
    <?php else: ?>
        <form action="" method="post">
            <p>Vas a eliminar la foto <strong><?= htmlspecialchars(basename($foto['Foto'])) ?></strong> del anuncio.</p>
            <p>Introduce tu contraseña para confirmar:</p>
            <input type="password" name="contrasenya" required>
            <button type="submit" class="boton">Eliminar foto</button>
            <a href="verFotos.php?id=<?= $foto['Anuncio'] ?>" class="boton">Cancelar</a>
        </form>
    <?php endif; ?>
</section>
</main>

<?php require_once("pie.php"); ?>
