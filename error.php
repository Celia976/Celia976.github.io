<?php
session_start();

$encabezado = "ENCUENTRA TU HOGAR AL MEJOR PRECIO";
$style = "index.css";
$style2 = "coloresPredeterminados.css";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

require_once("cabecera.php");
?>

<!--Muestra mensaje de error 404 al no estar logueado-->
<main>
    <section class="error404">
        <h2>Error 404: Página no encontrada</h2>
        <p>Para poder acceder al detalle del anuncio primero debes registrarte</p>
        <a class="volver" href="formularioAcceso.php">Volver a la página de inicio de sesión</a>
    </section>
</main>
<?php
require_once("pie.php");
?>