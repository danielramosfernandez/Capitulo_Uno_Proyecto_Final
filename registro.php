    <?php
    session_start();

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "capitulouno";
    //^Se realiza la conexión con la base de datos
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $error = "";
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $usuario = trim($_POST["usuario"]);
        $email = trim($_POST["email"]);
        $tipo_suscripcion = $_POST['tipo_suscripcion'] ?? 'gratuita';
        $password = $_POST["password"];
        $password2 = $_POST["password2"];

        //^Se comprueba que las dos contraseñas introducidas coincidan
        if ($password !== $password2) {
            $error = "Las contraseñas no coinciden.";
        } else {
    
        
            //^Se comprueba si el usuario o mail ya existan
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE nombre = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $usuario, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "El usuario o email ya están registrados.";
            } else {
                //^Se inserta el usuario
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre, email, tipo_suscripcion, contraseña) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $usuario, $email, $tipo_suscripcion, $hash_password);

           if ($stmt_insert->execute()) {
    //^Una vez creado el usuario es reenviado a la zona de inicio de sesión
    header("Location: login.php");
    exit;
} else {
    //^Si hay eror se le avisa para relizar otro intentoi
    $error = "Error al registrar. Intenta de nuevo.";
}

                $stmt_insert->close();
            }
            $stmt->close();
        }
    }

    $conn->close();
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

    <a href="inicio.php" class="close-btn" id="closeBtn" title="Cerrar">&times;</a>

    <div class="register-container">
        <h2>Registro</h2>

        <?php
        //^Se muestra error si algun campo esta vacio 
            if(!empty($error)) 
        : ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <?php if(!empty($success)) : ?>
            <div class="success-msg"><?= $success ?></div>
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

    <script>
        //?De nuevo en esta página el modo del tema es directamente intorducido
    document.getElementById('closeBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.body.classList.add('fade-out');
        setTimeout(() => window.location.href = this.href, 180);
    });

 
    const body = document.body;
    if(localStorage.getItem('modo') === 'oscuro') {
        body.classList.add('dark-mode');
    }
    </script>

    </body>
    </html>
