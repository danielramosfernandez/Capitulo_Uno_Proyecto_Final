<?php
//^Activar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'base_de_datos/conexion.php'; // Conexión a la BD

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    //^Se comprueba si existen administradores 
    $stmt = $conexion->prepare("SELECT id_admin, nombre, email, contraseña, rol FROM administradores WHERE email=? OR nombre=? LIMIT 1");
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $stmt->bind_result($id_admin, $nombre, $email, $hash_password, $rol);

    //^Se verifica que el administrador ha introducido los comentarios de manera correcta
    if ($stmt->fetch()) {
        if (password_verify($password, $hash_password)) {
            $_SESSION["id_admin"] = $id_admin;
            $_SESSION["nombre"] = $nombre;
            $_SESSION["rol"] = $rol;
            //^Si todo es correcto se le redirige al panel de administrador
            header("Location: admin/admin.php");
            exit;
        } else {
            //^Si no se introdujo la contraseña adecuada se notifica 
            $error = "Contraseña incorrecta";
        }
    } else {
        $stmt->close();

        //^En el caso de que no sea admin se comrpueba entonces en usuarios normales
        $stmt = $conexion->prepare("SELECT id_usuario, nombre, email, contraseña FROM usuarios WHERE email=? OR nombre=? LIMIT 1");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $stmt->bind_result($id_usuario, $nombre_usuario, $email_usuario, $hash_password_usuario);

        //^Se verifica que el administrador ha introducido los comentarios de manera correcta
        if ($stmt->fetch()) {
            if (password_verify($password, $hash_password_usuario)) {
                $_SESSION["id_usuario"] = $id_usuario;
                $_SESSION["nombre"] = $nombre_usuario;
                $_SESSION["email"] = $email_usuario;
                //^Si todo es correcto se le redirige al inicio
                header("Location: inicio.php");
                exit;
            } else {
                //^Si no se introdujo la contraseña adecuada se notifica 
                $error = "Contraseña incorrecta";
            }
        } else {
            //^Si el usuario no se encuentra se notifica también
            $error = "Usuario no encontrado";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inicio de sesión</title>
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/login.css">
<link rel="icon" type="image/x-icon" href="multimedia/logo.png">
</head>
<body>

<button id="modo">Oscuro</button>
<a href="index.php" class="close-btn" id="closeBtn" title="Cerrar">&times;</a>

<div class="login-container">
    <h2>Iniciar sesión</h2>

    <?php 
    //^mensaje para en case de que se haya dejado alguna parte vacia
    if (!empty($error)) : 
    ?> 
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <button type="submit">Entrar</button>
    </form>

    <div class="register-link">
        ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
    </div>
</div>

<script>
//?en este script se crea la cruz para enviar al inicio en caso de cierre
document.getElementById('closeBtn').addEventListener('click', function(e) {
    e.preventDefault();
    document.body.classList.add('fade-out');
    setTimeout(() => window.location.href = this.href, 180);
});

//? y aqui se pone la función del modo oscuro
const btn = document.getElementById('modo');
const body = document.body;

if (localStorage.getItem("modo") === "oscuro") {
    body.classList.add("dark-mode");
    btn.textContent = "Claro";
}

btn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    if (body.classList.contains("dark-mode")) {
        btn.textContent = "Claro";
        localStorage.setItem("modo", "oscuro");
    } else {
        btn.textContent = "Oscuro";
        localStorage.setItem("modo", "claro");
    }
});
</script>

</body>
</html>
