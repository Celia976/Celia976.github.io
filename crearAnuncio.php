<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ==============================
// Comprobación de sesión
// ==============================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';


$encabezado = "CREE UN NUEVO ANUNCIO";
$style = "FolletoEstilo.css";
$style2 = "coloresPredeterminados.css";

require_once("cabecera.php");

// ======================================================
// 1. CONSULTAS A LA BASE DE DATOS
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


?>
<main>
        <form action="respCrearAnuncio.php" method="post" class="campos">
                <fieldset>
                    <legend>Crear nuevo anuncio</legend>
                     
                     <p>
                        <label for="TAnuncio">Tipo de anuncio <span class="obligatorio">*</span></label><br>
                        <select id="TAnuncio" name="TAnuncio" required>
                            <option value="">--Selecciona tipo de anuncio--</option>
                            <?php foreach ($tiposAnuncios as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['IdTAnuncio']) ?>">
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
                                <option value="<?= htmlspecialchars($tipo['IdTVivienda']) ?>">
                                    <?= htmlspecialchars($tipo['NomTVivienda']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    
                    <p>
                        <label for="titulo">Titulo</label><br>
                        <input type="text" id="titulo" name="titulo" placeholder="Titulo de inmueble" maxlength="200" required>
                    </p>

                    <p>
                        <label for="descripcion">Descripción</label><br>
                        <textarea id="descripcion" name="descripcion" placeholder="Información adicional" rows="4" cols="50" maxlength="4000" required></textarea>
                    </p>

                    <p>
                        <label>Precio (€)</label><br>
                        <input type="number" id="precio" name="precio"  min="0" step="0.01" placeholder="Precio del inmueble">
                    </p>

                    <p>
                        <label for="pais">País <span class="obligatorio">*</span></label><br>
                        <select id="pais" name="pais" required>
                            <option value="">--Selecciona país--</option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?= htmlspecialchars($pais['IdPais']) ?>">
                                    <?= htmlspecialchars($pais['NomPais']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label for="Localidad">Localidad <span class="obligatorio">*</span></label><br>
                                                <select id="localidad" name="localidad" required>
                            <option value="">--Selecciona provincia--</option>
                            <option value="Madrid">Madrid</option>
                            <option value="Alicante">Alicante</option>
                            <option value="Valencia">Valencia</option>
                            <option value="Castellón">Castellón</option>
                        </select>
                    </p>
                    
                    <h2>Características del inmueble</h3>
                                        <p>
                        <label for="superficie">Superficie (m²): <span class="obligatorio">*</span></label><br>
                        <input type="number" id="superficie" name="superficie" min="0" step="1" placeholder="Ej: 120" required>
                    </p>

                    <p>
                        <label for="plantas">Plantas: <span class="obligatorio">*</span></label><br>
                        <input type="number" id="plantas" name="plantas" min="1" step="1" required>
                    </p>

                    <p>
                        <label for="habitaciones">Número de habitaciones: <span class="obligatorio">*</span></label><br>
                        <input type="number" id="habitaciones" name="habitaciones" min="1" step="1" required>
                    </p>

                    <p>
                        <label for="banos">Número de baños: <span class="obligatorio">*</span></label><br>
                        <input type="number" id="banos" name="banos" min="1" step="1" required>
                    </p>

                    <p>
                        <label for="anio">Año de construcción: <span class="obligatorio">*</span></label><br>
                        <input type="number" id="anio" name="anio" min="1900" max="<?= date('Y') ?>" step="1" required>
                    </p>
                
                    <!-- Extras -->
                    <p class="rad1">
                        <p class="extras">
                            <label>Extras:</label><br>

                            <div class="contenedor-extras">
                                <div class="fila-extra">
                                    <label for="extra_cocina">Cocina equipada</label>
                                    <input type="checkbox" id="extra_cocina" name="extras[]" value="Cocina equipada">
                                </div>

                                <div class="fila-extra">
                                    <label for="extra_terraza">Terraza</label>
                                    <input type="checkbox" id="extra_terraza" name="extras[]" value="Terraza">
                                </div>

                                <div class="fila-extra">
                                    <label for="extra_garaje">Garaje</label>
                                    <input type="checkbox" id="extra_garaje" name="extras[]" value="Garaje">
                                </div>

                                <div class="fila-extra">
                                    <label for="extra_piscina">Piscina</label>
                                    <input type="checkbox" id="extra_piscina" name="extras[]" value="Piscina">
                                </div>

                                <div class="fila-extra">
                                    <label for="extra_jardin">Jardín</label>
                                    <input type="checkbox" id="extra_jardin" name="extras[]" value="Jardín">
                                </div>
                            </div>
                        </p>
                    </p>

                    <p>
                        <button type="submit">Publicar un anuncio!</button>
                    </p>
                        
                    <p class="acciones">
                        <a href="perfilUsuario.php" class="cancelar">Cancelar</a>
                    </p>
                </fieldset>
            </form>
    </main>
<?php
require_once("pie.php");
?>