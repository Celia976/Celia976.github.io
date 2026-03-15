<?php
require_once("controlSesion.php"); 
require_once(__DIR__ . "/conexionBD.php"); 

$idAnuncio = $_GET['id'] ?? null;
$fotos = [];
$mensaje = "";
$anuncio_titulo = "Anuncio Desconocido"; 

// Validación del ID
if (!is_numeric($idAnuncio) || $idAnuncio <= 0) {
    $mensaje = "Error: ID de anuncio no proporcionado o inválido.";
} else {
    // Consultar el Título del anuncio para el encabezado y verificación de existencia
    $sqlTitulo = "SELECT Titulo FROM Anuncios WHERE IdAnuncio = ?";
    $stmtTitulo = $conexion->prepare($sqlTitulo);
    $anuncio_encontrado = false;
    
    if ($stmtTitulo) {
        $stmtTitulo->bind_param("i", $idAnuncio);
        $stmtTitulo->execute();
        $resTitulo = $stmtTitulo->get_result();
        
        if ($filaTitulo = $resTitulo->fetch_assoc()) {
            $anuncio_titulo = htmlspecialchars($filaTitulo['Titulo']);
            $anuncio_encontrado = true;
        }
        $stmtTitulo->close();
    } else {
        $mensaje = "Error en la preparación de la consulta de título: " . $conexion->error;
    }

    if ($anuncio_encontrado) {
    // Consultar la Galería de Fotos (tabla Fotos, columnas IdFoto, Foto y Titulo)
    $sqlFotos = "SELECT IdFoto, Foto, Titulo FROM Fotos WHERE Anuncio = ?";
    $stmtFotos = $conexion->prepare($sqlFotos);

    if ($stmtFotos) {
        $stmtFotos->bind_param("i", $idAnuncio);
        $stmtFotos->execute();
        $resultado = $stmtFotos->get_result();

        $fotos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $fotos[] = [
                'id' => (int)$fila['IdFoto'],
                'ruta' => htmlspecialchars($fila['Foto']),
                'titulo' => htmlspecialchars($fila['Titulo'])
            ];
        }
        $stmtFotos->close();

        if (empty($fotos)) {
            $mensaje = "Este anuncio no tiene fotos adicionales para mostrar.";
        }

        } else {
            $mensaje = "Error en la preparación de la consulta de fotos: " . $conexion->error;
        }

    } else {
        // Mensaje si el anuncio no existe
        $mensaje = $mensaje ?: "El anuncio con ID $idAnuncio no existe o fue eliminado.";
    }
}


$encabezado = "GALERÍA DE FOTOS: " . $anuncio_titulo;
$style = "detAnunEstilo.css"; 
$style2 = "coloresPredeterminados.css"; 
require_once("cabecera.php");
?>

<main class="fotos-container">
    <?php if (!empty($fotos)): ?>
        <!-- Muestra la galería si hay fotos -->
        <div class="galeria-fotos">
            <?php foreach ($fotos as $foto_data): ?>
                <figure class="foto-item">
                    
                    <img src="fotos_anuncios/<?= $foto_data['ruta'] ?>" 
                        alt="<?= $foto_data['titulo'] ?>"
                        title="<?= $foto_data['titulo'] ?>"
                        onerror="this.onerror=null; this.src='placeholder.png';"> 

                    
                    <?php if (!empty($foto_data['titulo'])): ?>
                        <figcaption class="foto-caption"><?= $foto_data['titulo'] ?></figcaption>
                    <?php endif; ?>
                    <p>
                        <a href="respuestaBorrarFoto.php?idFoto=<?= $foto_data['id'] ?>" class="boton">Borrar foto</a>
                    </p>
                </figure>
            <?php endforeach; ?>
        </div>
        
        <!-- Enlace para volver al detalle del anuncio -->
        <p class="acciones">
            <a class="atras" href="verAnuncio.php?id=<?= $idAnuncio ?>">Volver al detalle del anuncio</a>
        </p>
    <?php endif; ?>

</main>


<?php
require_once("pie.php");
//Cerrar conexión a la BD
mysqli_close($conexion);
?>