<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';


$encabezado = "RELLENE SU FOLLETO";
$style = "FolletoEstilo.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");

$idUsuario = $_SESSION['usuario_id'] ?? 0;

$sql = "SELECT IdAnuncio, Titulo
        FROM Anuncios 
        WHERE Usuario = ? ORDER BY FRegistro DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();

$anuncios = [];
while ($fila = $resultado->fetch_assoc()) {
    $anuncios[] = $fila;
}

$stmt->close();
mysqli_close($conexion);
?>

    <main>
        <h2>Solicitud de impresión de folleto publicitario</h2>
        <p>Mediante esta opción puedes solicitar la impresión y envío de uno de tus anuncios a todo color, toda resolución</p>

        <aside class="contenedor">
        <aside class="bloque-tablas">
        <section id="tarifas">
            
            <h2>Tabla de tarifas</h2>
            <aside>
                <table>
                    <tr>
                        <th>Concepto</th>
                        <th>Tarifa</th>
                    </tr>
                    <tr>
                        <td>Coste procesamiento y envio</td>
                        <td>10 €</td>
                    </tr>
                    <tr>
                        <td>Menos 5 páginas</td>
                        <td>2 € por pág.</td>
                    </tr>
                    <tr>
                        <td>Entre 5 y 10 páginas</td>
                        <td>1.8 € por pág</td>
                    </tr>
                    <tr>
                        <td>Más de 10 páginas</td>
                        <td>1.6 € por pág.</td>
                    </tr>
                    <tr>
                        <td>Blanco y negro</td>
                        <td>0 €</td>
                    </tr>
                    <tr>
                        <td>Color</td>
                        <td>0.5 € por foto</td>
                    </tr>
                    <tr>
                        <td>Resolución menor o igual a 300 dpi</td>
                        <td>0 € por foto</td>
                    </tr>
                    <tr>
                        <td>Resolución mayor a 300 dpi</td>
                        <td>0.2 € por foto</td>
                    </tr>
                </table>
            </aside>
           
        </section>

        <?php
            function crearTablaCostes($anuncios_disponibles) {
                $baseBn150 = [
                    1 => 12.00, 2 => 14.00, 3 => 16.00, 4 => 18.00, 5 => 19.80,
                    6 => 21.60, 7 => 23.40, 8 => 25.20, 9 => 27.00, 10 => 28.80,
                    11 => 30.40, 12 => 32.00, 13 => 33.60, 14 => 35.20, 15 => 36.80
                ];

                $fotosPorPagina = 3;

                echo "<section id='tablaCostes'>";
                echo "<h2>Tabla con posibles costes de un folleto impreso</h2>";

                echo "<table class='tabla-costes'>
                        <thead>
                            <tr>
                                <th rowspan='2'>Número de páginas</th>
                                <th rowspan='2'>Número de fotos</th>
                                <th colspan='2'>Blanco y negro</th>
                                <th colspan='2'>Color</th>
                            </tr>
                            <tr>
                                <th>150-300 dpi</th>
                                <th>450-900 dpi</th>
                                <th>150-300 dpi</th>
                                <th>450-900 dpi</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($baseBn150 as $paginas => $base) {
                    $fotos = $paginas * $fotosPorPagina;

                    $bn_150 = $base;
                    $bn_450 = $base + ($fotos * 0.2);
                    $color_150 = $base + ($fotos * 0.5);
                    $color_450 = $base + ($fotos * 0.7); 

                    echo "<tr>
                            <td>$paginas</td>
                            <td>$fotos</td>
                            <td>" . number_format($bn_150, 2) . " €</td>
                            <td>" . number_format($bn_450, 2) . " €</td>
                            <td>" . number_format($color_150, 2) . " €</td>
                            <td>" . number_format($color_450, 2) . " €</td>
                        </tr>";
                }

                echo "</tbody></table></section>";
            }
            
         crearTablaCostes($anuncios);
        ?>
        </aside>

            <form action="respuestaFolleto.php" method="post" class="campos">
                <fieldset>
                    <legend>Formulario de solicitud</legend>
                     <p>Los campos marcados con (*) son obligatorios</p>
                    <p>
                        <label for="descripcion">Texto adicional</label><br>
                        <textarea id="descripcion" name="descripcion" placeholder="Información adicional" rows="4" cols="50" maxlength="4000"></textarea>
                    </p>

                    <p>
                        <label for="nombre">Nombre (*)</label><br>
                        <input type="text" id="nombre" name="nombre" placeholder="Su nombre" maxlength="200" required>
                    </p>

                    <p>
                        <label for="email">Correo electrónico (*)</label><br>
                        <input type="email" id="email" name="email" placeholder="Su e-mail" maxlength="200" required>
                    </p>

                    <p>
                        <label>Dirección (*)</label><br>
                        <input type="text" id="calle" name="calle" placeholder="Calle" required>
                        <input type="number" id="numero" name="numero" placeholder="Número" required>
                        <input type="number" id="cp" name="cp" placeholder="CP" required>
                    </p>

                    <p>
                        <label for="localidad">Localidad (*)</label><br>
                        <select id="localidad" name="localidad" required>
                            <option value="">--Selecciona localidad--</option>
                            <option value="Alicante">Alicante</option>
                            <option value="Valencia">Valencia</option>
                            <option value="Madrid">Madrid</option>
                            <option value="Castellón">Castellón</option>
                            <option value="Murcia">Murcia</option>
                        </select>
                    </p>

                    <p>
                        <label for="provincia">Provincia (*)</label><br>
                        <select id="provincia" name="provincia" required>
                            <option value="">--Selecciona provincia--</option>
                            <option value="Alcoi">Alcoi</option>
                            <option value="Aspe">Aspe</option>
                            <option value="Altea">Altea</option>
                            <option value="Benidorm">Benidorm</option>
                        </select>
                    </p>

                    <p>
                        <label for="tel">Teléfono</label><br>
                        <input type="tel" id="tel" name="telefono" placeholder="### ## ## ##">
                    </p>

                    <p>
                        <label for="col">Color de la portada</label><br>
                        <input type="color" id="col" name="col" value="#000000">
                    </p>

                    <p>
                        <label for="num">Número de copias</label><br>
                        <input type="number" id="num" name="num" min="1" max="99" value="1" step="1">
                    </p>

                    <p>
                        <label for="resolucion">Resolución de impresión</label><br>
                        <input type="number" id="resolucion" name="resolucion" min="150" max="900" value="150" step="150">
                    </p>

                    <p>
                         <label for="anuncio">Selecciona uno de tus anuncios:</label>
                        <select name="anuncio" id="anuncio" required>
                            <?php if (empty($anuncios)): ?>
                                <option value="">No tienes anuncios publicados</option>
                            <?php else: ?>
                        <?php foreach ($anuncios as $anuncio): ?>
                            <option value="<?= htmlspecialchars($anuncio['IdAnuncio']) ?>">
                                <?= htmlspecialchars($anuncio['Titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                    </p>

                    <p>
                        <label for="fecha">Fecha recepción</label><br>
                        <input type="date" id="fecha" name="fecha"><br>
                        <small>Fecha aproximada de recepción, no se puede garantizar su cumplimiento</small>
                    </p>

                    <p class="rad1">
                        <label>¿Impresión a color?</label>
                        
                        <label for="color">Color</label>
                        <input type="radio" id="color" name="impresion" value="color" required>
                        
                        <label for="byn">Blanco y negro</label>
                        <input type="radio" id="byn" name="impresion" value="byn">
                        
                    </p>

                    <p class="rad2">
                        <label>¿Impresión del precio?</label>
                        
                        <label for="precio_si">Sí</label>
                        <input type="radio" id="precio_si" name="precio" value="si" required>
                        
                        <label for="precio_no">No</label>
                        <input type="radio" id="precio_no" name="precio" value="no">
                        
                    </p>

                    <p>
                        <button type="submit">¡Solicitar!</button>
                    </p>
                        
                    <p class="acciones">
                        <a href="perfilUsuario.php" class="cancelar">Cancelar</a>
                    </p>
                </fieldset>
            </form>
        </aside>
    </main>


<?php
require_once("pie.php");
?>