<?php
session_start();
require_once("conexionBD.php");

// ==================================================
// 0. Comprobación de Sesión y Datos Iniciales
// ==================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: formularioAcceso.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0;
$errores = [];

// Variables de presentación (cabecera)
$encabezado = "ACTUALIZAR MIS DATOS";
$style = "index.css";
$style2 = "comunFormularios.css";
$style3 = "coloresPredeterminados.css";
require_once("cabecera.php");

// Validación
require_once("filtrosUsuario.php");

// ==================================================
//  VALIDACIÓN DE SEGURIDAD: Contraseña Actual
// ==================================================
$sqlClave = "SELECT Clave FROM Usuarios WHERE IdUsuario = ?";
$stmtClave = $conexion->prepare($sqlClave);
$stmtClave->bind_param("i", $idUsuario);
$stmtClave->execute();
$resultadoClave = $stmtClave->get_result();
$filaClave = $resultadoClave->fetch_assoc();
$hash_clave_actual = $filaClave['Clave'] ?? '';
$stmtClave->close();

$contrasenya_actual_ingresada = $_POST['actual'] ?? '';

if (!password_verify($contrasenya_actual_ingresada, $hash_clave_actual)) {

    $errores[] = "La **contraseña actual** introducida no es correcta. No se han guardado los cambios.";
    $datos = [];

} else {

    // ==================================================
    //  FILTRADO Y VALIDACIÓN
    // ==================================================
    list($errores_filtrado, $datos) = filtrarDatosUsuario("misdatos", $_POST, $_FILES);
    $errores = array_merge($errores, $errores_filtrado);

    // ==================================================
    //  ASIGNACIÓN DE DATOS
    // ==================================================
    $nombre = $datos["nombre"];
    $pass = $datos["contrasenya"];
    $email = $datos["email"];
    $sexo = $datos["sexo"];
    $nacimiento = $datos["nacimiento"];
    $ciudad = $datos["ciudad"];
    $pais = $datos["pais"];
    $foto = $datos["foto"];
    $eliminar_foto = $datos["eliminar_foto"];
}

// ==================================================
//  ACTUALIZACIÓN DE BD
// ==================================================
if (count($errores) === 0) {

    if ($pass !== "") {
        $clave_hash = password_hash($pass, PASSWORD_DEFAULT);
        $sqlPass = ", Clave = ?";
    } else {
        $sqlPass = "";
    }

    $sql = "UPDATE usuarios SET
                NomUsuario = ?,
                Email = ?,
                Sexo = ?,
                FNacimiento = ?,
                Ciudad = ?,
                Pais = ?
                $sqlPass
            WHERE IdUsuario = ?";

    $stmt = $conexion->prepare($sql);

    if ($pass !== "") {
        $stmt->bind_param(
            "ssissisi",
            $nombre, $email, $sexo, $nacimiento, $ciudad, $pais, $clave_hash, $idUsuario
        );
    } else {
        $stmt->bind_param(
            "ssissii",
            $nombre, $email, $sexo, $nacimiento, $ciudad, $pais, $idUsuario
        );
    }

    if (!$stmt->execute()) {
        $errores[] = "Error al actualizar los datos principales: " . $stmt->error;
    }

    $stmt->close();

    if (count($errores) === 0) {

        // FOTO ACTUAL
        $sqlFotoActual = "SELECT Foto FROM Usuarios WHERE IdUsuario = ?";
        $stmtFotoActual = $conexion->prepare($sqlFotoActual);
        $stmtFotoActual->bind_param("i", $idUsuario);
        $stmtFotoActual->execute();
        $resultadoFotoActual = $stmtFotoActual->get_result();
        $foto_actual_ruta = $resultadoFotoActual->fetch_assoc()['Foto'] ?? 'imagenes_usuarios/default.jpg';
        $stmtFotoActual->close();

        // ELIMINAR FOTO
        if ($eliminar_foto) {
            if (!empty($foto_actual_ruta) && $foto_actual_ruta !== 'imagenes_usuarios/default.jpg' && file_exists($foto_actual_ruta)) {
                @unlink($foto_actual_ruta);
            }
            $stmtF = $conexion->prepare("UPDATE usuarios SET Foto = 'imagenes_usuarios/default.jpg' WHERE IdUsuario = ?");
            $stmtF->bind_param("i", $idUsuario);
            $stmtF->execute();
            $stmtF->close();
        }

        // SUBIR FOTO NUEVA
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {

            $extension = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
            $permitidas = ["jpg", "jpeg", "png", "gif"];

            if (in_array($extension, $permitidas)) {

                $nuevoNombre = "user_" . $idUsuario . "_" . time() . "." . $extension;
                $rutaDestino = "imagenes_usuarios/" . $nuevoNombre;

                if (!is_dir("imagenes_usuarios")) {
                    mkdir("imagenes_usuarios", 0755, true);
                }

                if (move_uploaded_file($foto["tmp_name"], $rutaDestino)) {

                    if (!empty($foto_actual_ruta) && $foto_actual_ruta !== 'imagenes_usuarios/default.jpg' && !$eliminar_foto && file_exists($foto_actual_ruta)) {
                        @unlink($foto_actual_ruta);
                    }

                    $stmtFoto = $conexion->prepare("UPDATE usuarios SET Foto = ? WHERE IdUsuario = ?");
                    $stmtFoto->bind_param("si", $rutaDestino, $idUsuario);
                    $stmtFoto->execute();
                    $stmtFoto->close();

                } else {
                    $errores[] = "Error al mover la foto subida.";
                }
            } else {
                $errores[] = "Formato de foto no permitido (solo jpg, jpeg, png o gif).";
            }
        }

        // SESIÓN Y COOKIE
        $_SESSION['usuario'] = $nombre;

        if (isset($_COOKIE['usuario_recordado'])) {
            $expiry = time() + (90 * 24 * 60 * 60);
            if ($pass !== "") {
                setcookie("usuario_recordado", "", time() - 3600, "/", "", false, true);
            } else {
                setcookie("usuario_recordado", $nombre, $expiry, "/", "", false, true);
            }
        }

        // OBTENER NOMBRE DEL PAÍS
        $nombre_pais = "";
        if ($pais) {
            $sqlPais = "SELECT NomPais FROM Paises WHERE IdPais = ?";
            $stmtPais = $conexion->prepare($sqlPais);
            $stmtPais->bind_param("i", $pais);
            $stmtPais->execute();
            $resPais = $stmtPais->get_result();
            $nombre_pais = $resPais->fetch_assoc()['NomPais'] ?? 'No especificado';
            $stmtPais->close();
        }
    }
}

$conexion->close();
?>

<main>
<section class="error404">
    <?php if (count($errores) > 0): ?>
        <h2>Se han encontrado errores:</h2>
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="misDatos.php" class="botonVolver">Volver al formulario</a></p>
    <?php else: ?>
        <h2>Datos actualizados correctamente</h2>
        <h3>Resumen de los cambios guardados:</h3>

        <table class="resultadoMisDatos">
            
            <tr><td><strong>Nombre de Usuario</strong></td><td><?= htmlspecialchars($nombre) ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($email) ?></td></tr>
            <tr><td><strong>Sexo</strong></td><td><?= ($sexo == 1) ? 'Hombre' : 'Mujer' ?></td></tr>
            <tr><td><strong>Fecha de Nacimiento</strong></td><td><?= htmlspecialchars($nacimiento) ?></td></tr>
            <tr><td><strong>Ciudad</strong></td><td><?= htmlspecialchars($ciudad) ?></td></tr>
            <tr><td><strong>País</strong></td><td><?= htmlspecialchars($nombre_pais) ?></td></tr>
            <?php if ($pass !== ""): ?>
            <tr><td><strong>Contraseña</strong></td><td>*** (Actualizada)</td></tr>
            <?php endif; ?>
            <tr><td><strong>Foto</strong></td><td>
                <?php 
                    if ($eliminar_foto) {
                        echo 'Eliminada (Foto por defecto)';
                    } else if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
                        echo 'Subida nueva foto.';
                    } else {
                        echo 'Sin cambios.';
                    }
                ?>
            </td></tr>
        </table>

        <p><a href="perfilUsuario.php?id=<?= $idUsuario ?>" class="botonVolver">Volver al Perfil</a></p>
    <?php endif; ?>
</section>
</main>

<?php require_once("pie.php"); ?>
