<?php
require_once("conexionBD.php");
require_once("controlSesion.php");
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

$encabezado = "RESGUARDO DE SU SOLICITUD";
$style = "respFolleto.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");

// ID del anuncio seleccionado por el usuario 
$anuncio_id = $_POST["anuncio"] ?? null; 
$anuncio_seleccionado = $anuncios[$anuncio_id] ?? null;

// Inicialización de variables para la lógica del coste
$paginas = $anuncio_seleccionado['paginas'] ?? 1; // Usar el número de páginas del anuncio
$fotos = $anuncio_seleccionado['fotos'] ?? 3;     // Usar el número total de fotos del anuncio
$nombre_anuncio = $anuncio_seleccionado['titulo'] ?? "Anuncio desconocido";
?>

<main class = "mainRespuestaSol">
        <p>Hemos recibido su solicitud. A continuación, los datos introducidos</p>

         <article>
        <ul>
            <li><strong>Nombre:</strong> <?= htmlspecialchars($_POST["nombre"] ?? "No indicado") ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($_POST["email"] ?? "No indicado") ?></li>
            <li><strong>Texto adicional:</strong> <?= htmlspecialchars($_POST["descripcion"] ?? "No especificado") ?></li>

            <li><strong>Dirección:</strong> 
                <?= htmlspecialchars($_POST["calle"] ?? "") ?>, 
                <?= htmlspecialchars($_POST["numero"] ?? "") ?>,
                <?= htmlspecialchars($_POST["cp"] ?? "") ?>,
                <?php
                    $localidad = htmlspecialchars($_POST["localidad"] ?? "No indicada");
                    $provincia = htmlspecialchars($_POST["provincia"] ?? "No indicada");
                    echo "$localidad, $provincia";
                ?>
            </li>

            <li><strong>Teléfono:</strong> <?= htmlspecialchars($_POST["telefono"] ?? "No indicado") ?></li>
            <li><strong>Color portada:</strong> <?= htmlspecialchars($_POST["col"] ?? "#000000") ?></li>
            <li><strong>Número de copias:</strong> <?= htmlspecialchars($_POST["num"] ?? "1") ?></li>
            <li><strong>Resolución:</strong> <?= htmlspecialchars($_POST["resolucion"] ?? "150") ?> dpi</li>

            <li><strong>Anuncio:</strong> 
                <?php 
                    $anuncio = $_POST["anuncio"] ?? "No seleccionado";
                    echo htmlspecialchars($anuncio);
                ?>
            </li>

            <li><strong>Fecha recepción:</strong> 
                <?= !empty($_POST["fecha"]) ? date("d/m/Y", strtotime($_POST["fecha"])) : "No especificada" ?>
            </li>

            <li><strong>Impresión a color:</strong> 
                <?= (($_POST["impresion"] ?? "") === "color") ? "Sí" : "No" ?>
            </li>

            <li><strong>Impresión del precio:</strong> 
                <?= (($_POST["precio"] ?? "") === "si") ? "Sí" : "No" ?>
            </li>
        </ul>


        <?php

            // --- Coste fijo ---
            $costeProcesamiento = 10.0;

            // --- Coste por páginas ---
            if ($paginas < 5) {
                $costePaginas = $paginas * 2.0;
            } elseif ($paginas <= 10) {
                $costePaginas = $paginas * 1.8;
            } else {
                $costePaginas = $paginas * 1.6;
            }

            // --- Coste por color / resolución ---
            $color = ($_POST["impresion"] ?? "") === "color";
            $altaRes = isset($_POST["resolucion"]) && $_POST["resolucion"] > 300;

            $costeFotos = 0.0;
            if ($color) {
                $costeFotos += $fotos * 0.5;   // coste adicional por foto a color
            }
            if ($altaRes) {
                $costeFotos += $fotos * 0.2;   // coste adicional por alta resolución
            }

            // --- Coste total unitario ---
            $costeUnitario = $costeProcesamiento + $costePaginas + $costeFotos;

            // --- Número de copias ---
            $copias = (int)($_POST["num"] ?? 1);

            // --- Coste final ---
            $costeFinal = $costeUnitario * $copias;

            // Preparar los datos
            $direccion = trim("{$_POST['calle']}, {$_POST['numero']}, {$_POST['cp']}, {$_POST['localidad']}, {$_POST['provincia']}");

            // Nombre literal del color (para el campo Color)
            $nombreColor = $_POST["col"] ?? "#000000";

            // Indicador lógico de impresión a color (para IColor)
            $impresionColor = ($_POST["impresion"] ?? "") === "color" ? 1 : 0;

            // Indicador lógico de impresión del precio (para IPrecio)
            $impresionPrecio = ($_POST["precio"] ?? "") === "si" ? 1 : 0;

            // Fecha de recepción
            $fechaRecepcion = !empty($_POST["fecha"]) ? $_POST["fecha"] : null;

            $stmt = $conexion->prepare("
                INSERT INTO solicitudes
                (Anuncio, Texto, Nombre, Email, Direccion, Telefono, Color, Copias, Resolucion, Fecha, IColor, IPrecio, Coste)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isssssiiisidd",
                $anuncio_id,
                $_POST["descripcion"],
                $_POST["nombre"],
                $_POST["email"],
                $direccion,
                $_POST["telefono"],
                $nombreColor,    
                $copias,
                $_POST["resolucion"],
                $fechaRecepcion,
                $impresionColor,
                $impresionPrecio,
                $costeFinal
            );

            $stmt->execute();
            $stmt->close();
            mysqli_close($conexion);
        ?>
        <h2>Coste del folleto publicitario: <?= number_format($costeFinal, 2) ?> €</h2>
    </article>
        
        <a class= "vuelta" href = "index.php"> Volver a la página de inicio</a>

    </main>

<?php
require_once("pie.php");
?>