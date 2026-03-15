<?php
/**
 * Lee el fichero de anuncios escogidos y devuelve un array con sus datos.
 *
 * El formato esperado es:
 * ID: <IdAnuncio>
 * EXPERTO: <Nombre del Experto>
 * COMENTARIO: <Comentario del Experto>
 * --- (Separador de anuncios)
 *
 * @param string $ruta_fichero La ruta al fichero de texto de anuncios escogidos.
 * @return array Un array de arrays, cada uno con 'IdAnuncio', 'Experto', 'Comentario'.
 */
function obtenerAnunciosEscogidos($ruta_fichero = 'anuncios_escogidos.txt') {
    $anuncios_escogidos = [];

    //Comprobar si el fichero existe y es legible.
    if (!file_exists($ruta_fichero) || !is_readable($ruta_fichero)) {
       
        return $anuncios_escogidos;
    }

    // Lee el fichero en un array de líneas, omitiendo líneas vacías
    $lineas = file($ruta_fichero, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $anuncio_actual = [];

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if (strpos($linea, 'ID:') === 0) {
            // Empieza un nuevo anuncio. Extrae el ID.
            $anuncio_actual = ['IdAnuncio' => (int)trim(substr($linea, 3))];
        } elseif (strpos($linea, 'EXPERTO:') === 0) {
            // Extrae el nombre del experto.
            $anuncio_actual['Experto'] = trim(substr($linea, 8));
        } elseif (strpos($linea, 'COMENTARIO:') === 0) {
            // Extrae el comentario.
            $anuncio_actual['Comentario'] = trim(substr($linea, 11));
        } elseif ($linea === '---') {
            // Separador de anuncios. Si es un anuncio válido, lo guardamos.
            if (isset($anuncio_actual['IdAnuncio'], $anuncio_actual['Experto'], $anuncio_actual['Comentario'])) {
                $anuncios_escogidos[] = $anuncio_actual;
            }
            $anuncio_actual = []; // Reiniciar para el siguiente
        }
    }

    return $anuncios_escogidos;
}
?>