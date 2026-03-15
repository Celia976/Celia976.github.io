<?php
require_once("controlSesion.php");

$encabezado = "AÑADIR FOTO AL ANUNCIO";
$style = "FolletoEstilo.css";
$style2 = "coloresPredeterminados.css";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok'; 
$nombre_usuario_actual = $_SESSION['usuario'];

require_once("cabecera.php");

// Mensajes
if (isset($_GET['exito'])) {
    echo "<p class='exito'>" . htmlspecialchars($_GET['exito']) . "</p>";
}
if (isset($_GET['error'])) {
    echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
}

$idAnuncio = $_GET['id'] ?? null;

require_once(__DIR__ . "/conexionBD.php");

if ($idAnuncio) {
    $sql = "SELECT IdAnuncio, Titulo FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idAnuncio, $_SESSION['usuario_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $anuncio = $res->fetch_assoc();
    $stmt->close();

    if (!$anuncio) {
        echo "<p class='error'>El anuncio no existe o no te pertenece.</p>";
        exit;
    }
} else {
    $sql = "SELECT IdAnuncio, Titulo FROM Anuncios WHERE Usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $misAnuncios = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$misAnuncios) {
        echo "<p class='error'>No tienes anuncios disponibles para añadir fotos.</p>";
        exit;
    }

    $anuncio = null;
}

$conexion->close();
?>

<main>
    <form action="respSubirFoto.php" method="post" enctype="multipart/form-data" class="campos">
        <fieldset>
            <legend>Añadir foto al anuncio</legend>

            <?php if ($anuncio): ?>
                <p>
                    <label for="anuncio">Anuncio seleccionado:</label><br>
                    <input type="text" id="anuncio" value="<?= htmlspecialchars($anuncio['Titulo']) ?>" readonly>
                    <input type="hidden" name="idAnuncio" value="<?= htmlspecialchars($anuncio['IdAnuncio']) ?>">
                </p>
            <?php else: ?>
                <p>
                    <label for="idAnuncio">Selecciona un anuncio:</label><br>
                    <select name="idAnuncio" id="idAnuncio" required>
                        <option value="">-- Selecciona un anuncio --</option>
                        <?php foreach ($misAnuncios as $a): ?>
                            <option value="<?= htmlspecialchars($a['IdAnuncio']) ?>">
                                <?= htmlspecialchars($a['Titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            <?php endif; ?>

            <p>
                <label for="foto">Seleccionar foto: <span class="obligatorio">*</span></label><br>
                <input type="file" id="foto" name="foto" accept="image/*" required>
            </p>

            <p>
                <label for="titulo">Título de la foto:</label><br>
                <input type="text" id="titulo" name="titulo" maxlength="200" required>
            </p>

            <p>
                <label for="alt">Texto alternativo (mínimo 10 caracteres):</label><br>
                <input type="text" id="alt" name="alt" minlength="10" required>
            </p>

            <p>
                <button type="submit">Subir foto</button>
            </p>

            <p class="acciones">
                <a href="verAnuncio.php?id=<?= urlencode($idAnuncio) ?>" class="cancelar">Volver al anuncio</a>
            </p>
        </fieldset>
    </form>
</main>

<?php require_once("pie.php"); ?>