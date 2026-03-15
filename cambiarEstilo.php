<?php
session_start();
require_once("conexionBD.php");

// Estilo recibido
$nuevo_estilo = $_GET['estilo'] ?? null;

// Solo procesar si se envió un estilo
if ($nuevo_estilo) {

    // Comprobar si ese estilo existe en la BD
    $stmt = $conexion->prepare("SELECT Nombre FROM estilos WHERE Nombre = ?");
    $stmt->bind_param("s", $nuevo_estilo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si existe → guardarlo en sesión
    if ($result->num_rows === 1) {
        $_SESSION['estilo_seleccionado'] = $nuevo_estilo;
    }

    $stmt->close();
}

// Redirigir directamente a la página de confirmación
header('Location: respuestaEstilos.php');
exit();
?>
