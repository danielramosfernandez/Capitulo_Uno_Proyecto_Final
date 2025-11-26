<?php
session_start();


$host = "localhost";
$user = "root";
$pass = "";
$db = "capitulouno";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);
//^Se comprueba si existen administradores 
    $stmt = $conn->prepare("SELECT id_admin, nombre, email, contraseña, rol FROM administradores WHERE email=? OR nombre=? LIMIT 1");
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
//^Se verifica que el administrador ha introducido los comentarios de manera correcta
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["contraseña"])) {
            $_SESSION["id_admin"] = $row["id_admin"];
            $_SESSION["nombre"] = $row["nombre"];
            $_SESSION["rol"] = $row["rol"];
            //^Si todo es correcto se le redirige al panel de administrador
            header("Location: admin/admin.php");
            exit;
        } else {
            //^Si no se introdujo la contraseña adecuada se notifica 
            $error = "Contraseña incorrecta";
        }
    } else {
        //^En el caso de que no sea admin se comrpueba entonces en usuarios normales
        $stmt = $conn->prepare("SELECT id_usuario, nombre, email, contraseña FROM usuarios WHERE email=? OR nombre=? LIMIT 1");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
//^Se verifica que el administrador ha introducido los comentarios de manera correcta
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["contraseña"])) {
                $_SESSION["id_usuario"] = $row["id_usuario"];
                $_SESSION["nombre"] = $row["nombre"];
                $_SESSION["email"] = $row["email"];
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
        $stmt->close();
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

<style>
/** El css fue introducido directamente en este archivo sin falta de enlazar al archivo de css*/
body {
    font-family: 'Raleway', sans-serif;
    background-color: #e8e1d6;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    position: relative;
}
.login-container {
    background: #fff;
    padding: 35px 40px;
    border-radius: 10px;
    border: 6px solid #d4a85c;
    width: 360px;
    box-shadow: 0 0 20px rgba(0,0,0,0.15);
    position: relative;
}
.login-container h2 {margin-bottom: 20px;font-weight:700;color:#5a4634;text-align:center;}
.login-container label {display:block;margin-bottom:8px;font-weight:500;color:#5a4634;}
.login-container input[type="text"], .login-container input[type="password"] {width:100%;padding:10px;margin-bottom:18px;border:1.5px solid #c8b28c;border-radius:6px;font-size:16px;transition:border-color 0.3s, box-shadow 0.3s;}
.login-container input[type="text"]:focus, .login-container input[type="password"]:focus {border-color:#d4a85c;box-shadow:0 0 4px rgba(212,168,92,0.4);outline:none;}
.login-container button {width:100%;padding:12px;background-color:#d4a85c;border:none;border-radius:6px;color:#fff;font-weight:700;font-size:16px;cursor:pointer;transition:background-color 0.3s;}
.login-container button:hover {background-color:#b98e4a;}
.register-link {text-align:center;margin-top:15px;}
.register-link a {color:#b98e4a;text-decoration:none;font-weight:600;}
.register-link a:hover {text-decoration:underline;}
.close-btn {position:absolute;top:25px;right:30px;width:36px;height:36px;background-color:#d4a85c;color:white;font-size:24px;font-weight:bold;border-radius:50%;text-align:center;line-height:36px;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.2);transition:transform 0.3s ease, background-color 0.3s;z-index:10;}
.close-btn:hover {background-color:#b98e4a;transform:scale(1.1);}
#modo {position:absolute;top:25px;left:30px;padding:8px 16px;background-color:#d4a85c;color:white;border:none;border-radius:20px;cursor:pointer;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,0.2);transition:background-color 0.3s, transform 0.3s;}
#modo:hover {background-color:#b98e4a;transform:scale(1.05);}
.fade-out {animation: fadeOutPage 0.5s forwards;}
@keyframes fadeOutPage {to {opacity:0;transform:translateX(100%);}}
.dark-mode {background-color:#3a3a3a !important;color:#fff !important;}
.dark-mode .login-container {background-color:#444 !important;color:#fff !important;}
.dark-mode .login-container label {color:#fff;}
.dark-mode input {background-color:#555;color:#fff;border-color:#777;}
.dark-mode input:focus {border-color:#d4a85c;box-shadow:0 0 4px rgba(212,168,92,0.6);}
.dark-mode .register-link a {color:#d4a85c;}
.dark-mode #modo {background-color:#d4a85c;color:#fff;}
.error-msg {color:#cc3333;font-weight:600;text-align:center;margin-bottom:12px;}
</style>
</head>
<body>

<button id="modo">Oscuro</button>
<a href="inicio.php" class="close-btn" id="closeBtn" title="Cerrar">&times;</a>

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
