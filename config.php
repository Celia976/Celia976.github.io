<?php
session_start();

require_once("conexionBD.php"); 

// Asignar el error por defecto a 1 
$error = 1;

if (isset($_POST['nombre']) && isset($_POST['contrasenya'])) {
    $user = trim($_POST['nombre']);
    $pass = trim($_POST['contrasenya']);
    $recordarme = isset($_POST['recordarme']);

    if ($user === '' || $pass === '') {
        $error = 1; // Error 1: Campos vacíos
    } else {
        
        global $conexion;

        //Comprobar la conexión a la BD
        if (!$conexion) {
            $error = 3; // Error 3: Error interno del servidor (fallo de conexión)
        } else {
            
            //Preparar la consulta para obtener el ID y la CLAVE
            $stmt = $conexion->prepare("SELECT IdUsuario, NomUsuario, Clave FROM usuarios WHERE NomUsuario = ?");
            $stmt->bind_param("s", $user);
            
            if (!$stmt->execute()) {
                $error = 3; 
            } else {
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $fila = $result->fetch_assoc();
                    $clave_almacenada = $fila['Clave']; 

                    if (password_verify($pass, $clave_almacenada)) {
                        // LOGIN EXITOSO
                        $_SESSION['login'] = 'ok';
                        $_SESSION['usuario'] = $fila['NomUsuario'];
                        $_SESSION['usuario_id'] = $fila['IdUsuario'];

                        if ($recordarme) {
                            $expiry = time() + (90 * 24 * 60 * 60);
                            setcookie('usuario_recordado', $user, $expiry, "/", "", false, true);
                            setcookie('ultima_visita', date('Y-m-d H:i:s'), $expiry, "/", "", false, true);
                        } else {
                            if (isset($_COOKIE['usuario_recordado'])) {
                                setcookie('usuario_recordado', '', time() - 3600, "/");
                                setcookie('ultima_visita', '', time() - 3600, "/");
                            }
                        }

                        $stmt->close();
                        $conexion->close();
                        header("Location: perfilUsuario.php?usuario=" . urlencode($fila['NomUsuario']));
                        exit();

                    } else {
                        $error = 2; // Error 2: Contraseña incorrecta
                    }
                } else {
                    $error = 2; // Error 2: Usuario no encontrado
                }
            }
            $stmt->close();
            $conexion->close();
        }
    }
} else {
    $error = 1;
}

// Redirección en caso de fallo
$_SESSION['flash_error'] = $error;
header("Location: formularioAcceso.php");
exit();
?>