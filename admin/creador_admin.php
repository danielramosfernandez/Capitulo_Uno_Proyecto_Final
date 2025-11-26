<?php
echo "<link rel='stylesheet' href='estilos_admin.css'>";

echo "<div class='install-container'>";

$host = "localhost";
$user = "root";
$pass = "";
$db   = "capitulouno";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<p style='color:red;'>Error de conexión: " . $conn->connect_error . "</p>");
}

echo "<h2>Instalador de Administrador - CapituloUno</h2>";

$result = $conn->query("SELECT COUNT(*) AS total FROM administradores");
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo "<p style='color:green;'>Ya existe al menos un administrador. No es necesario crear otro.</p>";
    echo "</div>";
    exit;
}


$nombre = "Admin";
$email  = "admin@capitulouno.com";
$password_plano = "AdminAdmin";
$password_hash = password_hash($password_plano, PASSWORD_DEFAULT);
$rol = "superadmin";

$stmt = $conn->prepare("INSERT INTO administradores (nombre, email, contraseña, rol) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre, $email, $password_hash, $rol);

if ($stmt->execute()) {
    echo "<p style='color:green;'>Administrador creado exitosamente.</p>";
    echo "<p><b>Email:</b> $email</p>";
    echo "<p><b>Contraseña:</b> $password_plano</p>";
    echo "<p><i>(Puedes cambiarla luego desde el panel)</i></p>";
} else {
    echo "<p style='color:red;'>Error creando el administrador: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

echo "</div>"; 

echo "<hr><p class='install-footer-info'><b>IMPORTANTE:</b> Borra este archivo <code>install.php</code> después de usarlo.</p>";
?>
