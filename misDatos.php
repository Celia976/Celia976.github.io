<?php
require_once("controlSesion.php");
require_once(__DIR__ . "/conexionBD.php");

// ==============================
// Comprobación de sesión
// ==============================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$idUsuario = $_SESSION['usuario_id'] ?? 0;

if (!$logueado || !$idUsuario) {
    header("Location: formularioAcceso.php");
    exit();
}

// Encabezado
$encabezado = "MIS DATOS PERSONALES";
$style = "folletoEstilo.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");

// ==================================================
// 1. Obtener datos del usuario ANTES de procesar POST
// ==================================================
$sql = "SELECT NomUsuario, Email, Ciudad, Pais, Sexo, FNacimiento, Foto, Clave
        FROM Usuarios 
        WHERE IdUsuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

$clave_hash_actual = $usuario["Clave"];
$foto_actual = $usuario["Foto"] ?: 'imagenes_usuarios/default.jpg';

// ==================================================
// 2. Procesar POST (guardar cambios)
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ➡️ OBTENER CONTRASEÑA ACTUAL INGRESADA POR EL USUARIO
    $contrasenyaActual = $_POST['actual'] ?? '';
    
    // ➡️ VALIDACIÓN DE LA CONTRASEÑA ACTUAL
    if (!password_verify($contrasenyaActual, $clave_hash_actual)) {
        echo "<div class='error-msg' style='color:red; text-align:center;'>La contraseña actual es incorrecta.</div>";
    } else {

        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $ciudad = trim($_POST['ciudad']);
        $sexo = intval($_POST['sexo']);
        $nacimiento = $_POST['nacimiento'] ?: null;
        $pais = intval($_POST['pais']);

        $nuevaContrasenya = $_POST['contrasenya'] ?? '';
        $repetirContrasenya = $_POST['contrasenya2'] ?? '';

        // Validación de contraseña
        if (!empty($nuevaContrasenya) && $nuevaContrasenya !== $repetirContrasenya) {
            echo "<div class='error-msg' style='color:red; text-align:center;'>Las contraseñas no coinciden.</div>";
        } else {

            // Inicializar variables para el UPDATE
            $sqlCamposAdicionales = "";
            $paramTipos = "";
            $parametros = [];
            $fotoBD = $foto_actual;

            $eliminarFoto = isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == "1";
            $fotoNueva = $_FILES['foto']['name'] ?? null;

            // 1️⃣ Subir nueva foto
            if (!empty($fotoNueva) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($fotoNueva, PATHINFO_EXTENSION));
                $extPermitidas = ['jpg','jpeg','png','gif'];

                if (in_array($extension, $extPermitidas)) {
                    $nuevoNombre = "user_" . $idUsuario . "_" . time() . "." . $extension;
                    $rutaDestino = "imagenes_usuarios/" . $nuevoNombre;

                    if (!is_dir('imagenes_usuarios')) mkdir('imagenes_usuarios', 0755, true);

                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                        if (!empty($foto_actual) && $foto_actual != 'imagenes_usuarios/default.jpg' && file_exists($foto_actual)) {
                            @unlink($foto_actual);
                        }
                        $fotoBD = $rutaDestino;
                    } else {
                        echo "<div class='error-msg'>Error al subir la nueva foto.</div>";
                    }
                } else {
                    echo "<div class='error-msg'>Formato no permitido. Solo jpg, jpeg, png o gif.</div>";
                }
            }
            // 2️⃣ Eliminar foto actual
            else if ($eliminarFoto) {
                if (!empty($foto_actual) && $foto_actual != 'imagenes_usuarios/default.jpg' && file_exists($foto_actual)) {
                    @unlink($foto_actual);
                }
                $fotoBD = 'imagenes_usuarios/default.jpg';
            }

            // Añadir foto al UPDATE
            $sqlCamposAdicionales .= ", Foto = ?";
            $paramTipos .= "s";
            $parametros[] = &$fotoBD;

            // Añadir contraseña al UPDATE si se proporciona
            if (!empty($nuevaContrasenya)) {
                $contrasenya_hasheada = password_hash($nuevaContrasenya, PASSWORD_DEFAULT);
                $sqlCamposAdicionales .= ", Clave = ?";
                $paramTipos .= "s";
                $parametros[] = &$contrasenya_hasheada;
            }

            // UPDATE FINAL
            $sqlUpdate = "UPDATE Usuarios 
                        SET NomUsuario = ?, Email = ?, Ciudad = ?, Sexo = ?, FNacimiento = ?, Pais = ?"
                        . $sqlCamposAdicionales .
                        " WHERE IdUsuario = ?";

            // Tipos y parámetros totales
            $paramTipos = "sssisi" . $paramTipos . "i";
            $parametros = array_merge(
                [&$nombre, &$email, &$ciudad, &$sexo, &$nacimiento, &$pais],
                $parametros,
                [&$idUsuario]
            );

            // Preparar y ejecutar
            $stmt = $conexion->prepare($sqlUpdate);
            if ($stmt) {
                call_user_func_array([$stmt, 'bind_param'], array_merge([$paramTipos], $parametros));

                if ($stmt->execute()) {
                    $_SESSION['usuario'] = $nombre;
                    $_SESSION['login'] = 'ok';
                    $_SESSION['usuario_id'] = $idUsuario;

                    if (isset($_COOKIE['usuario_recordado'])) {
                        $expiry = time() + (90 * 24 * 60 * 60);
                        setcookie("usuario_recordado", $nombre, $expiry, "/", "", false, true);
                    }

                    $stmt->close();
                    $conexion->close();
                    header("Location: respuestaDatos.php");
                    exit();
                } else {
                    echo "<p class='error'>Error al actualizar: " . $stmt->error . "</p>";
                }

                $stmt->close();
            } else {
                echo "<p class='error'>Error preparando consulta: " . $conexion->error . "</p>";
            }
        }
    }
}

// ==================================================
// Cargar lista de países
// ==================================================
$paises = [];
$resPaises = $conexion->query("SELECT IdPais, NomPais FROM Paises ORDER BY NomPais ASC");
while ($fila = $resPaises->fetch_assoc()) {
    $paises[] = $fila;
}
$conexion->close();

// ==================================================
// Determinar foto a mostrar
// ==================================================
$ruta_foto_mostrar = !empty($usuario['Foto']) 
    ? htmlspecialchars($usuario['Foto']) 
    : 'imagenes_usuarios/default.jpg';
?>

<main>
    <h2>Mis datos personales</h2>
    <p>Puedes ver y modificar tus datos.</p>

    <form action="respuestaMisDatos.php" method="post" enctype="multipart/form-data" class="formUsuario">
        <fieldset>
            <legend>Datos de usuario</legend>

            <p>
                <label for="nombre">Nombre de usuario *</label><br>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['NomUsuario']) ?>" maxlength="50" required>
            </p>

            <p>
                <label for="actual">Contraseña actual *</label><br>
                <input type="password" id="actual" name="actual" required>
            </p>

            <p>
                <label for="email">Correo electrónico *</label><br>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['Email']) ?>" maxlength="100" required>
            </p>

            <p>
                <label for="ciudad">Ciudad</label><br>
                <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($usuario['Ciudad'] ?? '') ?>" maxlength="100">
            </p>

            <p>
                <label for="sexo">Sexo *</label><br>
                <select id="sexo" name="sexo" required>
                    <option value="">-- Selecciona --</option>
                    <option value="1" <?= ($usuario['Sexo'] == 1) ? 'selected' : '' ?>>Hombre</option>
                    <option value="2" <?= ($usuario['Sexo'] == 2) ? 'selected' : '' ?>>Mujer</option>
                </select>
            </p>

            <p>
                <label for="nacimiento">Fecha de nacimiento</label><br>
                <input type="date" id="nacimiento" name="nacimiento" value="<?= htmlspecialchars($usuario['FNacimiento'] ?? '') ?>">
            </p>

            <p>
                <label for="contrasenya">Nueva contraseña (opcional)</label><br>
                <input type="password" id="contrasenya" name="contrasenya" maxlength="255">
            </p>

            <p>
                <label for="contrasenya2">Repetir contraseña</label><br>
                <input type="password" id="contrasenya2" name="contrasenya2" maxlength="255">
            </p>

            <p>
                <label for="pais">País *</label><br>
                <select id="pais" name="pais" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= $pais['IdPais'] ?>" <?= ($usuario['Pais'] == $pais['IdPais']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($pais['NomPais'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="foto">Foto de perfil</label><br>
                <input type="file" id="foto" name="foto" accept="image/*"><br>
                <?php if (!empty($usuario['Foto'])): ?>
                    Foto actual:<br>
                    <img src="<?= $ruta_foto_mostrar ?>" alt="Foto de perfil" width="100">
                    <label style="display:block; margin-top:8px;">
                        <input type="checkbox" name="eliminar_foto" value="1">
                        Eliminar foto actual
                    </label>
                <?php endif; ?>
            </p>

            <p>
                <button type="submit">Guardar cambios</button>
                <a href="perfilUsuario.php" class="cancelar">Volver</a>
            </p>

        </fieldset>
    </form>
</main>

<?php
require_once("pie.php");
?>
