<?php
session_start();
require_once("conexionBD.php");

// ==========================================================
// Cabecera
// ==========================================================
$encabezado = "ELIGE UN NUEVO ESTILO PARA LA PÁGINA";
$style = "perfil.css";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
require_once("cabecera.php");

// ==========================================================
// Obtener todos los estilos desde la BD
// ==========================================================
$sql = "SELECT IdEstilo, Nombre, Descripcion FROM estilos ORDER BY IdEstilo ASC";
$result = $conexion->query($sql);

?>
<main>
    <nav class="menu-perfil">
        <ul>
            <?php while ($fila = $result->fetch_assoc()): ?>
                <li>
                    <a href="cambiarEstilo.php?estilo=<?= urlencode($fila['Nombre']) ?>">
                        <?= htmlspecialchars($fila['Nombre']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </nav>
</main>

<?php
require_once("pie.php");
?>

