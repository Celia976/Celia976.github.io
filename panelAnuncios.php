<?php
/* ============================================================
   PANEL “ÚLTIMOS ANUNCIOS VISITADOS” 
   ============================================================ */

if ($usuario_anterior !== $usuario_actual || !$logueado) {
    // Borrar cookie de últimos anuncios
    setcookie('ultimos_anuncios', '', time() - 3600, "/");

    // Actualizar cookie de usuario actual

}

echo "<section class='panel-ultimos'>";
echo "<h3> Últimos anuncios visitados</h3>";


// Verificamos si la variable $anuncios_visitados (cargada desde index.php) contiene datos
if (!empty($anuncios_visitados)) {
    // $ultimos contiene solo los IDs de la cookie, usado para mantener el orden de visita.
    // Este array se obtiene en index.php antes de este include.
    $ultimos = json_decode($_COOKIE['ultimos_anuncios'] ?? '[]', true);

    if ($ultimos && is_array($ultimos) && count($ultimos) > 0) {
        echo "<div class='lista-ultimos'>";
        
        // Iteramos sobre los IDs de la cookie para asegurar el orden de visita
        foreach ($ultimos as $id) {
            if (isset($anuncios_visitados[$id])) {
                $a = $anuncios_visitados[$id];

                // Construir ruta de la foto correctamente
                $srcFoto = !empty($a['Foto']) ? "fotos_anuncios/" . $a['Foto'] : "placeholder.png";

                // URL destino según login
                $url_destino = $logueado ? "detalleAnuncio.php?id=" . $id : "error.php?id=" . $id;

                echo "<article class='mini-anuncio'>
                        <a href='" . htmlspecialchars($url_destino) . "'>
                            <img src='" . htmlspecialchars($srcFoto) . "' 
                                 alt='" . htmlspecialchars($a['Titulo']) . "' 
                                 class='mini-foto'>
                            <h4>" . htmlspecialchars($a['Titulo']) . "</h4>
                            <p><strong>" . htmlspecialchars($a['Ciudad']) . "</strong>, " . htmlspecialchars($a['PaisNombre']) . "</p>
                            <p class='precio'>" . htmlspecialchars($a['Precio']) . " €</p>
                        </a>
                      </article>";
            }
        }

        echo "</div>";
    } else {
        echo "<p>No has visitado ningún anuncio todavía.</p>";
    }
} else {
    echo "<p>No has visitado ningún anuncio todavía.</p>";
}
echo "</section>";
?>
