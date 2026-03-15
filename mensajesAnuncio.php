<?php
require_once("controlSesion.php");
require_once("conexionBD.php");

// ==========================
// CONTROL DE SESIÓN
// ==========================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$usuario_actual = $_SESSION['usuario'] ?? null;

if (!$logueado) {
    header("Location: index.php");
    exit;
}

// ==========================
// OBTENER ID DEL USUARIO
// ==========================
$sql = "SELECT IdUsuario FROM usuarios WHERE NomUsuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario_actual);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $result->fetch_assoc();
$usuarioId = $usuario['IdUsuario'];

// ==========================
// OBTENER ID DEL ANUNCIO
// ==========================
$idAnuncio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idAnuncio <= 0) {
    header("Location: misAnuncios.php");
    exit;
}

// ==========================
// OBTENER EL TÍTULO DEL ANUNCIO
// ==========================
$sql_Anuncio = "SELECT Titulo FROM anuncios WHERE IdAnuncio = ?";
$stmt = $conexion->prepare($sql_Anuncio);
$stmt->bind_param("i", $idAnuncio);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Anuncio no encontrado.";
    exit;
}

$anuncio = $result->fetch_assoc();
$Titulo = $anuncio['Titulo'];



// ==========================================================
// MENSAJES RECIBIDOS RELACIONADOS AL ANUNCIO
// ==========================================================
$sql_recibidos = "SELECT m.Texto, m.FRegistro, u.NomUsuario AS Remitente
                  FROM mensajes m
                  JOIN usuarios u ON m.UsuOrigen = u.IdUsuario
                  WHERE m.UsuDestino = ? AND m.Anuncio = ?
                  ORDER BY m.FRegistro DESC";
$stmt_recibidos = $conexion->prepare($sql_recibidos);
$stmt_recibidos->bind_param("ii", $usuarioId, $idAnuncio);
$stmt_recibidos->execute();
$result_recibidos = $stmt_recibidos->get_result();
$total_recibidos = $result_recibidos->num_rows;

// ==========================================================
// CABECERA
// ==========================================================
$encabezado = "MENSAJES DEL ANUNCIO: " . strtoupper($Titulo);
$style = "misMensajes.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<main>
    <aside class="panel-mensajes">
        <h3>Mensajes recibidos (<?= $total_recibidos ?>)</h3>
        <?php if ($total_recibidos > 0): ?>
            <table class="tabla-mensajes-recibidos">
                <thead>
                    <tr>
                        <th>Texto</th>
                        <th>Fecha</th>
                        <th>Remitente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($msg = $result_recibidos->fetch_assoc()): ?>
                        <tr class="recibido">
                            <td><?= htmlspecialchars($msg['Texto']) ?></td>
                            <td><?= htmlspecialchars($msg['FRegistro']) ?></td>
                            <td><?= htmlspecialchars($msg['Remitente']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay mensajes recibidos para este anuncio.</p>
        <?php endif; ?>
    </aside>

    <nav class="acciones">
        <a href="verAnuncio.php?id=<?= urlencode($idAnuncio) ?>" class="boton">Volver al anuncio</a>
        <a href="mensaje.php?id=<?= urlencode($idAnuncio) ?>" class="boton">Enviar un mensaje</a>
    </nav>

</main>

<?php
require_once("pie.php");
?>
