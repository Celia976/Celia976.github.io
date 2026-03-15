<?php
require_once("controlSesion.php");
require_once("conexionBD.php");

// ==========================================================
// VALIDAR USUARIO A MOSTRAR
// ==========================================================
if (isset($_GET['usuario'])) {
    $usuarioPerfil = $_GET['usuario'];
} elseif (isset($_SESSION['usuario'])) {
    $usuarioPerfil = $_SESSION['usuario']; // usuario que inició sesión
} else {
    header("Location: index.php");
    exit;
}

// ==========================================================
// OBTENER DATOS DEL USUARIO
// ==========================================================
$sql = "SELECT u.IdUsuario, u.NomUsuario, u.FRegistro, u.Email, u.Sexo, u.FNacimiento, 
        u.Ciudad, p.NomPais AS PaisNombre, u.Foto
        FROM usuarios u
        LEFT JOIN paises p ON u.Pais = p.IdPais
        WHERE u.NomUsuario = ?";
        
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuarioPerfil);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$usuario = $result->fetch_assoc(); 
$usuario['Ciudad'] = (string) $usuario['Ciudad'];

// Convertir sexo a texto
$sexos = [
    1 => 'Masculino',
    2 => 'Femenino'
];
$usuario['SexoTexto'] = $sexos[$usuario['Sexo']] ?? 'No especificado';

// ==========================================================
// OBTENER ANUNCIOS DEL USUARIO
// ==========================================================
$sql_anuncios = "SELECT IdAnuncio, Titulo, Precio, Ciudad 
                 FROM anuncios 
                 WHERE Usuario = ? 
                 ORDER BY FRegistro DESC";
$stmt_anuncios = $conexion->prepare($sql_anuncios);
$stmt_anuncios->bind_param("i", $usuario['IdUsuario']);
$stmt_anuncios->execute();
$result_anuncios = $stmt_anuncios->get_result();

// ==========================================================
// CABECERA
// ==========================================================
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';
$nombreUsu = $_SESSION['usuario'] ?? '';

$encabezado = "PERFIL DE USUARIO DE " . htmlspecialchars(strtoupper($usuario['NomUsuario']));
$style = "perfil.css";
$style2 = "coloresPredeterminados.css";
require_once("cabecera.php");
?>

<main>
    <aside class="perfil-usuario">
        <header class="avatar">
            <img src="<?= htmlspecialchars($usuario['Foto'] ?? 'imagenes_usuarios/default.jpg') ?>" alt="Foto de perfil">
        </header>

        <article class="info-usuario">
            <h2><?= htmlspecialchars($usuario['NomUsuario']) ?></h2>
            <?php if ($logueado): ?>
                <p><strong>Email: </strong><?= htmlspecialchars($usuario['Email']) ?></p>
                <p><strong>Sexo: </strong><?= htmlspecialchars($usuario['SexoTexto']) ?></p>
                <p><strong>Fecha de nacimiento: </strong><?= htmlspecialchars($usuario['FNacimiento']) ?></p>
                <p><strong>Ciudad: </strong><?= htmlspecialchars($usuario['Ciudad'] ?: 'No especificada') ?></p>
                <p><strong>País de residencia: </strong><?= htmlspecialchars($usuario['PaisNombre']) ?></p>
            <?php endif; ?>
            <p><strong>Fecha de incorporación: </strong><?= htmlspecialchars($usuario['FRegistro']) ?></p>
        </article>

        <section class="anuncios-usuario">
            <h3>Anuncios de <?= htmlspecialchars($usuario['NomUsuario']) ?></h3>
            <?php if ($result_anuncios->num_rows > 0): ?>
                <ul>
                    <?php while ($anuncio = $result_anuncios->fetch_assoc()): ?>
                        <li>
                            <strong><?= htmlspecialchars($anuncio['Titulo']) ?></strong>
                            - <?= htmlspecialchars($anuncio['Ciudad']) ?>
                            - <?= htmlspecialchars($anuncio['Precio']) ?> €
                            <a href="verAnuncio.php?id=<?= $anuncio['IdAnuncio'] ?>">Ver</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Este usuario no tiene anuncios publicados.</p>
            <?php endif; ?>
        </section>
    </aside>

    <?php if ($logueado): ?>
    <nav class="menu-perfil">
        <ul>
            <li><a href="eligeTuEstilo.php">Cambiar Estilo de Página</a></li>
            <li><a href="misDatos.php">Modificar mis datos</a></li>
            <li><a href="confirmarBaja.php">Darse de baja</a></li>
            <li><a href="misAnuncios.php">Visualizar mis anuncios</a></li>
            <li><a href="crearAnuncio.php">Crear un anuncio nuevo</a></li>
            <li><a href="anadirFotoAnuncio.php">Añadir foto Anuncio</a></li>
            <li><a href="misMensajes.php">Visualizar mensajes enviados y recibidos</a></li>
            <li><a href="solicitarFolleto.php">Solicitar folleto publicitario impreso</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
            <li><a href="index.php">Volver atrás</a></li>
        </ul>
    </nav>
    <?php endif; ?>
</main>

<?php
require_once("pie.php");
?>