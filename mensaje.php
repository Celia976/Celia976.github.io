<?php
require_once("controlSesion.php");
require_once("conexionBD.php");

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$usuarioActual = $_SESSION['usuario'] ?? null;

$encabezado = "Enviar un mensaje";
$style = "index.css";
$style2 = "comunFormularios.css";

require_once("cabecera.php");

if (!$logueado) {
    header("Location: index.php");
    exit;
}

// ======================================================
// OBTENER ID DEL USUARIO ACTUAL
// ======================================================
$stmt = $conexion->prepare("SELECT IdUsuario FROM usuarios WHERE NomUsuario = ?");
$stmt->bind_param("s", $usuarioActual);
$stmt->execute();
$idUsuario = $stmt->get_result()->fetch_assoc()['IdUsuario'];

// ======================================================
// DETECTAR SI VENIMOS DESDE DETALLEANUNCIO.PHP
// ======================================================
$idDesdeDetalle = isset($_GET['id']) ? (int)$_GET['id'] : null;
$anuncioSeleccionado = null;

if ($idDesdeDetalle) {
    $sql_det = "SELECT a.IdAnuncio, a.Titulo, u.IdUsuario AS Propietario
                FROM anuncios a
                JOIN usuarios u ON a.Usuario = u.IdUsuario
                WHERE a.IdAnuncio = ? AND a.Usuario != ?";

    $stmt_det = $conexion->prepare($sql_det);
    $stmt_det->bind_param("ii", $idDesdeDetalle, $idUsuario);
    $stmt_det->execute();
    $anuncioSeleccionado = $stmt_det->get_result()->fetch_assoc();
}

// ======================================================
// SI NO VENIMOS DE DETALLE, MOSTRAR LISTA COMPLETA
// ======================================================
$sql_anuncios = "SELECT a.IdAnuncio, a.Titulo, u.IdUsuario AS Propietario
                 FROM anuncios a
                 JOIN usuarios u ON a.Usuario = u.IdUsuario
                 WHERE a.Usuario != ?";

$stmt_anuncios = $conexion->prepare($sql_anuncios);
$stmt_anuncios->bind_param("i", $idUsuario);
$stmt_anuncios->execute();
$result_anuncios = $stmt_anuncios->get_result();
?>

<main>
    <form action="respuestaMensaje.php" method="post" class="datos">
        <fieldset >
            <legend>Envíe su mensaje</legend>

            <!-- =======================
                 SELECCIÓN DEL ANUNCIO
                 ======================= -->
            <p>
                <label for="idAnuncio">Seleccione el anuncio:</label>
                <select name="idAnuncio" id="idAnuncio" required>

                    <?php if ($anuncioSeleccionado): ?>
                        <!-- Modo “desde detalle”: solo un anuncio -->
                        <option value="<?= $anuncioSeleccionado['IdAnuncio'] ?>|<?= $anuncioSeleccionado['Propietario'] ?>" selected>
                            <?= htmlspecialchars($anuncioSeleccionado['Titulo']) ?>
                        </option>

                    <?php else: ?>
                        <!-- Modo normal: lista de anuncios -->
                        <?php while($a = $result_anuncios->fetch_assoc()): ?>
                            <option value="<?= $a['IdAnuncio'] ?>|<?= $a['Propietario'] ?>">
                                <?= htmlspecialchars($a['Titulo']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>

                </select>
            </p>

            <!-- =======================
                 TIPO DE MENSAJE
                 ======================= -->
            <p>
                <label for="mensaje">Tipo de mensaje:</label>
                <select id="mensaje" name="mensaje" required>
                    <option value="">--Selecciona--</option>

                    <?php
                    $sql_tipos = "SELECT IdTMensaje, NomTMensaje FROM tiposmensajes ORDER BY NomTMensaje ASC";
                    $result_tipos = $conexion->query($sql_tipos);

                    if ($result_tipos && $result_tipos->num_rows > 0) {
                        while($tipo = $result_tipos->fetch_assoc()) {
                            $id = htmlspecialchars($tipo['IdTMensaje']);
                            $nombre = htmlspecialchars($tipo['NomTMensaje']);
                            echo "<option value=\"$id\">$nombre</option>";
                        }
                        $result_tipos->free();
                    } else {
                        echo '<option value="" disabled>No hay tipos disponibles</option>';
                    }
                    ?>

                </select>
            </p>

            <!-- =======================
                 CUADRO DE TEXTO
                 ======================= -->
            <p>
                <label for="cuadroTexto">Escribe tu mensaje:</label>
                <textarea name="cuadroTexto" id="cuadroTexto" placeholder="Escribe aquí tu mensaje..." required></textarea>
            </p>

            <aside class="acciones">
                <button type="submit" class="enviar">Enviar Mensaje</button>
                <button type="reset" class="cancelar">Borrar Mensaje</button>
            </aside>

        </fieldset>
    </form>
</main>

<?php
require_once("pie.php");
?>
