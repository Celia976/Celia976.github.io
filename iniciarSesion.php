<?php
session_start();
$logueado = isset($_SESSION['login']) && $_SESSION['login'] === 'ok';


$encabezado = "REGISTRATE COMO NUEVO USUARIO";
$style = "index.css";
$style2 = "comunFormularios.css";
$style3 = "coloresPredeterminados.css";
require_once("cabecera.php");
require_once("conexionBD.php");
?>

<main>
        <form id="formRegistro" action="respuestaRegistro.php" method="post" enctype="multipart/form-data" class="datos">

            <fieldset>
                <legend>Introduce tus datos...</legend>

             <p>
            <label for="nombre">Nombre de usuario:<span class="obligatorio">*</span></label>
            <input type="text" id="nombre" name="nombre" placeholder = "Usa entre 3-15 caracteres. No puede empezar por número.">
            </p>


             <p>
            <label for="contrasenya">Contraseña:<span class="obligatorio">*</span></label>
            <input type="password" id="contrasenya" name="contrasenya" placeholder = "Usa entre 6-15 caracteres. Con mayuscula, minuscula y numeros.">
            </p>


             <p>
            <label for="rep">Repetir contraseña:<span class="obligatorio">*</span></label>
            <input type="password" id="rep" name="rep">
            </p>


             <p>
            <label for="dir">Dirección de email:<span class="obligatorio">*</span></label>
            <input type="text" id="dir" name="dir" placeholder="Ejemplo: usuario@mail.com">
            </p>


             <p>
            <label for="sexo">Sexo:<span class="obligatorio">*</span></label>
            <select id="sexo" name="sexo">
                <option value="">--Selecciona--</option>
                <option value="1">Hombre</option>
                <option value="2">Mujer</option>
            </select>
            </p>

             <p>
            <label for="fecha">Fecha de nacimiento:<span class="obligatorio">*</span></label>
            <input type="text" id="fecha" name="fecha" placeholder="dd/mm/aaaa">
            </p>


            <p>
            <label for="ciudad">Ciudad:</label>
            <input type="text" id="ciudad" name="ciudad" placeholder="Ej: Madrid">
            </p>


            <p>
            <label for="pais">País de residencia:</label>
                <select id="pais" name="pais">
                    <option value="">--Selecciona--</option>
                    <?php
                    // =========================================================
                    // INICIO: CARGA DINÁMICA DE PAÍSES
                    // =========================================================
                    
                    global $conexion;
                    
                    if ($conexion) {
                        $sql = "SELECT IdPais, NomPais FROM paises ORDER BY NomPais ASC";
                        $resultado = $conexion->query($sql);
                        
                        if ($resultado && $resultado->num_rows > 0) {
                            while ($fila = $resultado->fetch_assoc()) {
                                $id = htmlspecialchars($fila['IdPais']);
                                $nombre_pais = htmlspecialchars($fila['NomPais']);
                                echo "<option value=\"$id\">$nombre_pais</option>";
                            }
                            $resultado->free(); // Liberar el resultado
                        } else {
                            // En caso de error o que no haya países
                            echo "<option value=\"\" disabled>Error al cargar países</option>";
                        }
                    } else {
                        echo "<option value=\"\" disabled>Error de conexión a BD</option>";
                    }
                    // =========================================================
                    // CARGA DINÁMICA DE PAÍSES
                    // =========================================================
                    ?>
                </select>
            </p>

            <p class="fotos">
                <label for="foto">Foto de perfil:</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </p>

            <button type="submit" class="boton">Registrarse</button>

            <p class="acciones">
                <button type="button" onclick="location.href='index.php'" class="cancelar"> Cancelar Registro</button>
                ¿Ya tienes una cuenta creada?
                <a href = "formularioAcceso.php" class="iniciar">INICIAR SESIÓN</a>
            </p>

            </fieldset>
        </form>

    </main>

<?php
require_once("pie.php");
?>