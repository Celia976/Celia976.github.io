<?php
session_start();
require_once("conexionBD.php");

// ==========================================================
// Comprobar login
// ==========================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: formularioAcceso.php");
    exit();
}

// ==========================================================
// Recoger el estilo seleccionado desde la sesión
// ==========================================================
$estiloSeleccionado = $_SESSION['estilo_seleccionado'] ?? null;

if (!$estiloSeleccionado) {
    // Si no hay estilo en sesión, redirigir a elegir
    header("Location: eligeTuEstilo.php");
    exit();
}

// ==========================================================
// Obtener IdEstilo desde la tabla estilos
// ==========================================================
$stmt = $conexion->prepare("SELECT IdEstilo FROM estilos WHERE Nombre = ?");
$stmt->bind_param("s", $estiloSeleccionado);
$stmt->execute();
$result = $stmt->get_result();

if ($fila = $result->fetch_assoc()) {
    $idEstilo = $fila['IdEstilo'];

    // Guardar el estilo en la BBDD del usuario actual
    $stmtUpdate = $conexion->prepare("UPDATE usuarios SET Estilo = ? WHERE IdUsuario = ?");
    $stmtUpdate->bind_param("ii", $idEstilo, $_SESSION['usuario_id']);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

$stmt->close();

// ==========================================================
// Cabecera con el estilo seleccionado
// ==========================================================
$encabezado = "Estilo cambiado con éxito";
$style = $estiloSeleccionado . ".css";
$style2 = "index.css";
require_once("cabecera.php");
?>

<main>
    <section class="resultadoRegistro">
        <h2>¡Estilo cambiado correctamente!</h2>
        <p>Ahora estás usando el estilo: <strong><?= htmlspecialchars($estiloSeleccionado) ?></strong></p>
        <p><a href="perfilUsuario.php" class="botonVolver">Volver a mi perfil</a></p>
    </section>
</main>

<?php require_once("pie.php"); ?>
<?php $conexion->close(); ?>