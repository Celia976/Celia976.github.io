<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ==========================
// CONTROL DE SESIÓN
// ==========================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$usuario_logueado_id = $_SESSION['usuario_id'] ?? null;

if (!$logueado) {
    header("Location: formularioAcceso.php");
    exit();
}

$encabezado = "DETALLE DE MI ANUNCIO";
$style = "detAnunEstilo.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");

// ==========================
// OBTENER ID DEL ANUNCIO
// ==========================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: misAnuncios.php");
    exit();
}

// ==========================
// CONSULTAR EL ANUNCIO (solo si pertenece al usuario actual)
// ==========================
$sql = "SELECT 
            a.IdAnuncio, a.Titulo, a.Texto, a.FRegistro AS Fecha, 
            a.Ciudad, p.NomPais AS Pais, a.Precio, a.FPrincipal AS FotoPrincipal,
            a.Superficie, a.NHabitaciones, a.NBanyos, a.Planta, a.Anyo, a.Extras,
            ta.NomTAnuncio AS TipoAnuncio, tv.NomTVivienda AS TipoVivienda,
            u.NomUsuario AS Usuario, u.Foto AS FotoUsuario, a.Usuario AS IdUsuarioPropietario
        FROM Anuncios a
        JOIN TiposAnuncios ta ON a.TAnuncio = ta.IdTAnuncio
        JOIN TiposViviendas tv ON a.TVivienda = tv.IdTVivienda
        JOIN Paises p ON a.Pais = p.IdPais
        JOIN Usuarios u ON a.Usuario = u.IdUsuario
        WHERE a.IdAnuncio = ? AND a.Usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id, $usuario_logueado_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // No existe o no pertenece al usuario
    header("Location: misAnuncios.php");
    exit();
}

$anuncio = $resultado->fetch_assoc();
$stmt->close();

// ==========================
// CONSULTAR FOTOS ADICIONALES
// ==========================
$sqlFotos = "SELECT Foto, Titulo FROM Fotos WHERE Anuncio = ?";
$stmtFotos = $conexion->prepare($sqlFotos);
$stmtFotos->bind_param("i", $id);
$stmtFotos->execute();
$resFotos = $stmtFotos->get_result();

$fotos = [];
while ($fila = $resFotos->fetch_assoc()) {
    $fotos[] = $fila;
}

$stmtFotos->close();
mysqli_close($conexion);

$extras_delimitador = " | ";
$extras_lista = explode($extras_delimitador, $anuncio['Extras'] ?? '');
$mostrar_extras = array_filter($extras_lista); // elimina vacíos

// ==========================
// MOSTRAR INFORMACIÓN
// ==========================
?>

<main>
    <h2><?= htmlspecialchars($anuncio['TipoAnuncio']) ?> - <?= htmlspecialchars($anuncio['TipoVivienda']) ?></h2>
    <aside class="contenedor">
        
        <figure>
            <img src="fotos_anuncios/<?= htmlspecialchars($anuncio['FotoPrincipal']) ?>" alt="Foto principal" width="500">
        </figure>


        <section class="datos">
            <h3><?= htmlspecialchars($anuncio['Titulo']) ?></h3>
            <ul>
                <li><?= nl2br(htmlspecialchars($anuncio['Texto'])) ?></li>
                <li><strong>Fecha de publicación:</strong> <?= htmlspecialchars($anuncio['Fecha']) ?></li>
                <li><strong>Ciudad:</strong> <?= htmlspecialchars($anuncio['Ciudad']) ?></li>
                <li><strong>País:</strong> <?= htmlspecialchars($anuncio['Pais']) ?></li>
                <li><strong>Precio:</strong> <?= htmlspecialchars($anuncio['Precio']) ?> €</li>
                <li><strong>Publicado por:</strong> Tú (<?= htmlspecialchars($anuncio['Usuario']) ?>)</li>
            </ul>
        </section>

        <section class="caracteristicas">
            <h3>Características</h3>
            <ul>
                <li><strong>Superficie:</strong> <?= htmlspecialchars($anuncio['Superficie']) ?> m²</li>
                <li><strong>Habitaciones:</strong> <?= htmlspecialchars($anuncio['NHabitaciones']) ?></li>
                <li><strong>Baños:</strong> <?= htmlspecialchars($anuncio['NBanyos']) ?></li>
                <li><strong>Planta:</strong> <?= htmlspecialchars($anuncio['Planta']) ?></li>
                <li><strong>Año de construcción:</strong> <?= htmlspecialchars($anuncio['Anyo']) ?></li>
            </ul>
        </section>
        
         <section class="caracteristicas">
            <h3>Extras</h3>
            <ul>
                <?php foreach ($mostrar_extras as $extra): ?>
                    <li><?= htmlspecialchars($extra) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <?php if (!empty($fotos)): ?>
        <section class="miniaturas">
            <h3>Galería de fotos</h3>
            <?php foreach ($fotos as $foto): ?>
                <a href="fotos_anuncios/<?= htmlspecialchars($foto['Foto']) ?>">
                    <img src="fotos_anuncios/<?= htmlspecialchars($foto['Foto']) ?>" alt="<?= htmlspecialchars($foto['Titulo']) ?>" width="100">
                </a>
            <?php endforeach; ?>

        </section>
        <?php endif; ?>
    </aside>

    <p><a class="ver Fotos" href="verFotosUsuario.php?id=<?= $id ?>">Ver fotos del anuncio</a></p>
    
    <p><a href="anadirFotoAnuncio.php?id=<?= $id ?>" class="btn">Añadir nueva foto</a></p>
    
    <p><a class="mensajesAnuncio" href="mensajesAnuncio.php?id=<?= $id ?>">Ver mensajes del anuncio</a></p>
    
    <p><a href="modificarAnuncio.php?id=<?= $id ?>" class="btn">Modificar anuncio</a></p>

    <p><a class="eliminar anunc" href="respEliminarAnuncio.php?id=<?= $id ?>">Eliminar Anuncio</a></p>

    <a class="atras" href="misAnuncios.php">Volver a mis anuncios</a>
</main>

<?php
require_once("pie.php");
?>