<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ===========================
// Comprobación POST
// ===========================
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: modificarAnuncio.php");
    exit;
}

// ===========================
// Comprobación de sesión
// ===========================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: formularioAcceso.php");
    exit;
}

// ===========================
// ID del anuncio y del usuario
// ===========================
$anuncioId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$idUsuario = $_SESSION['usuario_id'] ?? null;

if (!$anuncioId || !$idUsuario) {
    header("Location: perfilUsuario.php?error=" . urlencode("ID de anuncio o de usuario no encontrado."));
    exit;
}

// ===========================
// Recuperar y validar datos del formulario
// ===========================
$TAnuncio = intval($_POST['TAnuncio'] ?? 0);
$TVivienda = intval($_POST['TVivienda'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$Texto = trim($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$pais = intval($_POST['pais'] ?? 0);
$ciudad = trim($_POST['localidad'] ?? '');
$plantas = intval($_POST['plantas'] ?? 0);
$superficie = intval($_POST['superficie'] ?? 0);
$NHabitaciones = intval($_POST['habitaciones'] ?? 0);
$NBanyos = intval($_POST['banos'] ?? 0);
$Anyo = intval($_POST['anio'] ?? 0);

// ============================
// Guardar los extras con delimitador " | "
// ============================
$extrasSeleccionados = $_POST['extras'] ?? [];
$extrasGuardados = implode(' | ', $extrasSeleccionados);

// ===========================
// Actualización en la base de datos
// ===========================
$sql = "UPDATE Anuncios SET 
        TAnuncio = ?, 
        TVivienda = ?, 
        Titulo = ?, 
        Texto = ?, 
        Precio = ?, 
        Ciudad = ?, 
        Pais = ?, 
        Superficie = ?, 
        Planta = ?, 
        NHabitaciones = ?, 
        NBanyos = ?, 
        Anyo = ?,
        Extras = ?
        WHERE IdAnuncio = ? AND Usuario = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Location: modificarAnuncio.php?id=$anuncioId&error=" . urlencode("Error en la preparación de la consulta: " . $conexion->error));
    exit;
}

$stmt->bind_param(
    "iissdsiiiiiisii",
    $TAnuncio,
    $TVivienda,
    $titulo,
    $Texto,
    $precio,
    $ciudad,
    $pais,
    $superficie,
    $plantas,
    $NHabitaciones,
    $NBanyos,
    $Anyo,
    $extrasGuardados,
    $anuncioId,
    $idUsuario
);


// ===========================
// Ejecutar consulta
// ===========================
if ($stmt->execute()) {
    $filasAfectadas = $stmt->affected_rows;
    $stmt->close();
    $conexion->close();

    if ($filasAfectadas > 0) {
        header("Location: verAnuncio.php?id=$anuncioId&exito=" . urlencode("¡Anuncio modificado con éxito!"));
    } else {
        header("Location: perfilUsuario.php?aviso=" . urlencode("El anuncio #$anuncioId fue encontrado, pero no se realizó ningún cambio."));
    }
    exit;
} else {
    $error = urlencode("Error al actualizar: " . $stmt->error);
    $stmt->close();
    $conexion->close();
    header("Location: modificarAnuncio.php?id=$anuncioId&error=" . $error);
    exit;
}
?>
