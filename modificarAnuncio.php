<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ==============================
// Comprobación de sesión
// ==============================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
if (!$logueado) {
    header("Location: inicio.php"); 
    exit;
}

// ======================================================
// 1. OBTENER ID DEL ANUNCIO Y DATOS A MODIFICAR
// ======================================================
$anuncioId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idUsuarioLogueado = $_SESSION['usuario_id'] ?? null;

if (!$anuncioId) {
    header("Location: perfilUsuario.php?error=" . urlencode("ID de anuncio no especificado."));
    exit;
}

// Consulta para obtener los datos actuales del anuncio
$sqlAnuncio = "SELECT * FROM Anuncios WHERE IdAnuncio = ? AND Usuario = ?";
$stmtAnuncio = $conexion->prepare($sqlAnuncio);

if (!$stmtAnuncio) {
    die("Error de preparación de consulta: " . $conexion->error);
}

$stmtAnuncio->bind_param("ii", $anuncioId, $idUsuarioLogueado);
$stmtAnuncio->execute();
$resAnuncio = $stmtAnuncio->get_result();

if ($resAnuncio->num_rows === 0) {
    // Si no existe o no pertenece al usuario logueado
    $stmtAnuncio->close();
    header("Location: perfilUsuario.php?error=" . urlencode("Anuncio no encontrado o no autorizado para modificar."));
    exit;
}

$anuncioActual = $resAnuncio->fetch_assoc();
$stmtAnuncio->close();

// ============================
// Convertir los extras guardados en array
// ============================
$extrasAnuncio = [];
if (!empty($anuncioActual['Extras'])) {
    $extrasAnuncio = array_map('trim', explode('|', $anuncioActual['Extras']));
}

// ======================================================
// 2. CONSULTAS A LA BASE DE DATOS PARA SELECTS
// ======================================================

// --- Obtener Tipos de Anuncios ---
$sqlTAnuncio = "SELECT IdTAnuncio, NomTAnuncio FROM TiposAnuncios ORDER BY NomTAnuncio";
$resTAnuncio = $conexion->query($sqlTAnuncio);
$tiposAnuncios = [];
if ($resTAnuncio) {
    while ($fila = $resTAnuncio->fetch_assoc()) {
        $tiposAnuncios[] = $fila;
    }
}

// --- Obtener Tipos de Viviendas ---
$sqlTVivienda = "SELECT IdTVivienda, NomTVivienda FROM TiposViviendas ORDER BY NomTVivienda";
$resTVivienda = $conexion->query($sqlTVivienda);
$tiposViviendas = [];
if ($resTVivienda) {
    while ($fila = $resTVivienda->fetch_assoc()) {
        $tiposViviendas[] = $fila;
    }
}

// --- Obtener Países ---
$sqlPaises = "SELECT IdPais, NomPais FROM Paises ORDER BY NomPais";
$resPaises = $conexion->query($sqlPaises);
$paises = [];
if ($resPaises) {
    while ($fila = $resPaises->fetch_assoc()) {
        $paises[] = $fila;
    }
}



$encabezado = "MODIFICAR ANUNCIO";
$style = "FolletoEstilo.css";
$style2 = "coloresPredeterminados.css";

require_once("cabecera.php");
?>
<main>
    <form action="respModificarAnuncio.php" method="post" class="campos">
        <fieldset>
            <legend>Modificar anuncio</legend>
            <input type="hidden" name="id" value="<?= htmlspecialchars($anuncioId) ?>">

            <p>
                <label for="TAnuncio">Tipo de anuncio <span class="obligatorio">*</span></label><br>
                <select id="TAnuncio" name="TAnuncio" required>
                    <option value="">--Selecciona tipo de anuncio--</option>
                    <?php foreach ($tiposAnuncios as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['IdTAnuncio']) ?>"
                            <?= $anuncioActual['TAnuncio'] == $tipo['IdTAnuncio'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['NomTAnuncio']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="TVivienda">Tipo de vivienda <span class="obligatorio">*</span></label><br>
                <select id="TVivienda" name="TVivienda" required>
                    <option value="">--Selecciona tipo de vivienda--</option>
                    <?php foreach ($tiposViviendas as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['IdTVivienda']) ?>"
                            <?= $anuncioActual['TVivienda'] == $tipo['IdTVivienda'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['NomTVivienda']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p>
                <label for="titulo">Titulo</label><br>
                <input type="text" id="titulo" name="titulo" placeholder="Titulo de inmueble" maxlength="200" required
                       value="<?= htmlspecialchars($anuncioActual['Titulo'] ?? '') ?>">
            </p>

            <p>
                <label for="descripcion">Descripción</label><br>
                <textarea id="descripcion" name="descripcion" required><?= htmlspecialchars($anuncioActual['Texto'] ?? '') ?></textarea>

            </p>

            <p>
                <label>Precio (€)</label><br>
                <input type="number" id="precio" name="precio"  min="0" step="0.01" placeholder="Precio del inmueble"
                       value="<?= htmlspecialchars($anuncioActual['Precio'] ?? '') ?>">
            </p>

            <p>
                <label for="pais">País <span class="obligatorio">*</span></label><br>
                <select id="pais" name="pais" required>
                    <option value="">--Selecciona país--</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= htmlspecialchars($pais['IdPais']) ?>"
                            <?= $anuncioActual['Pais'] == $pais['IdPais'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pais['NomPais']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="Localidad">Localidad <span class="obligatorio">*</span></label><br>
                <select id="localidad" name="localidad" required>
                    <option value="<?= htmlspecialchars($anuncioActual['Ciudad'] ?? '') ?>" selected>
                        <?= htmlspecialchars($anuncioActual['Ciudad'] ?? '-- Localidad actual --') ?>
                    </option>
                    <option value="Madrid">Madrid</option>
                    <option value="Alicante">Alicante</option>
                    <option value="Valencia">Valencia</option>
                    <option value="Castellón">Castellón</option>
                </select>
            </p>
            
            <h2>Características del inmueble</h2>
            <p>
                <label for="superficie">Superficie (m²): <span class="obligatorio">*</span></label><br>
                <input type="number" id="superficie" name="superficie" min="0" step="1" placeholder="Ej: 120" required
                       value="<?= htmlspecialchars($anuncioActual['Superficie'] ?? '') ?>">
            </p>

            <p>
                <label for="plantas">Plantas: <span class="obligatorio">*</span></label><br>
                <input type="number" id="plantas" name="plantas" min="1" step="1" required
                       value="<?= htmlspecialchars($anuncioActual['Planta'] ?? '') ?>">
            </p>

            <p>
                <label for="habitaciones">Número de habitaciones: <span class="obligatorio">*</span></label><br>
                <input type="number" id="habitaciones" name="habitaciones" min="1" step="1" required
                       value="<?= htmlspecialchars($anuncioActual['NHabitaciones'] ?? '') ?>">
            </p>

            <p>
                <label for="banos">Número de baños: <span class="obligatorio">*</span></label><br>
                <input type="number" id="banos" name="banos" min="1" step="1" required
                       value="<?= htmlspecialchars($anuncioActual['NBanyos'] ?? '') ?>">
            </p>

            <p>
                <label for="anio">Año de construcción: <span class="obligatorio">*</span></label><br>
                <input type="number" id="anio" name="anio" min="1900" max="<?= date('Y') ?>" step="1" required
                       value="<?= htmlspecialchars($anuncioActual['Anyo'] ?? '') ?>">
            </p>

            <p class="rad1">
                <p class="extras">
                    <label>Extras:</label><br>
                    <aside class="contenedor-extras">
                        <?php
                        $todosExtras = ['Cocina equipada', 'Terraza', 'Garaje', 'Piscina', 'Jardín'];
                        foreach ($todosExtras as $extra) {
                            $checked = in_array($extra, $extrasAnuncio) ? 'checked' : '';
                            echo "<aside class='fila-extra'>
                                    <label for='extra_" . strtolower(str_replace(' ', '_', $extra)) . "'>$extra</label>
                                    <input type='checkbox' id='extra_" . strtolower(str_replace(' ', '_', $extra)) . "' 
                                           name='extras[]' value='$extra' $checked>
                                  </aside>";
                        }
                        ?>
                    </aside>
                </p>
            </p>

            <p>
                <button type="submit">Guardar Cambios</button>
            </p>
                
            <p class="acciones">
                <a href="verAnuncio.php?id=<?= urlencode($anuncioId) ?>" class="cancelar">Volver al anuncio</a>
            </p>
        </fieldset>
    </form>
</main>
<?php
require_once("pie.php");
?>