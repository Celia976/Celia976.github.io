<?php
    require_once("conexionBD.php");
    // Determinar el estilo activo
    $estilo_activo = $_SESSION['estilo_seleccionado'] ?? 'Predeterminado';

    // Obtener archivos CSS de ese estilo desde BD
    $stmt = $conexion->prepare("
        SELECT ea.ArchivoCSS
        FROM estilos e
        JOIN estilos_archivos ea ON e.IdEstilo = ea.IdEstilo
        WHERE e.Nombre = ?
        ORDER BY ea.IdArchivo ASC
    ");
    $stmt->bind_param("s", $estilo_activo);
    $stmt->execute();
    $result = $stmt->get_result();

    $archivosCSS = [];
    while ($fila = $result->fetch_assoc()) {
        $archivosCSS[] = $fila['ArchivoCSS'];
    }

    // Determinar si usuario está logueado
    $logueado = $logueado ?? (isset($_SESSION['login']) && $_SESSION['login'] === 'ok');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENT & HOUSE</title>

    <link rel="stylesheet" href="estiloComun.css">
    <?php if (isset($style)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endif; ?>
    <?php if (isset($style2)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style2) ?>">
    <?php endif; ?>
    
    <?php foreach ($archivosCSS as $archivo): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($archivo) ?>">
    <?php endforeach; ?>

    
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Lobster&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

</head>
<body>
    <header>
    <aside class="cabecera">
        <a href="index.php">
            <img src="logo.png" alt="logo" height="130" width="130"> 
        </a>
        <h1><?= htmlspecialchars($encabezado) ?></h1>
        <nav> 
            <?php if ($logueado): ?>
                <a class="perfil" href="perfilUsuario.php"> 
                    <span class="material-symbols-outlined">account_circle</span> Perfil Usuario
                </a>
            <?php else: ?>
                <a href="formularioAcceso.php">
                    <span class="material-symbols-outlined">login</span> Iniciar Sesión
                </a>
                <a href="iniciarSesion.php"> 
                    <span class="material-symbols-outlined">output</span> Registrarse
                </a>          
            <?php endif; ?>
        </nav>
    </aside>
    <aside class="formulario">
        <form action="formularioBusqueda.php" method="get">
            <label for="bCiudad">
                <span class="material-symbols-outlined">search</span> Búsqueda rápida:
            </label>
            <input type="text" id="bCiudad" name="bCiudad" placeholder="Ej: vivienda alquiler alicante">
            <button type="submit">Buscar</button>
        </form>
    </aside>
</header>