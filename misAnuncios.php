<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// Comprobamos si está logueado
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
if (!$logueado) {
    header("Location: formularioAcceso.php");
    exit();
}

$nombre_usuario_actual = $_SESSION['usuario'];
$idUsuario = $_SESSION['usuario_id'] ?? 0;

$encabezado = "Anuncios de " . htmlspecialchars($nombre_usuario_actual);
$style = "index.css";
$style2 = "coloresPredeterminados.css";

require_once("cabecera.php");

// =======================
// OBTENER ANUNCIOS DEL USUARIO CON NOMBRE DE PAÍS
// =======================
$sql = "SELECT a.IdAnuncio, a.Titulo, a.FPrincipal, a.Precio, a.Ciudad, p.NomPais AS Pais, a.FRegistro
        FROM Anuncios a
        JOIN Paises p ON a.Pais = p.IdPais
        WHERE a.Usuario = ?
        ORDER BY a.FRegistro DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();

$anunciosUsuario = [];
while ($fila = $resultado->fetch_assoc()) {
    $anunciosUsuario[] = $fila;
}

$stmt->close();
$conexion->close();
?>

<main>
    <aside class="misAnuncios">
        <h2>Mis anuncios</h2>
        <aside class="contenedor_anuncios">
            <?php if (empty($anunciosUsuario)): ?>
                <p>No tienes anuncios publicados actualmente.</p>
            <?php else: ?>
                <?php foreach ($anunciosUsuario as $anuncio): ?>
                    <article class="anuncio">
                        <a href="verAnuncio.php?id=<?= htmlspecialchars($anuncio['IdAnuncio']) ?>" class="anuncio-enlace">
                            <img src="fotos_anuncios/<?= htmlspecialchars($anuncio['FPrincipal'])?>" 
                                 alt="Imagen de <?= htmlspecialchars($anuncio['Titulo']) ?>">
                            <h3><?= htmlspecialchars($anuncio['Titulo']) ?></h3>
                        </a>
                        <p><strong>Fecha:</strong> <?= htmlspecialchars($anuncio['FRegistro']) ?></p>
                        <p><strong>Ciudad:</strong> <?= htmlspecialchars($anuncio['Ciudad']) ?></p>
                        <p><strong>Precio:</strong> <?= htmlspecialchars($anuncio['Precio']) ?> €</p>
                        <p><strong>País:</strong> <?= htmlspecialchars($anuncio['Pais']) ?></p>

                        <a class="botonAñadirFoto" href="anadirFotoAnuncio.php?id=<?= urlencode($anuncio['IdAnuncio']) ?>">
                            Añadir nueva foto al anuncio
                        </a>
                        <a class="botonAñadirFoto" href="crearAnuncio.php">Crear un nuevo anuncio</a>
                            
                        <a class="botonAñadirFoto" href="RespEliminarAnuncio.php?id=<?= urlencode($anuncio['IdAnuncio']) ?>">
                            Eliminar Anuncio
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>
    </aside>
</main>

<?php
require_once("pie.php");
?>
