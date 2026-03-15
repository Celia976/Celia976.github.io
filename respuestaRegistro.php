<?php
session_start();
require_once("conexionBD.php");
require_once("filtrosUsuario.php");

$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';

// Estilos y cabecera
$encabezado = "REGÍSTRATE COMO NUEVO USUARIO";
$style = "index.css";
$style2 = "comunFormularios.css";
$style3 = "coloresPredeterminados.css";
require_once("cabecera.php");

// =====================================================
// FILTRADO DE DATOS
// =====================================================
list($errores, $datos) = filtrarDatosUsuario("registro", $_POST, $_FILES);

// Variables ya filtradas y seguras
$nombre = $datos["nombre"];
$contrasenya = $datos["contrasenya"];
$dir = $datos["email"];
$sexoSQL = $datos["sexo"];
$fechaSQL = $datos["nacimiento"];   // viene lista en formato Y-m-d
$ciudadSQL = $datos["ciudad"] ?: null;
$paisSQL = $datos["pais"];
$foto_subida = "";

// =====================================================
// SI NO HAY ERRORES → GUARDAR EN BD
// =====================================================
if (count($errores) === 0) {

    // Contraseña con hash
    $clave_hash = password_hash($contrasenya, PASSWORD_DEFAULT);

    // Foto por defecto temporalmente
    $foto_url = "imagenes_usuarios/default.jpg";

    // INSERT del usuario
    $sql = "INSERT INTO usuarios 
            (NomUsuario, Clave, Email, Sexo, FNacimiento, Ciudad, Pais, Foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "sssissis",
            $nombre,
            $clave_hash,
            $dir,
            $sexoSQL,
            $fechaSQL,
            $ciudadSQL,
            $paisSQL,
            $foto_url
        );

        if ($stmt->execute()) {

            $idUsuario = $stmt->insert_id;

            // =====================================================
            // MANEJO DE FOTO SUBIDA
            // =====================================================
            if (!empty($datos["foto"]) && $datos["foto"]["error"] === UPLOAD_ERR_OK) {

                $foto = $datos["foto"];
                $extension = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $permitidas)) {

                    if (!is_dir('imagenes_usuarios')) {
                        mkdir('imagenes_usuarios', 0755, true);
                    }

                    $nuevoNombre = "user_" . $idUsuario . "_" . time() . "." . $extension;
                    $rutaDestino = "imagenes_usuarios/" . $nuevoNombre;

                    if (move_uploaded_file($foto["tmp_name"], $rutaDestino)) {

                        $foto_url = $rutaDestino;

                        // Actualizamos la foto real
                        $stmtFoto = $conexion->prepare("UPDATE usuarios SET Foto = ? WHERE IdUsuario = ?");
                        $stmtFoto->bind_param("si", $foto_url, $idUsuario);
                        $stmtFoto->execute();
                        $stmtFoto->close();

                        $foto_subida = "Foto subida correctamente.";

                    } else {
                        $foto_subida = "Error al mover la foto al servidor.";
                    }

                } else {
                    $foto_subida = "Formato de imagen no permitido.";
                }

            } else {
                $foto_subida = "Se usó la foto por defecto.";
            }

        } else {
            $errores[] = "Error al guardar el usuario: " . $stmt->error;
        }

        $stmt->close();

    } else {
        $errores[] = "Error al preparar la consulta: " . $conexion->error;
    }
}

// =====================================================
// MAPEAR SEXO Y PAÍS
// =====================================================

$sexos = [
    1 => "Hombre",
    2 => "Mujer"
];

$sexoTexto = $sexos[$sexoSQL] ?? "No especificado";

// Nombre del país desde BD
$paisTexto = "No especificado";

if (!empty($paisSQL)) {
    $stmtPais = $conexion->prepare("SELECT NomPais FROM paises WHERE IdPais = ?");
    $stmtPais->bind_param("i", $paisSQL);
    $stmtPais->execute();
    $r = $stmtPais->get_result();
    if ($fila = $r->fetch_assoc()) {
        $paisTexto = $fila["NomPais"];
    }
    $stmtPais->close();
}

$conexion->close();
?>

<main>
<section class="resultadoRegistro">

    <?php if (count($errores) > 0): ?>

        <h2>Se han encontrado errores en el registro:</h2>
        <ul>
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>

        <p><a href="iniciarSesion.php" class="botonVolver">Volver al formulario</a></p>

    <?php else: ?>

        <h2>Registro completado con éxito</h2>

        <article class="datosRegistro">

            <p>Nombre de usuario: <strong><?= htmlspecialchars($nombre) ?></strong></p>
            <p>Contraseña: <strong><?= str_repeat('•', strlen($contrasenya)) ?></strong></p>
            <p>Correo electrónico: <strong><?= htmlspecialchars($dir) ?></strong></p>
            <p>Sexo: <strong><?= htmlspecialchars($sexoTexto) ?></strong></p>
            <p>Fecha de nacimiento: <strong><?= htmlspecialchars($fechaSQL) ?></strong></p>
            <p>Ciudad: <strong><?= htmlspecialchars($ciudadSQL ?: '') ?></strong></p>
            <p>País de residencia: <strong><?= htmlspecialchars($paisTexto) ?></strong></p>

            <p><strong>Foto de perfil:</strong></p>
            <img src="<?= htmlspecialchars($foto_url) ?>" 
                 alt="Foto de <?= htmlspecialchars($nombre) ?>" 
                 style="max-width:150px; border-radius:8px;">
            <p><?= htmlspecialchars($foto_subida) ?></p>

        </article>

        <p><a href="formularioAcceso.php" class="botonVolver">Ir a Iniciar Sesión</a></p>

    <?php endif; ?>

</section>
</main>

<?php require_once("pie.php"); ?>
