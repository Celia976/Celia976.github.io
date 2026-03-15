<?php
//Funcion para filtrar y validar los datos del usuario en registro y mis datos
function filtrarDatosUsuario($modo, $post, $archivos)
{
    $errores = [];
    $datos = [];

    // =============================
    // NOMBRE
    // =============================
    $nombre = trim($post['nombre'] ?? "");
    if ($modo === "registro" || $modo === "misdatos") {
        if ($nombre === "") {
            $errores[] = "El nombre de usuario no puede estar vacío.";
        } elseif (strlen($nombre) < 3 || strlen($nombre) > 15) {
            $errores[] = "El nombre de usuario debe tener entre 3 y 15 caracteres.";
        } elseif (is_numeric($nombre[0])) {
            $errores[] = "El nombre de usuario no puede comenzar con un número.";
        } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9]{2,14}$/', $nombre)) {
            $errores[] = "El nombre de usuario solo puede contener letras del alfabeto inglés y números.";
        }
        $datos['nombre'] = $nombre;
    }

    // =============================
    // CONTRASEÑA
    // =============================
    $pass = trim($post['contrasenya'] ?? "");

    if ($modo === "registro") {
        $rep = trim($post['rep'] ?? "");

        if ($pass === "") {
            $errores[] = "La contraseña no puede estar vacía.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d_-]{6,15}$/', $pass)) {
            $errores[] = "La contraseña debe tener entre 6 y 15 caracteres, incluir mayúscula, minúscula y número.";
        }

        if ($rep === "" || $rep !== $pass) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        $datos['contrasenya'] = $pass;

    } else {  
        // —— MIS DATOS ——
        $rep = trim($post['contrasenya2'] ?? "");

        if ($pass !== "") {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d_-]{6,15}$/', $pass)) {
                $errores[] = "La nueva contraseña no cumple los requisitos.";
            }
            if ($rep !== $pass) {
                $errores[] = "Las contraseñas no coinciden.";
            }
            $datos['contrasenya'] = $pass;
        } else {
            $datos['contrasenya'] = "";
        }
    }

    // =============================
    // EMAIL
    // =============================
    $email = trim($post['email'] ?? $post['dir'] ?? "");
    if ($email === "") {
        $errores[] = "El correo electrónico no puede estar vacío.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido.";
    }
    $datos['email'] = $email;

    // =============================
    // SEXO
    // =============================
    $sexo = (int)($post['sexo'] ?? 0);
    if ($sexo !== 1 && $sexo !== 2) {
        $errores[] = "El sexo debe ser 1 u 2.";
    }
    $datos['sexo'] = $sexo;

    // =============================
    // FECHA NACIMIENTO
    // =============================
    if ($modo === "registro") {
        $fecha = trim($post['fecha'] ?? "");
        if ($fecha === "") {
            $errores[] = "Debe introducir su fecha de nacimiento.";
        } else {
            $partes = explode("/", $fecha);
            if (count($partes) === 3) {
                [$dia, $mes, $anio] = $partes;

                if (!checkdate($mes, $dia, $anio)) {
                    $errores[] = "La fecha de nacimiento no es válida.";
                } else {
                    $hoy = new DateTime();
                    $nac = new DateTime("$anio-$mes-$dia");

                    if ($hoy->diff($nac)->y < 18) {
                        $errores[] = "Debes ser mayor de edad.";
                    }
                    $datos['nacimiento'] = $nac->format("Y-m-d");
                }
            }
        }
    } else {
     
        $datos['nacimiento'] = $post['nacimiento'] ?? null;
    }

    // =============================
    // CIUDAD
    // =============================
    $datos['ciudad'] = trim($post['ciudad'] ?? "");

    // =============================
    // PAÍS
    // =============================
    $pais = $post['pais'] ?? "";
    $datos['pais'] = is_numeric($pais) ? (int)$pais : null;

    // =============================
    // FOTO
    // =============================
    $datos['foto'] = $archivos['foto'] ?? null;
    $datos['eliminar_foto'] = isset($post["eliminar_foto"]) && $post["eliminar_foto"] == "1";

    return [$errores, $datos];
}
