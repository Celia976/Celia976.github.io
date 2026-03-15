<?php
/**
 * Obtiene un consejo aleatorio desde "consejos.json"
 *
 * @param string $ruta_json Ruta al fichero consejos.json
 * @return array|null Devuelve un consejo o null si hay error
 */
function obtenerConsejoAleatorio($ruta_json = "consejos.json") {

    if (!file_exists($ruta_json)) {
        return null;
    }

    $contenido = file_get_contents($ruta_json);
    $consejos = json_decode($contenido, true);

    if (is_array($consejos) && count($consejos) > 0) {
        return $consejos[array_rand($consejos)];
    }

    return null;
}
