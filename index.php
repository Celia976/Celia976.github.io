<?php
session_start();

require_once("conexionBD.php");
require_once("gestionFicheros.php");
require_once("consejos.php");
$consejo_aleatorio = obtenerConsejoAleatorio();

$encabezado = "ENCUENTRA TU HOGAR AL MEJOR PRECIO";
$style = "index.css";
$style2 = "coloresPredeterminados.css";

$logueado = (isset($_SESSION['login']) && $_SESSION['login'] === 'ok') || isset($_COOKIE['usuario_recordado']);
$mensaje_bienvenida = "";

/* ============================================================
   COOKIES “RECORDARME EN ESTE EQUIPO”
============================================================ */
if (isset($_COOKIE['usuario_recordado'])) {
    $_SESSION['usuario'] = $_COOKIE['usuario_recordado'];
    $_SESSION['login'] = 'ok';

    $ultima_visita = $_COOKIE['ultima_visita'] ?? null;

    if ($ultima_visita) {
        $fecha_formateada = date("d/m/Y", strtotime($ultima_visita));
        $hora_formateada = date("H:i", strtotime($ultima_visita));

        $mensaje_bienvenida = "<p class='saludo-usuario2'>
            Tu última visita fue el &nbsp;<strong>{$fecha_formateada}</strong> &nbsp; a las  &nbsp;<strong>{$hora_formateada}</strong>.
        </p>";
    }

    // Actualizar cookie de última visita
    setcookie('ultima_visita', date('Y-m-d H:i:s'), time() + (90 * 24 * 60 * 60), "/", "", false, true);
}

/* ============================================================
   DETECTAR CAMBIO O AUSENCIA DE USUARIO → VACIAR PANEL
============================================================ */
$usuario_actual = ($logueado && isset($_SESSION['usuario'])) ? $_SESSION['usuario'] : 'anonimo';
$usuario_anterior = $_COOKIE['usuario_actual'] ?? null;

if ($usuario_anterior !== $usuario_actual || !$logueado) {
    setcookie('usuario_actual', $usuario_actual, time() + (7 * 24 * 60 * 60), "/", "", false, true);
}

/* ============================================================
   SALUDO PERSONALIZADO
============================================================ */
$hora = date("H");
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 16) {
    $saludo = "Hola";
} elseif ($hora >= 16 && $hora < 20) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}

require_once("cabecera.php");

/* ============================================================
   OBTENER LOS 5 ÚLTIMOS ANUNCIOS
============================================================ */
$anuncios = [];

$consulta_cinco = "SELECT 
                        a.IdAnuncio, 
                        a.Titulo, 
                        a.FRegistro, 
                        a.Ciudad, 
                        a.Precio, 
                        a.FPrincipal AS Foto, 
                        a.Texto, 
                        p.NomPais AS PaisNombre 
                    FROM ANUNCIOS a
                    JOIN PAISES p ON a.Pais = p.IdPais
                    ORDER BY a.FRegistro DESC 
                    LIMIT 5";

if ($resultado = mysqli_query($conexion, $consulta_cinco)) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $fila['FechaRegistroFormato'] = date("d/m/Y", strtotime($fila['FRegistro']));
        $anuncios[] = $fila;
    }
    mysqli_free_result($resultado);
} else {
    echo "<p class='error-bd'>Error al obtener anuncios: " . mysqli_error($conexion) . "</p>";
}

/* ============================================================
   ANUNCIOS ESCOGIDOS POR EXPERTOS (FICHERO)
============================================================ */
$anuncios_expertos_raw = obtenerAnunciosEscogidos('anuncios_escogidos.txt');
$anuncios_expertos_db = [];

if (!empty($anuncios_expertos_raw)) {
    $ids_expertos = [];
    $comentarios_expertos = [];

    // 1. Recoger IDs y comentarios para consulta y mapeo
    foreach ($anuncios_expertos_raw as $item) {
        $ids_expertos[] = (int)$item['IdAnuncio'];
        $comentarios_expertos[$item['IdAnuncio']] = [
            'Experto' => $item['Experto'],
            'ComentarioExperto' => $item['Comentario']
        ];
    }

    if (!empty($ids_expertos)) {
        // 2. Consultar la BD para obtener los detalles de los anuncios
        $ids_sql_expertos = implode(',', $ids_expertos);
        $consulta_expertos = "SELECT 
                                a.IdAnuncio, 
                                a.Titulo, 
                                a.FPrincipal AS Foto, 
                                a.Alternativo, 
                                a.Precio, 
                                p.NomPais AS PaisNombre 
                              FROM ANUNCIOS a
                              JOIN PAISES p ON a.Pais = p.IdPais
                              WHERE a.IdAnuncio IN ($ids_sql_expertos)
                              ORDER BY FIELD(a.IdAnuncio, $ids_sql_expertos)"; // Mantiene el orden del fichero

        if ($resultado_expertos = mysqli_query($conexion, $consulta_expertos)) {
            while ($fila = mysqli_fetch_assoc($resultado_expertos)) {
                $id = $fila['IdAnuncio'];
                if (isset($comentarios_expertos[$id])) {
                    $anuncios_expertos_db[] = array_merge($fila, $comentarios_expertos[$id]);
                }
            }
            mysqli_free_result($resultado_expertos);
        } else {
            echo "<p class='error-bd'>Error al obtener anuncios de expertos: " . mysqli_error($conexion) . "</p>";
        }
    }
}

// 3. Seleccionar un anuncio aleatorio de expertos
$anuncio_aleatorio = !empty($anuncios_expertos_db) ? $anuncios_expertos_db[array_rand($anuncios_expertos_db)] : null;



/* ============================================================
   PANEL LATERAL "ÚLTIMOS ANUNCIOS VISITADOS"
============================================================ */
$anuncios_visitados = []; 

if (isset($_COOKIE['ultimos_anuncios'])) {
    $ultimos = json_decode($_COOKIE['ultimos_anuncios'], true);

    if ($ultimos && is_array($ultimos) && count($ultimos) > 0) {
        $ids_filtrados = array_map('intval', array_filter($ultimos, 'is_numeric'));
        
        if (!empty($ids_filtrados)) {
            $ids_sql = implode(',', $ids_filtrados);

            $consulta_panel = "SELECT 
                                a.IdAnuncio, 
                                a.Titulo, 
                                a.Ciudad, 
                                a.Precio, 
                                a.FPrincipal AS Foto, 
                                p.NomPais AS PaisNombre 
                            FROM ANUNCIOS a
                            JOIN PAISES p ON a.Pais = p.IdPais 
                            WHERE a.IdAnuncio IN ($ids_sql)";
            
            if ($resultado_panel = mysqli_query($conexion, $consulta_panel)) {
                while ($fila_panel = mysqli_fetch_assoc($resultado_panel)) {
                    $anuncios_visitados[$fila_panel['IdAnuncio']] = $fila_panel;
                }
                mysqli_free_result($resultado_panel);
            } else {
                echo "<p class='error-bd'>Error al obtener anuncios visitados: " . mysqli_error($conexion) . "</p>";
            }
        }
    }
}

/* ============================================================
   MOSTRAR SALUDO SI ESTÁ LOGUEADO
============================================================ */
if ($logueado && isset($_SESSION['usuario'])) {
    echo "<p class='saludo-usuario'>
            {$saludo},&nbsp; <strong>{$_SESSION['usuario']}</strong>
          </p>";

    if ($mensaje_bienvenida) {
        echo $mensaje_bienvenida;
    }
}

require_once("panelAnuncios.php");
?>

<main>
    
<!--SECCIÓN DE ANUNCIOS DESTACADOS-->
    <section class="anuncios-expertos">
        <h2>SELECCIÓN DE EXPERTOS</h2>

        <aside class="contenedor_anuncios">
            <?php if ($anuncio_aleatorio): 
                $id_anuncio = $anuncio_aleatorio['IdAnuncio'];
                $url_destino = $logueado ? "detalleAnuncio.php?id=" . $id_anuncio : "error.php?id=" . $id_anuncio;
                
                // Asegurar que solo usamos el nombre del archivo
                $foto_experto = basename($anuncio_aleatorio['Foto'] ?? 'default.png');
            ?>
                <article class="anuncio-experto-destacado">
                    <a href="<?= htmlspecialchars($url_destino) ?>" class="anuncio-enlace">
                        <img src="fotos_anuncios/<?= htmlspecialchars($foto_experto) ?>" 
                             alt="<?= htmlspecialchars($anuncio_aleatorio['Alternativo'] ?? 'Imagen del anuncio') ?>">
                        <h3><?= htmlspecialchars($anuncio_aleatorio['Titulo']) ?></h3>
                    </a>
                    <p class="expert-info">
                        <strong>Comentario del Experto <?= htmlspecialchars($anuncio_aleatorio['Experto']) ?>:</strong>
                    </p>
                    <blockquote class="expert-quote">
                        "<?= htmlspecialchars($anuncio_aleatorio['ComentarioExperto']) ?> "
                    </blockquote>
                    <p><strong>Precio:</strong> <?= htmlspecialchars($anuncio_aleatorio['Precio']) ?> €</p>
                    <p><strong>Ubicación:</strong> <?= htmlspecialchars($anuncio_aleatorio['PaisNombre']) ?></p>
                </article>
            <?php else: ?>
                <p>En este momento, no hay anuncios destacados por nuestros expertos.</p>
            <?php endif; ?>
        </aside>
    </section>

<!--SECCIÓN DE CONSEJOS DE COMPRA/VENTA-->
    <section class="anuncios-expertos">
        <h2>CONSEJO DE COMPRA/VENTA</h2>

        <?php if ($consejo_aleatorio): ?>
        <article class="contenedor_anuncios">
            <p><strong>Categoría:</strong> <?= htmlspecialchars($consejo_aleatorio['categoria']) ?></p>
            <p><strong>Importancia:</strong> <?= htmlspecialchars($consejo_aleatorio['importancia']) ?></p>
            <p class="descripcion-consejo">
                <?= htmlspecialchars($consejo_aleatorio['descripcion']) ?>
            </p>
        </article>
        <?php else: ?>
            <p>No hay consejos disponibles en este momento.</p>
        <?php endif; ?>
    </section>

    
<!--SECCIÓN DE ÚLTIMOS ANUNCIOS-->
    <h2>ANUNCIOS RECIENTES</h2>
    
    <?php if ($logueado): ?>
        <a class="nuevoAnuncio" href="crearAnuncio.php">Publicar Nuevo Anuncio</a>
    <?php endif; ?>
    
    <aside class="Cincoanuncios">
        <aside class="contenedor_anuncios">
            <?php if (empty($anuncios)): ?>
                <p>No hay anuncios disponibles en este momento.</p>
            <?php else: 
                foreach ($anuncios as $anuncio): 
                    $id_anuncio = $anuncio['IdAnuncio'];
                    $url_destino = $logueado ? "detalleAnuncio.php?id=" . $id_anuncio : "error.php?id=" . $id_anuncio;
                    
                    // Normalizar la ruta de la imagen
                    $foto_anuncio = basename($anuncio['Foto']);
            ?>
                <article class="anuncio">
                    <a href="<?= htmlspecialchars($url_destino) ?>" class="anuncio-enlace">
                        <img src="fotos_anuncios/<?= htmlspecialchars($foto_anuncio) ?>" 
                             alt="Imagen de <?= htmlspecialchars($anuncio['Titulo']) ?>">
                        <h3><?= htmlspecialchars($anuncio['Titulo']) ?></h3>
                    </a>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($anuncio['FechaRegistroFormato']) ?></p>
                    <p><strong>Ciudad:</strong> <?= htmlspecialchars($anuncio['Ciudad']) ?></p>
                    <p><strong>País:</strong> <?= htmlspecialchars($anuncio['PaisNombre']) ?></p>
                    <p><strong>Precio:</strong> <?= htmlspecialchars($anuncio['Precio']) ?> €</p>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($anuncio['Texto']) ?></p>
                </article>
            <?php endforeach; endif; ?>
        </aside>
    </aside>
</main>



<?php
if (isset($conexion)) {
    mysqli_close($conexion);
}
require_once("pie.php");
?>
