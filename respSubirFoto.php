<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ===========================
// Comprobación POST y sesión
// ===========================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: misAnuncios.php");
    exit;
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: inicio.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'] ?? null;
if (!$idUsuario) {
    header("Location: misAnuncios.php?error=" . urlencode("Usuario no encontrado."));
    exit;
}

// ===========================
// Recuperar datos del formulario
// ===========================
$idAnuncio = filter_input(INPUT_POST, 'idAnuncio', FILTER_VALIDATE_INT);
$tituloFoto = trim($_POST['titulo']);
$altTexto = trim($_POST['alt']);

// ===========================
// Validaciones del anuncio
// ===========================
if (!$idAnuncio) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("Debes seleccionar un anuncio."));
    exit;
}

// Comprobar que el anuncio pertenece al usuario
$sql = "SELECT IdAnuncio FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idAnuncio, $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
$anuncio = $res->fetch_assoc();
$stmt->close();

if (!$anuncio) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("Ese anuncio no te pertenece."));
    exit;
}

// ===========================
// Validaciones de campos
// ===========================
if (empty($tituloFoto)) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("El título de la foto es obligatorio."));
    exit;
}

if (empty($altTexto) || strlen($altTexto) < 10) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("El texto alternativo debe tener al menos 10 caracteres."));
    exit;
}

// Comprobar palabras prohibidas al inicio
$palabrasProhibidas = ['foto', 'imagen'];
$primerasPalabras = strtolower(substr($altTexto, 0, 10));
foreach ($palabrasProhibidas as $palabra) {
    if (str_starts_with($primerasPalabras, $palabra)) {
        header("Location: anadirFotoAnuncio.php?error=" . urlencode("El texto alternativo no puede comenzar con '$palabra'."));
        exit;
    }
}

// ===========================
// Validar archivo subido
// ===========================
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("Error al subir el archivo."));
    exit;
}

// Obtener info del archivo
$archivoTmp = $_FILES['foto']['tmp_name'];
$nombreOriginal = basename($_FILES['foto']['name']);
$extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);

// ===========================
// Generar nombre único
// ===========================
$timestamp = time();
$nombreUnico = "u{$idUsuario}_a{$idAnuncio}_{$timestamp}." . $extension;

// ===========================
// Guardar solo el nombre en la BD
// ===========================
$rutaBD = $nombreUnico;

// ===========================
// Mover archivo a destino
// ===========================
$carpetaDestino = __DIR__ . "/fotos_anuncios/";
if (!is_dir($carpetaDestino)) {
    mkdir($carpetaDestino, 0755, true);
}

$rutaDestino = $carpetaDestino . $nombreUnico;
if (!move_uploaded_file($archivoTmp, $rutaDestino)) {
    header("Location: anadirFotoAnuncio.php?error=" . urlencode("No se pudo mover el archivo al servidor."));
    exit;
}

// ===========================
// Guardar en la base de datos
// ===========================
$sql = "INSERT INTO Fotos (Anuncio, Titulo, Alternativo, Foto) VALUES (?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("isss", $idAnuncio, $tituloFoto, $altTexto, $rutaBD);

if ($stmt->execute()) {
    $stmt->close();

    // ===========================
    // Comprobar si el anuncio ya tiene foto principal
    // ===========================
    $sqlCheck = "SELECT FPrincipal FROM Anuncios WHERE IdAnuncio = ?";
    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $idAnuncio);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $anuncioInfo = $resCheck->fetch_assoc();
    $stmtCheck->close();

    if (empty($anuncioInfo['FPrincipal'])) {
        // ===========================
        // Actualizar el anuncio con la nueva foto como principal
        // ===========================
        $sqlUpdate = "UPDATE Anuncios SET FPrincipal = ? WHERE IdAnuncio = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $rutaBD, $idAnuncio);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $conexion->close();
    header("Location: perfilUsuario.php?exito=" . urlencode("Foto subida correctamente."));
    exit;
} else {
    $error = urlencode("Error al guardar en la base de datos: " . $stmt->error);
    $stmt->close();
    $conexion->close();
    header("Location: anadirFotoAnuncio.php?error=" . $error);
    exit;
}