<?php
session_start();
require_once("conexionBD.php");

$encabezado = "BUSCANDO TU PRÓXIMA VIVIENDA...";
$style = "index.css";
$style2 = "formBusqueda.css";
$style3 = "coloresPredeterminados.css";

$filtrosUsados = [];
$resultados_filtrados = [];
$fechaValida = true;

// Inicializamos el array de condiciones para la consulta
$where = [];

/* ============================================================
   PROCESAR BÚSQUEDA RÁPIDA DESDE LA CABECERA
============================================================ */
$valorBusqueda = isset($_GET['bCiudad']) ? trim($_GET['bCiudad']) : '';

if (!empty($valorBusqueda)) {
    // Convertir a minúsculas y dividir palabras
    $terminos = explode(' ', mb_strtolower($valorBusqueda));

    $tiposAnuncioMap = ['alquiler' => 2, 'venta' => 1];
    $tiposViviendaMap = [
        'obra' => 1, 'vivienda' => 2, 'oficina' => 3,
        'local' => 4, 'garaje' => 5, 'apartamento' => 6,
        'estudio' => 7, 'casa' => 8
    ];

    $tipoAnuncio = '';
    $tipoVivienda = '';
    $ciudad = '';

    foreach ($terminos as $t) {
        if (isset($tiposAnuncioMap[$t])) {
            $tipoAnuncio = $t;
        } elseif (isset($tiposViviendaMap[$t])) {
            $tipoVivienda = $t;
        } elseif (!in_array($t, ['de', 'en', 'una', 'un', 'la', 'el'])) {
            // Cualquier palabra no reconocida la tratamos como ciudad
            $ciudad = $t;
        }
    }

    // Agregamos a los filtros 
    if ($tipoAnuncio) {
        $filtrosUsados['Tipo de Anuncio'] = ucfirst($tipoAnuncio);
        $where[] = "a.TAnuncio = " . intval($tiposAnuncioMap[$tipoAnuncio]);
    }

    if ($tipoVivienda) {
        $filtrosUsados['Tipo de Vivienda'] = ucfirst($tipoVivienda);
        $where[] = "a.TVivienda = " . intval($tiposViviendaMap[$tipoVivienda]);
    }

    if ($ciudad) {
        $filtrosUsados['Ciudad'] = ucfirst($ciudad);
        $where[] = "LOWER(a.Ciudad) LIKE LOWER('%" . mysqli_real_escape_string($conexion, $ciudad) . "%')";
    }
}

/* ============================================================
   CAPTURA DE FILTROS DEL FORMULARIO AVANZADO
============================================================ */
$tipoAnuncio = isset($_GET['tipoAnuncio']) ? trim($_GET['tipoAnuncio']) : '';
$tipoVivienda = isset($_GET['tipoVivienda']) ? trim($_GET['tipoVivienda']) : '';
$ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
$pais = isset($_GET['pais']) ? trim($_GET['pais']) : '';
$precioMax = isset($_GET['precio']) ? trim($_GET['precio']) : '';
$fechaPubli = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';

/* ============================================================
   VALIDACIÓN DE FECHA
============================================================ */
$valorFecha = '';
$fechaValida = true;
$fechaPubliDisplay = '';

if(!empty($fechaPubli)) {
    $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fechaPubli);
    if ($fechaObjeto && $fechaObjeto->format('Y-m-d') === $fechaPubli) {
        $valorFecha = $fechaPubli; 
        $fechaPubliDisplay = $fechaObjeto->format('d/m/Y');
    } else {
        $fechaValida = false;
    }
}

/* ============================================================
   APLICAR FILTROS AVANZADOS A LA CONSULTA
============================================================ */
if (!empty($tipoAnuncio)) {
    $mapTipoAnuncio = ['venta' => 1, 'alquiler' => 2];
    if (isset($mapTipoAnuncio[$tipoAnuncio])) {
        $where[] = "a.TAnuncio = " . intval($mapTipoAnuncio[$tipoAnuncio]);
        $filtrosUsados['Tipo de Anuncio'] = ucfirst($tipoAnuncio);
    }
}

if (!empty($tipoVivienda)) {
    $mapTipoVivienda = [
        'obraNueva' => 1, 'vivienda' => 2, 'oficina' => 3,
        'local' => 4, 'garaje' => 5, 'apartamento' => 6,
        'estudio' => 7, 'casaRural' => 8
    ];
    if (isset($mapTipoVivienda[$tipoVivienda])) {
        $where[] = "a.TVivienda = " . intval($mapTipoVivienda[$tipoVivienda]);
        $filtrosUsados['Tipo de Vivienda'] = ucfirst($tipoVivienda);
    }
}

if (!empty($ciudad)) {
    $where[] = "LOWER(a.Ciudad) LIKE LOWER('%" . mysqli_real_escape_string($conexion, $ciudad) . "%')";
    $filtrosUsados['Ciudad'] = ucfirst($ciudad);
}

if (!empty($pais)) {
    $where[] = "LOWER(p.NomPais) LIKE LOWER('%" . mysqli_real_escape_string($conexion, $pais) . "%')";
    $filtrosUsados['País'] = ucfirst($pais);
}

if (!empty($precioMax) && is_numeric($precioMax)) {
    $where[] = "a.Precio <= " . floatval($precioMax);
    $filtrosUsados['Precio Máximo'] = $precioMax . " €";
}

if (!empty($valorFecha) && $fechaValida) {
    $where[] = "DATE(a.FRegistro) >= '" . mysqli_real_escape_string($conexion, $valorFecha) . "'";
    $filtrosUsados['Fecha desde'] = date('d/m/Y', strtotime($valorFecha));
}

/* ============================================================
   CONSULTA PRINCIPAL
============================================================ */
$sql = "SELECT 
            a.IdAnuncio, a.Titulo, a.Texto, a.FPrincipal, 
            a.Precio, a.Ciudad, p.NomPais AS Pais,
            ta.NomTAnuncio AS TipoAnuncio,
            tv.NomTVivienda AS TipoVivienda,
            DATE_FORMAT(a.FRegistro, '%d/%m/%Y') AS Fecha
        FROM anuncios a
        JOIN paises p ON a.Pais = p.IdPais
        JOIN tiposanuncios ta ON a.TAnuncio = ta.IdTAnuncio
        JOIN tiposviviendas tv ON a.TVivienda = tv.IdTVivienda";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY a.FRegistro DESC";

$result = mysqli_query($conexion, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

$resultados_filtrados = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $resultados_filtrados[] = [
        'id' => $fila['IdAnuncio'],
        'titulo' => $fila['Titulo'],
        'tipo' => $fila['TipoAnuncio'] . " / " . $fila['TipoVivienda'],
        'fecha' => $fila['Fecha'],
        'ciudad' => $fila['Ciudad'],
        'pais' => $fila['Pais'],
        'precio' => $fila['Precio'] . " €",
        'texto' => $fila['Texto'],
        'foto' => $fila['FPrincipal']
    ];
}

/* ============================================================
   CARGA DE DATOS PARA LOS SELECTS
============================================================ */
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

$queryTiposAnuncio = "SELECT IdTAnuncio, NomTAnuncio FROM tiposanuncios ORDER BY IdTAnuncio";
$resTiposAnuncio = mysqli_query($conexion, $queryTiposAnuncio);
$tiposAnuncio = mysqli_fetch_all($resTiposAnuncio, MYSQLI_ASSOC);
mysqli_free_result($resTiposAnuncio);

$queryTiposVivienda = "SELECT IdTVivienda, NomTVivienda FROM tiposviviendas ORDER BY IdTVivienda";
$resTiposVivienda = mysqli_query($conexion, $queryTiposVivienda);
$tiposVivienda = mysqli_fetch_all($resTiposVivienda, MYSQLI_ASSOC);
mysqli_free_result($resTiposVivienda);

$queryPaises = "SELECT IdPais, NomPais FROM paises ORDER BY NomPais";
$resPaises = mysqli_query($conexion, $queryPaises);
$paises = mysqli_fetch_all($resPaises, MYSQLI_ASSOC);
mysqli_free_result($resPaises);

require_once("cabecera.php");
?>


<main>
        <aside class ="filtrado">
        <h2>Introduce los datos de búsqueda</h2>
            <form action="formularioBusqueda.php" method="get">
                <fieldset>
                    <legend>Filtros de búsqueda</legend>
                    
                
                    <label for="tipoAnuncio">Tipo de anuncio:</label>
                        <select id="tipoAnuncio" name="tipoAnuncio">
                            <option value="">--Selecciona--</option>
                            <?php foreach ($tiposAnuncio as $tipo): ?>
                                <?php 
                                    $valor = strtolower($tipo['NomTAnuncio']); 
                                ?>
                                <option value="<?php echo $valor; ?>" <?php if($tipoAnuncio === $valor) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($tipo['NomTAnuncio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>


                    <label for="tipoVivienda">Tipo de vivienda:</label>
                        <select id="tipoVivienda" name="tipoVivienda">
                            <option value="">--Selecciona--</option>
                            <?php foreach ($tiposVivienda as $tipo): ?>
                                <?php 
                                    
                                    $valor = preg_replace('/\s+/', '', lcfirst($tipo['NomTVivienda']));
                                ?>
                                <option value="<?php echo $valor; ?>" <?php if($tipoVivienda === $valor) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($tipo['NomTVivienda']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" id="ciudad" name="ciudad" placeholder="Ej: Madrid" value = "<?php echo htmlspecialchars($ciudad); ?>">
                    

                    <label for="pais">País:</label>
                    <select id="pais" name="pais">
                        <option value="">--Selecciona--</option>
                        <?php foreach ($paises as $paisItem): ?>
                            <option value="<?php echo htmlspecialchars($paisItem['NomPais']); ?>" 
                                <?php if (strcasecmp($pais, $paisItem['NomPais']) == 0) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
             
                    <label for="precio">Precio máximo (€):</label>
                    <input type="number" id="precio" name="precio" value = "<?php echo $precioMax; ?>">
                    

                    <label for="fecha">Fecha de publicación:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fechaPubli); ?>">

                    <nav>
                        <button type="submit">Buscar</button>
                        <button type="reset">Cancelar</button>
                    </nav>

                </fieldset>
            </form>
        </aside>
        
        <aside class="res">
        <h2>RESULTADOS DE LA BÚSQUEDA</h2>
        <aside class="contenedor_resultados">
            
            <article class="resultadoFiltros">
                <?php    
                    if (count($filtrosUsados) > 0) {
                        echo "<h3>Filtros aplicados:</h3>";
                        echo "<ul>";
                        foreach ($filtrosUsados as $filtro => $valor) {
                            echo "<li><strong>$filtro:</strong> $valor</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No se han aplicado filtros de búsqueda. Mostrando todos los anuncios disponibles.</p>";
                    }
                ?>
            </article>

            <?php if (count($resultados_filtrados) > 0): ?>
                <?php foreach ($resultados_filtrados as $anuncio): ?>
                    <?php $enlace_detalle = "detalleAnuncio.php?id=" . urlencode($anuncio['id']); ?>
                    <article class="resultados">
                        <a href="<?php echo htmlspecialchars($enlace_detalle); ?>" class="anuncio-enlace">
                            <img src="fotos_anuncios/<?php echo htmlspecialchars($anuncio['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($anuncio['titulo']); ?>">
                            <h3><?php echo htmlspecialchars($anuncio['titulo']); ?></h3>
                        </a>
                        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($anuncio['tipo']); ?></p>
                        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($anuncio['fecha']); ?></p>
                        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($anuncio['ciudad']); ?> (<?php echo htmlspecialchars($anuncio['pais']); ?>)</p>
                        <p><strong>Precio:</strong> <?php echo htmlspecialchars($anuncio['precio']); ?></p>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($anuncio['texto']); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">No se encontraron anuncios que coincidan con los filtros aplicados.</p>
            <?php endif; ?>
        </aside>

        <a class="volverAtras" href="index.php">Volver atrás</a>
    </aside>
    
</main>


<?php 
mysqli_free_result($result);
mysqli_close($conexion);
require_once("pie.php");
?>