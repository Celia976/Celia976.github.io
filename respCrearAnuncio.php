<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ===========================
// Comprobación POST
// ===========================
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: crearAnuncio.php");
    exit;
}

// ===========================
// Comprobación de sesión
// ===========================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: inicio.php");
    exit;
}

// ===========================
// ID del usuario logueado
// ===========================
$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
    header("Location: crearAnuncio.php?error=" . urlencode("ID de usuario no encontrado"));
    exit;
}

// ===========================
// Recuperar y sanitizar datos del formulario
// ===========================
$TAnuncio = filter_input(INPUT_POST, 'TAnuncio', FILTER_VALIDATE_INT);
$TVivienda = filter_input(INPUT_POST, 'TVivienda', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo']);
$Texto = trim($_POST['descripcion']);
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$pais = filter_input(INPUT_POST, 'pais', FILTER_VALIDATE_INT);
$ciudad = trim($_POST['localidad']);
$plantas = filter_input(INPUT_POST, 'plantas', FILTER_VALIDATE_INT) ?? 0;
$superficie = filter_input(INPUT_POST, 'superficie', FILTER_VALIDATE_INT);
$NHabitaciones = filter_input(INPUT_POST, 'habitaciones', FILTER_VALIDATE_INT);
$NBanyos = filter_input(INPUT_POST, 'banos', FILTER_VALIDATE_INT);
$Anyo = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);

// ===========================
// Recuperar y procesar Extras
// ===========================
$extrasSeleccionados = $_POST['extras'] ?? [];
$extrasTexto = implode(" | ", array_map('trim', $extrasSeleccionados));

// ===========================
// Validación de datos
// ===========================
if (!$TAnuncio || !$TVivienda || empty($titulo) || empty($Texto) || $precio < 0 ||
    !$pais || empty($ciudad) || !$superficie || !$NHabitaciones || !$NBanyos || !$Anyo) {

    header("Location: crearAnuncio.php?error=" . urlencode("Por favor, revisa los campos obligatorios marcados con *."));
    exit;
}

// ===========================
// Inserción en la base de datos
// ===========================
$sql = "INSERT INTO Anuncios 
        (Usuario, TAnuncio, TVivienda, Titulo, Texto, Precio, Ciudad, Pais, Superficie, Planta, NHabitaciones, NBanyos, Anyo, Extras, FRegistro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Location: crearAnuncio.php?error=" . urlencode("Error en la preparación de la consulta: " . $conexion->error));
    exit;
}

$stmt->bind_param(
    "iiissdsiiiiiis",
    $idUsuario,
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
    $extrasTexto 
);

// ===========================
// Ejecutar consulta
// ===========================
if ($stmt->execute()) {
    $newID = $conexion->insert_id;
    $stmt->close();
    $conexion->close();

    // Redirigir a página de subir foto con mensaje de éxito
    header("Location: anadirFotoAnuncio.php?id=$newID&exito=" . urlencode("¡Anuncio creado con éxito! Ahora añade tu primera foto."));
    exit;
} else {
    $error = urlencode("Error al insertar: " . $stmt->error);
    $stmt->close();
    $conexion->close();
    header("Location: crearAnuncio.php?error=" . $error);
    exit;
}
?>