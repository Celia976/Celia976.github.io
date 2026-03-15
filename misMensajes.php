<?php
require_once("controlSesion.php");
require_once("conexionBD.php");

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

if (!$logueado) {
    header("Location: index.php");
    exit;
}

// Obtener datos del usuario desde la sesión
$nombre_usuario_actual = $_SESSION['usuario'];

// Obtener el IdUsuario 
$sql = "SELECT IdUsuario FROM usuarios WHERE NomUsuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $nombre_usuario_actual);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $result->fetch_assoc();
$usuarioId = $usuario['IdUsuario'];

// ==========================================================
// Obtener mensajes enviados
// ==========================================================
$sql_enviados = "SELECT m.Texto, m.FRegistro, u.NomUsuario AS Destinatario
                 FROM mensajes m
                 JOIN usuarios u ON m.UsuDestino = u.IdUsuario
                 WHERE m.UsuOrigen = ?
                 ORDER BY m.FRegistro DESC";
$stmt_enviados = $conexion->prepare($sql_enviados);
$stmt_enviados->bind_param("i", $usuarioId);
$stmt_enviados->execute();
$result_enviados = $stmt_enviados->get_result();
$total_enviados = $result_enviados->num_rows;

// ==========================================================
// Obtener mensajes recibidos
// ==========================================================
$sql_recibidos = "SELECT m.Texto, m.FRegistro, u.NomUsuario AS Remitente
                  FROM mensajes m
                  JOIN usuarios u ON m.UsuOrigen = u.IdUsuario
                  WHERE m.UsuDestino = ?
                  ORDER BY m.FRegistro DESC";
$stmt_recibidos = $conexion->prepare($sql_recibidos);
$stmt_recibidos->bind_param("i", $usuarioId);
$stmt_recibidos->execute();
$result_recibidos = $stmt_recibidos->get_result();
$total_recibidos = $result_recibidos->num_rows;

// ==========================================================
// Cabecera
// ==========================================================
$encabezado = "MENSAJES DE ". htmlspecialchars($nombre_usuario_actual);
$style = "misMensajes.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<main>
    <aside class="panel-mensajes">
        <h3>Mensajes enviados (<?= $total_enviados ?>)</h3>
        <?php if ($total_enviados > 0): ?>
            <table class = "tabla-mensajes-enviados">
                <thead>
                    <tr>
                        <th>Texto</th>
                        <th>Fecha</th>
                        <th>Destinatario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($msg = $result_enviados->fetch_assoc()): ?>
                        <tr class="enviado">
                            <td><?= htmlspecialchars($msg['Texto']) ?></td>
                            <td><?= htmlspecialchars($msg['FRegistro']) ?></td>
                            <td><?= htmlspecialchars($msg['Destinatario']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay mensajes enviados.</p>
        <?php endif; ?>

        <h3>Mensajes recibidos (<?= $total_recibidos ?>)</h3>
        <?php if ($total_recibidos > 0): ?>
            <table class = "tabla-mensajes-recibidos">
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
            <p>No hay mensajes recibidos.</p>
        <?php endif; ?>
    </aside>

    <nav class="acciones">
        <a href="perfilUsuario.php" class="boton">Volver al perfil</a>
        <a href="mensaje.php" class="boton">Enviar un mensaje</a>
    </nav>
</main>

<?php
require_once("pie.php");
?>