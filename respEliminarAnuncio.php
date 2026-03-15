<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ==============================
// Comprobación de sesión
// ==============================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$idUsuario = $_SESSION['usuario_id'] ?? 0;
$idAnuncio = intval($_REQUEST['id'] ?? 0);

if (!$logueado || !$idUsuario || !$idAnuncio) {
    header("Location: formularioAcceso.php");
    exit();
}

$encabezado = "ELIMINAR ANUNCIO";
$style = "detAnunEstilo.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");

$mensaje = "";
$clase_mensaje = "";

// ==============================
// Lógica de Eliminación 
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {

    // ---Obtener la ruta de la foto principal ANTES de borrar el registro ---
    $sqlFoto = "SELECT FPrincipal FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
    $stmtFoto = $conexion->prepare($sqlFoto);
    $stmtFoto->bind_param("ii", $idAnuncio, $idUsuario);
    $stmtFoto->execute();
    $resultadoFoto = $stmtFoto->get_result();
    $anuncio = $resultadoFoto->fetch_assoc();
    $fotoPrincipal = $anuncio['FPrincipal'] ?? null;
    $stmtFoto->close();

    // ---Eliminar todas las fotos asociadas en la tabla Fotos ---
    $sqlFotos = "SELECT Foto FROM Fotos WHERE Anuncio = ?";
    $stmtFotos = $conexion->prepare($sqlFotos);
    $stmtFotos->bind_param("i", $idAnuncio);
    $stmtFotos->execute();
    $resultFotos = $stmtFotos->get_result();

    while ($foto = $resultFotos->fetch_assoc()) {
        // Ruta de la carpeta donde se guardan las fotos nuevas
        $rutaArchivo = __DIR__ . "/fotos_anuncios/" . $foto['Foto'];
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo); // elimina el archivo físico
        }
    }
    $stmtFotos->close();

    // Eliminar registros de Fotos
    $sqlDeleteFotos = "DELETE FROM Fotos WHERE Anuncio = ?";
    $stmtDeleteFotos = $conexion->prepare($sqlDeleteFotos);
    $stmtDeleteFotos->bind_param("i", $idAnuncio);
    $stmtDeleteFotos->execute();
    $stmtDeleteFotos->close();

    // --- Eliminar el anuncio de la base de datos ---
    $sqlDelete = "DELETE FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
    $stmtDelete = $conexion->prepare($sqlDelete);
    $stmtDelete->bind_param("ii", $idAnuncio, $idUsuario);

    if ($stmtDelete->execute()) {
        if ($stmtDelete->affected_rows > 0) {
            // --- Eliminar la foto principal del anuncio  ---
            if (!empty($fotoPrincipal)) {
                $rutaArchivo = __DIR__ . "/fotos_anuncios/" . $fotoPrincipal;
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }

            $mensaje = "El anuncio ha sido **eliminado satisfactoriamente**.";
            $clase_mensaje = "exito";
        } else {
            $mensaje = "Error: El anuncio con ID **{$idAnuncio}** no existe o no te pertenece.";
            $clase_mensaje = "error";
        }
    } else {
        $mensaje = "Error de base de datos al intentar eliminar el anuncio: " . $stmtDelete->error;
        $clase_mensaje = "error";
    }

    $stmtDelete->close();
    $conexion->close();

} else {
    // ==============================
    // Mostrar formulario de confirmación 
    // ==============================
    $sql = "SELECT Titulo FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idAnuncio, $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $anuncio = $resultado->fetch_assoc();
    $stmt->close();

    if (!$anuncio) {
        $mensaje = "Error: El anuncio seleccionado no existe o no te pertenece.";
        $clase_mensaje = "error";
        $conexion->close();
    } else {
        $tituloAnuncio = $anuncio['Titulo'];
        $conexion->close();
    }
}
?>

<main>
    <h2>Confirmación de Eliminación</h2>

    <?php if (!empty($mensaje)): ?>

        <p class="<?= $clase_mensaje ?>"><?= $mensaje ?></p>
        <p class="accion">
            <a href="misAnuncios.php" class="boton-volver">Volver a Mis Anuncios</a>
        </p>

    <?php elseif (isset($tituloAnuncio)): ?>

        <p class="aviso">
            Estás a punto de eliminar el anuncio: <strong><?= htmlspecialchars($tituloAnuncio) ?></strong>
        </p>
        <p>
            Esta acción es <strong>irreversible</strong> y se eliminarán todos los datos relacionados con este anuncio. ¿Estás seguro de que deseas continuar?
        </p>

        <form action="respEliminarAnuncio.php" method="post" class="formConfirmacion">
            <input type="hidden" name="id" value="<?= $idAnuncio ?>">
            <input type="hidden" name="confirmar_eliminar" value="1">
            <button type="submit" class="boton-eliminar">Sí</button>
            <a href="misAnuncios.php" class="cancelar">Cancelar y Volver</a>
        </form>

    <?php endif; ?>

</main>

<?php
require_once("pie.php");
?>
