<?php
// Parámetros de conexión 
$servidor = "localhost";  
$usuario  = "root";       
$clave    = "";         
$bd       = "pibd";    

// Intentar la conexión
$conexion = @mysqli_connect("localhost:3307", "root", "", "pibd");

// Comprobación de errores
if (!$conexion) {
    die("<p>Error al conectar con la base de datos: " . mysqli_connect_error() . "</p>");
}

mysqli_set_charset($conexion, "utf8mb4");
?>