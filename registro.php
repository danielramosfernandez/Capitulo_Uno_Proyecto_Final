<?php
session_start();
include 'base_de_datos/conexion.php'; // Conexión a la BD

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $email = trim($_POST["email"]);
    $tipo_suscripcion = $_POST['tipo_suscripcion'] ?? 'gratuita';
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    /* ========================== */
    /*    VALIDACIÓN EN PHP      */
    /* ========================== */

    if (strlen($usuario) < 3) {
        $error = "El nombre debe tener al menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener mínimo 6 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {

        // Comprobar usuario/email duplicado
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE nombre = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $usuario, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "El usuario o email ya están registrados.";
        } else {

            // Insertar usuario
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conexion->prepare(
                "INSERT INTO usuarios (nombre, email, tipo_suscripcion, contraseña) VALUES (?, ?, ?, ?)"
            );
            $stmt_insert->bind_param("ssss", $usuario, $email, $tipo_suscripcion, $hash_password);

            if ($stmt_insert->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Error al registrar. Intenta de nuevo.";
            }

            $stmt_insert->close();
        }

        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro - CapituloUno</title>

<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/registro.css">
<link rel="icon" type="image/x-icon" href="multimedia/logo.png">
</head>
<body>

<a href="index.php" class="close-btn" id="closeBtn" title="Cerrar">&times;</a>

<div class="register-container">
    <h2>Registro</h2>

    <?php if (!empty($error)) : ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="tipo_suscripcion">Tipo de cuenta</label>
        <select id="tipo_suscripcion" name="tipo_suscripcion" required>
            <option value="gratuita">Estándar</option>
            <option value="premium">Premium</option>
        </select>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <label for="password2">Confirmar contraseña</label>
        <input type="password" id="password2" name="password2" required>

        <button type="submit">Registrarse</button>
    </form>
</div>

<!-- ========================== -->
<!--     VALIDACIÓN JS         -->
<!-- ========================== -->

<script>
// Validar cierre con animación
document.getElementById('closeBtn').addEventListener('click', function(e) {
    e.preventDefault();
    document.body.classList.add('fade-out');
    setTimeout(() => window.location.href = this.href, 180);
});

// Validación rápida en el navegador
document.querySelector("form").addEventListener("submit", function(e) {
    const usuario = document.getElementById("usuario").value.trim();
    const email = document.getElementById("email").value.trim();
    const pass = document.getElementById("password").value;
    const pass2 = document.getElementById("password2").value;

    if (usuario.length < 3) {
        alert("El nombre debe tener al menos 3 caracteres");
        e.preventDefault();
        return;
    }

    // Email válido
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Introduce un email válido");
        e.preventDefault();
        return;
    }

    if (pass.length < 6) {
        alert("La contraseña debe tener mínimo 6 caracteres");
        e.preventDefault();
        return;
    }

    if (pass !== pass2) {
        alert("Las contraseñas no coinciden");
        e.preventDefault();
    }
});

// Modo oscuro
if (localStorage.getItem("modo") === "oscuro") {
    document.body.classList.add("dark-mode");
}
</script>

</body>
</html>
