<?php
session_start();

$encabezado = "ENCUENTRA TU HOGAR AL MEJOR PRECIO";
$style = "index.css";
$style2 = "coloresPredeterminados.css";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

require_once("cabecera.php");
?>
<!--Muestra mensaje de error 404 si no existe la página-->
<main>
    <section class="error404">
        <h2>Error 404: Página no encontrada</h2>
        <a class="volver" href="index.php">Volver atrás</a>
    </section>
</main>
<?php
require_once("pie.php");
?>