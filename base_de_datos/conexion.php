<?php
// conexion.php
$host = "localhost";      
$usuario = "root";          
$contrasena = "";           
$basedatos = "capitulouno";

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $basedatos);

// Revisar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8");
?>
