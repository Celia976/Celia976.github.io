<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ===========================
// Comprobación POST y Sesión
// ===========================
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: perfilUsuario.php");
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

if (
    $TAnuncio === null || $TAnuncio === false || 
    $TVivienda === null || $TVivienda === false || 
    empty($titulo) || 
    empty($Texto) || 
    $precio === false || $precio < 0 || 
    $pais === null || $pais === false || 
    empty($ciudad) || 
    $superficie === null || $superficie === false || $superficie <= 0 || 
    $plantas === null || $plantas === false || $plantas <= 0 || 
    $NHabitaciones === null || $NHabitaciones === false || $NHabitaciones <= 0 || 
    $NBanyos === null || $NBanyos === false || $NBanyos <= 0 || 
    $Anyo === null || $Anyo === false || $Anyo < 1900 
) {
    // Redirigir si alguna validación falla
    header("Location: modificarAnuncio.php?id=$anuncioId&error=" . urlencode("Por favor, revisa todos los datos del formulario (selects, números, etc.)."));
    exit;
}

// ===========================
// Actualización en la base de datos 
// ===========================
$sql = "UPDATE Anuncios SET 
        TAnuncio = ?, TVivienda = ?, Titulo = ?, Texto = ?, Precio = ?, 
        Ciudad = ?, Pais = ?, Superficie = ?, Planta = ?, NHabitaciones = ?, 
        NBanyos = ?, Anyo = ?, FModificacion = NOW()
        WHERE IdAnuncio = ? AND Usuario = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    // Manejo de error de preparación
    header("Location: modificarAnuncio.php?id=$anuncioId&error=" . urlencode("Error en la preparación de la consulta: " . $conexion->error));
    exit;
}

$stmt->bind_param(
    "iissdsiiiiiiii",
    $TAnuncio, $TVivienda, $titulo, $Texto, $precio,
    $ciudad, $pais, $superficie, $plantas, $NHabitaciones,
    $NBanyos, $Anyo, $anuncioId, $idUsuario
);

if (!$stmt->execute()) {
    // Manejo de error de ejecución
    $error = urlencode("Error al actualizar: " . $stmt->error);
    $stmt->close();
    $conexion->close();
    header("Location: modificarAnuncio.php?id=$anuncioId&error=" . $error);
    exit;
}

$stmt->close();

// ==========================================================
// CONSULTA PARA OBTENER LOS DATOS ACTUALIZADOS DEL ANUNCIO
// ==========================================================
$sqlAnuncio = "SELECT 
                    a.*,
                    ta.NomTAnuncio,
                    tv.NomTVivienda,
                    p.NomPais
                FROM Anuncios a
                JOIN TiposAnuncios ta ON a.TAnuncio = ta.IdTAnuncio
                JOIN TiposViviendas tv ON a.TVivienda = tv.IdTVivienda
                JOIN Paises p ON a.Pais = p.IdPais
                WHERE a.IdAnuncio = ?";

$stmtAnuncio = $conexion->prepare($sqlAnuncio);
$stmtAnuncio->bind_param("i", $anuncioId);
$stmtAnuncio->execute();
$resultadoAnuncio = $stmtAnuncio->get_result();
$anuncioActual = $resultadoAnuncio->fetch_assoc();

$stmtAnuncio->close();
$conexion->close();

// Si el anuncio no se encuentra por alguna razón, redirigir
if (!$anuncioActual) {
    header("Location: perfilUsuario.php?error=" . urlencode("Anuncio modificado, pero no se pudo recuperar para mostrar la confirmación."));
    exit;
}

// ===========================
// INICIO DE LA PÁGINA DE CONFIRMACIÓN
// ===========================

$encabezado = "ANUNCIO ACTUALIZADO";
$style = "FolletoEstilo.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<main>
    <h2>¡Anuncio modificado con éxito!</h2>
    <p class="exito">El anuncio **#<?= htmlspecialchars($anuncioId) ?>** ha sido guardado correctamente con la siguiente información:</p>

    <div class="respuesta-datos-container">
        
        <div class="perfil-foto">
            <img src="imagenes_anuncios/default_anuncio.png" alt="Foto principal del anuncio" width="150">
        </div>

        <div class="perfil-datos">
            <h3>Detalles del Anuncio</h3>
            <table>
                <tr>
                    <th>Título:</th>
                    <td><?= htmlspecialchars($anuncioActual['Titulo']) ?></td>
                </tr>
                <tr>
                    <th>Tipo de Operación:</th>
                    <td><?= htmlspecialchars($anuncioActual['NomTAnuncio']) ?></td>
                </tr>
                <tr>
                    <th>Tipo de Inmueble:</th>
                    <td><?= htmlspecialchars($anuncioActual['NomTVivienda']) ?></td>
                </tr>
                <tr>
                    <th>Precio:</th>
                    <td><?= number_format($anuncioActual['Precio'], 2, ',', '.') ?> €</td>
                </tr>
                <tr>
                    <th>País:</th>
                    <td><?= htmlspecialchars($anuncioActual['NomPais']) ?></td>
                </tr>
                <tr>
                    <th>Ciudad:</th>
                    <td><?= htmlspecialchars($anuncioActual['Ciudad']) ?></td>
                </tr>
                <tr>
                    <th>Superficie:</th>
                    <td><?= htmlspecialchars($anuncioActual['Superficie']) ?> m²</td>
                </tr>
                <tr>
                    <th>Habitaciones:</th>
                    <td><?= htmlspecialchars($anuncioActual['NHabitaciones']) ?></td>
                </tr>
                <tr>
                    <th>Baños:</th>
                    <td><?= htmlspecialchars($anuncioActual['NBanyos']) ?></td>
                </tr>
                <tr>
                    <th>Año de Construcción:</th>
                    <td><?= htmlspecialchars($anuncioActual['Anyo']) ?></td>
                </tr>
            </table>
            
            <p>
                <strong>Descripción:</strong><br>
                <?= nl2br(htmlspecialchars($anuncioActual['Texto'])) ?>
            </p>
        </div>

    </div>

    <p class="accion">
        <a href="perfilUsuario.php" class="boton-volver"> Volver a mis anuncios</a>
        </p>

</main>

<?php
require_once("pie.php");
?>