<?php
session_start();

$style = "index.css";
$style2 = "coloresPredeterminados.css";
$encabezado = "DECLARACIÓN DE ACCESIBILIDAD";

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

require_once("cabecera.php");
?>

<main>
        <aside class="declaracion">
            <h2>Compromiso con la accesibilidad</h2>
            <p>
                En <strong>RENT & HOUSE</strong> trabajamos para garantizar que nuestro sitio web sea accesible a todas las personas, 
                incluyendo aquellas con diferentes capacidades visuales, auditivas o cognitivas.
            </p>

            <h2>Medidas implementadas</h2>
            <ul>
                <li><strong>Modo oscuro:</strong> para usuarios con sensibilidad a la luz o que prefieren fondos oscuros.</li>
                <li><strong>Alto contraste:</strong> colores muy diferenciados entre fondo y texto para mejorar la legibilidad.</li>
                <li><strong>Letra grande:</strong> textos ampliados y con mayor espaciado para facilitar la lectura.</li>
                <li><strong>Modo dislexia:</strong> tipografía diseñada para personas con dislexia, mejorando la fluidez lectora.</li>
                <li><strong>Combinación alto contraste + letra grande:</strong> para usuarios que necesiten ambas mejoras.</li>
            </ul>

            <h2>Buenas prácticas de accesibilidad</h2>
            <ul>
                <li>Uso de <strong>etiquetado semántico</strong> correcto (encabezados, listas, secciones y enlaces).</li>
                <li>Todas las imágenes incluyen <strong>texto alternativo</strong> mediante el atributo <code>alt</code>.</li>
                <li>Se utilizan <strong>colores con contraste adecuado</strong> conforme a las pautas WCAG 2.1.</li>
                <li>Los enlaces y botones son fácilmente distinguibles al pasar el ratón o navegar con teclado.</li>
                <li>El contenido mantiene su estructura y sentido incluso con los estilos CSS desactivados.</li>
            </ul>

            <h2>Activación de los estilos alternativos</h2>
            <p>
                Para cambiar entre los distintos estilos de accesibilidad en <strong>Mozilla Firefox</strong>, pulsa la tecla <kbd>Alt</kbd> 
                y selecciona desde el menú superior: <strong>Ver → Estilo de página</strong>, eligiendo el tema deseado 
                (Oscuro, Alto Contraste, Letra grande, etc.).
            </p>

            <h2>Compromiso de mejora continua</h2>
            <p>
                RENT & HOUSE sigue las recomendaciones del <strong>W3C (Web Content Accessibility Guidelines - WCAG 2.1)</strong> 
                y revisa periódicamente el sitio web para incorporar nuevas mejoras que faciliten el acceso universal.
            </p>
        </aside>

        <aside class="explicacion">
            <h2>Cómo se ha implementado la accesibilidad</h2>
            <p>
                Durante el desarrollo del sitio, se aplicaron principios de diseño inclusivo, tipografías legibles y 
                combinaciones de colores seguras para todo tipo de usuarios.
            </p>
            <p>
                Además, se han creado hojas de estilo alternativas que pueden activarse fácilmente, adaptándose a las preferencias 
                y necesidades de cada persona.
            </p>
        </aside>
    </main>

<?php
require_once("pie.php");
?>
