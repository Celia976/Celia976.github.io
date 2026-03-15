<?php
require_once("controlSesion.php");
require_once("conexionBD.php");

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$usuarioActual = $_SESSION['usuario'] ?? null;

$encabezado = "CONFIRMACIÓN DE ENVÍO DE MENSAJE";
$style = "index.css";
$style2 = "misMensajes.css";

require_once("cabecera.php");

if (!$logueado) {
    header("Location: index.php");
    exit;
}

// Datos del formulario
$tipoMensajeId = $_POST['mensaje'] ?? '';
$mensaje = $_POST['cuadroTexto'] ?? '';
$idAnuncioPropietario = $_POST['idAnuncio'] ?? '';

$mensajeEnviado = false;
$error = [];

// Separar IdAnuncio y IdUsuario propietario
if (!empty($idAnuncioPropietario)) {
    list($idAnuncio, $usuDestino) = explode("|", $idAnuncioPropietario);
}

// Validar campos
if(!empty($tipoMensajeId) && !empty($mensaje) && !empty($idAnuncio) && !empty($usuDestino)) {

    // Obtener IdUsuario del emisor
    $stmt_emisor = $conexion->prepare("SELECT IdUsuario FROM usuarios WHERE NomUsuario = ?");
    $stmt_emisor->bind_param("s", $usuarioActual);
    $stmt_emisor->execute();
    $emisor = $stmt_emisor->get_result()->fetch_assoc()['IdUsuario'];
    $stmt_emisor->close();

    // Insertar mensaje en la base de datos
    $stmt_insert = $conexion->prepare("
        INSERT INTO mensajes (TMensaje, Texto, Anuncio, UsuOrigen, UsuDestino)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt_insert->bind_param("isiii", $tipoMensajeId, $mensaje, $idAnuncio, $emisor, $usuDestino);
    if($stmt_insert->execute()) {
        $mensajeEnviado = true;
    } else {
        $error[] = "Error al guardar el mensaje en la base de datos.";
    }
    $stmt_insert->close();

    // Obtener nombre de usuario destinatario
    $stmt_dest = $conexion->prepare("SELECT NomUsuario FROM usuarios WHERE IdUsuario = ?");
    $stmt_dest->bind_param("i", $usuDestino);
    $stmt_dest->execute();
    $destinatario = $stmt_dest->get_result()->fetch_assoc()['NomUsuario'];
    $stmt_dest->close();

    // Obtener nombre del tipo de mensaje
    $stmt_tipoNombre = $conexion->prepare("SELECT NomTMensaje FROM tiposmensajes WHERE IdTMensaje = ?");
    $stmt_tipoNombre->bind_param("i", $tipoMensajeId);
    $stmt_tipoNombre->execute();
    $nombreTipo = $stmt_tipoNombre->get_result()->fetch_assoc()['NomTMensaje'];
    $stmt_tipoNombre->close();

    $fechaEnvio = date("d/m/Y");

} else {
    $error[] = "Debe completar todos los campos del formulario.";
}
?>

<main>
    <aside class="confirmacion">
        <?php if($mensajeEnviado): ?>
            <h2>Su mensaje ha sido enviado correctamente.</h2>
            <p>Datos del mensaje:</p>
            <hr>
            <ul>
                <li><strong>Destinatario: </strong><?= htmlspecialchars($destinatario) ?></li>
                <li><strong>Tipo de Mensaje: </strong><?= htmlspecialchars($nombreTipo) ?></li>
                <li><strong>Mensaje: </strong><?= htmlspecialchars($mensaje) ?></li>
                <li><strong>Fecha: </strong><?= $fechaEnvio ?></li>
            </ul>
        <?php else: ?>
            <h2>Error al enviar el mensaje</h2>
            <?php foreach($error as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <nav class="acciones">
            <a class="boton" href="mismensajes.php">Volver a mis mensajes</a>
            <a class="boton" href="perfilUsuario.php">Volver al perfil</a>
        </nav>
    </aside>
</main>

<?php
require_once("pie.php");
?>
