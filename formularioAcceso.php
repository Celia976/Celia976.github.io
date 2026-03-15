<?php
session_start();
require_once("conexionBD.php");
$encabezado = "INICIO DE SESIÓN";
$style = "index.css";
$style2 = "comunFormularios.css";
$style3 = "coloresPredeterminados.css";
require_once("cabecera.php");

// ==========================================================
// LECTURA Y BORRADO DE FLASH DATA
// ==========================================================

//Leer el código de error de la sesión (Flash Data)
$error = $_SESSION['flash_error'] ?? null;

// Borrar la variable de sesión para que el mensaje no persista
unset($_SESSION['flash_error']);

$mensaje_error = "";
switch ($error) {
    case '1':
        $mensaje_error = "Error: Debes introducir tu nombre de usuario y contraseña (no pueden estar vacíos).";
        break;
    case '2':
        $mensaje_error = "Error: Nombre de usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.";
        break;
    case '3':
        $mensaje_error = "Error interno del servidor (fallo al cargar usuarios). Inténtelo más tarde.";
        break;
}
?>

<main>
    <?php if ($mensaje_error): ?>
       <p style="color: red; font-weight: bold; padding: 10px; border: 1px solid red; background-color: #fee;">
            <?= htmlspecialchars($mensaje_error) ?>
        </p>
    <?php endif; ?>
        <form id="formRegistro" action="config.php" method="post" enctype="multipart/form-data" class="datos">

            <fieldset>
                <legend>Introduce tus datos...</legend>

             <p>
             <label for="nombre">Nombre de usuario:<span class="obligatorio">*</span></label>
             <input type="text" id="nombre" name="nombre"
                 value="<?= isset($_COOKIE['usuario_recordado']) ? htmlspecialchars($_COOKIE['usuario_recordado']) : '' ?>">
             </p>


             <p>
             <label for="contrasenya">Contraseña:<span class="obligatorio">*</span></label>
             <input type="password" id="contrasenya" name="contrasenya" >
             </p>

             <p>
                 <label>
                     <input type="checkbox" name="recordarme" value="1"
                         <?= isset($_COOKIE['usuario_recordado']) ? 'checked' : '' ?>>
                     Recordarme en este equipo
                 </label>
             </p>
             <button type="submit" class="boton">Iniciar sesión</button>

             <p class="acciones">
                 <button type="button" onclick="location.href='index.php'" class="cancelar"> Cancelar Inicio de Sesión</button>
                     ¿No tienes cuenta?
                 <a href = "iniciarSesion.php" class="iniciar">REGISTRATE</a>
             </p>
             </fieldset>
        </form>
</main>

<?php
require_once("pie.php");
?>