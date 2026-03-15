<?php
$encabezado = "Usuario eliminado";
$style = "perfil.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<!--Muestra mensaje de baja completada-->
<main>
    <section class="bajaCompletaS">

        <h2>Tu usuario ha sido eliminado correctamente</h2>

        <p>
            Hemos eliminado tu cuenta, tus anuncios y todas tus imágenes asociadas.
            Esperamos volver a verte pronto.
        </p>

        <a href="index.php" class="boton">Volver al inicio</a>
    </section>
</main>

<?php require_once("pie.php"); ?>
