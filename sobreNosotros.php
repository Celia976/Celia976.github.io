<?php
session_start();
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

$style = "index.css";
$style2 = "coloresPredeterminados.css";
$encabezado = "SOBRE NOSOTROS";
require_once("cabecera.php");
?>

<main>
        <aside class="explicacion">
            <p>En RENT & HOUSE creemos que cada vivienda tiene una historia, y nuestro objetivo es ayudarte a escribir la tuya.</p>
            <p>Sabemos que buscar una casa no es solo una transacción, sino una decisión importante en tu vida. Por eso, te acompañamos paso a paso para que encuentres el espacio que realmente se sienta como un hogar.</p>
            <p>Ya sea que busques alquilar, comprar o vender, nuestro equipo te ofrecerá un trato cercano, transparente y profesional, garantizando que cada proceso sea claro y sin complicaciones.</p>
        </aside>
</main>

<?php
require_once("pie.php");
?>