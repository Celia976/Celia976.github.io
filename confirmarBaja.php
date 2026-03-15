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

$usuarioId = $_SESSION['usuario_id'];

// ==========================================================
// Obtener información del usuario y anuncios
// ==========================================================
$sqlUsuario = "SELECT NomUsuario, Clave FROM usuarios WHERE IdUsuario = ?";
$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $usuarioId);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
$stmtUsuario->close();

$sqlAnuncios = "SELECT a.IdAnuncio, a.Titulo, 
                (SELECT COUNT(*) FROM fotos WHERE Anuncio = a.IdAnuncio) AS num_fotos
                FROM anuncios a
                WHERE a.Usuario = ?";
$stmtAnuncios = $conexion->prepare($sqlAnuncios);
$stmtAnuncios->bind_param("i", $usuarioId);
$stmtAnuncios->execute();
$resultAnuncios = $stmtAnuncios->get_result();

// Totales
$totalAnuncios = $resultAnuncios->num_rows;
$totalFotos = 0;
$anuncios = [];
while ($fila = $resultAnuncios->fetch_assoc()) {
    $totalFotos += $fila['num_fotos'];
    $anuncios[] = $fila;
}
$stmtAnuncios->close();

// ==========================================================
// Cabecera
// ==========================================================
$encabezado = "CONFIRMACIÓN DE BAJA";
$style = "perfil.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<main>
<section class="confirmar-baja">
    <div class = "tit-confirmar-baja">
        <h2>Confirmar baja de usuario: <?= htmlspecialchars($usuario['NomUsuario']) ?></h2>
        <p>Antes de continuar, revisa tus datos:</p>
    </div>

    
    <h3>Resumen de anuncios</h3>
    <div class= "resAnun">
        <?php if ($totalAnuncios > 0): ?>
            <ul>
                <?php foreach ($anuncios as $anuncio): ?>
                    <li>
                        <strong><?= htmlspecialchars($anuncio['Titulo']) ?></strong> - Fotos: <?= $anuncio['num_fotos'] ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p>Total anuncios: <?= $totalAnuncios ?>, total fotos: <?= $totalFotos ?></p>
        <?php else: ?>
            <p>No tienes anuncios publicados.</p>
        <?php endif; ?>
    </div>

    <!-- Formulario de confirmación de baja -->
    <form action="bajaUsuario.php" method="post" class= "formBaja">
        <p>Para confirmar la baja, introduce tu contraseña actual:</p>
        <p>
            <label for="contrasenya">Contraseña:</label>
            <input type="password" name="contrasenya" id="contrasenya" required>
        </p>
        <button type="submit" class="boton">Confirmar baja</button>
        <a href="perfilUsuario.php" class="boton">Cancelar</a>
    </form>
</section>
</main>

