<?php
require_once(__DIR__ . "/conexionBD.php");
require_once("controlSesion.php");

$encabezado = "ENCUENTRA TU HOGAR AL MEJOR PRECIO";
$style = "detAnunEstilo.css";
$style2 = "coloresPredeterminados.css";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
require_once("cabecera.php");
?>

<main>
<?php
// ======================================================
// OBTENER ID DEL ANUNCIO DESDE GET
// ======================================================
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: error404.php");
    exit();
}

// ======================================================
// GUARDAR COOKIE “ÚLTIMOS ANUNCIOS VISITADOS”
// ======================================================
$ultimos = [];
if (isset($_COOKIE['ultimos_anuncios'])) {
    $ultimos = json_decode($_COOKIE['ultimos_anuncios'], true) ?? [];
}
$ultimos = array_diff($ultimos, [$id]);
array_unshift($ultimos, $id);
$ultimos = array_slice($ultimos, 0, 4);
setcookie('ultimos_anuncios', json_encode($ultimos), time() + (7 * 24 * 60 * 60), "/", "", false, true);

// ======================================================
// CONSULTAR DATOS DEL ANUNCIO
// ======================================================
$sql = "SELECT 
            a.IdAnuncio, a.Titulo, a.Texto, a.FRegistro AS Fecha, 
            a.Ciudad, p.NomPais AS Pais, a.Precio, a.FPrincipal AS FotoPrincipal,
            a.Superficie, a.NHabitaciones, a.NBanyos, a.Planta, a.Anyo, a.Extras,
            ta.NomTAnuncio AS TipoAnuncio, tv.NomTVivienda AS TipoVivienda,
            u.IdUsuario AS IdUsuario, u.NomUsuario AS Usuario, u.Foto AS FotoUsuario
        FROM Anuncios a
        JOIN TiposAnuncios ta ON a.TAnuncio = ta.IdTAnuncio
        JOIN TiposViviendas tv ON a.TVivienda = tv.IdTVivienda
        JOIN Paises p ON a.Pais = p.IdPais
        JOIN Usuarios u ON a.Usuario = u.IdUsuario
        WHERE a.IdAnuncio = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: error404.php");
    exit();
}

$anuncio = $resultado->fetch_assoc();
$stmt->close();

// ======================================================
// CONSULTAR GALERÍA DE FOTOS (TABLA FOTOS)
// ======================================================
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

$extras_delimitador = " | "; // El delimitador usado en respCrearAnuncio.php
$extras_lista = explode($extras_delimitador, $anuncio['Extras'] ?? '');
$mostrar_extras = array_filter($extras_lista); // Elimina elementos vacíos si no hay extras o si hay un delimitador final
?>

    
    <h2><?= htmlspecialchars($anuncio['TipoAnuncio']) ?> - <?= htmlspecialchars($anuncio['TipoVivienda']) ?></h2>
    <aside class="contenedor">
        
        <figure>
            <img src="fotos_anuncios/<?= htmlspecialchars($anuncio['FotoPrincipal']) ?>" alt="Foto principal" width="500">
        </figure>

        <section class="datos">
            <h3><?= htmlspecialchars($anuncio['Titulo']) ?></h3>
            <ul>
                <li><?= htmlspecialchars($anuncio['Texto']) ?></li>
                <li><strong>Fecha de publicación:</strong> <?= htmlspecialchars($anuncio['Fecha']) ?></li>
                <li><strong>Ciudad:</strong> <?= htmlspecialchars($anuncio['Ciudad']) ?></li>
                <li><strong>País:</strong> <?= htmlspecialchars($anuncio['Pais']) ?></li>
                <li><strong>Precio:</strong> <?= htmlspecialchars($anuncio['Precio']) ?> €</li>
                <li>
                    <strong>Publicado por:</strong>  
                    <a href="perfilPublico.php?id=<?= urlencode($anuncio['IdUsuario']) ?>&anuncio=<?= urlencode($anuncio['IdAnuncio']) ?>">
                        <?= htmlspecialchars($anuncio['Usuario']) ?>
                    </a>
                </li>
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

    <p><a class="contactar" href="mensaje.php?id=<?= $id ?>">Contactar con el anunciante</a></p>
    <p><a class="verFotos" href="verFotos.php?id=<?= $id ?>">Ver fotos del anuncio</a></p>
    <a class="atras" href="formularioBusqueda.php">Volver atrás</a>
</main>

<?php
require_once("pie.php");
?>
