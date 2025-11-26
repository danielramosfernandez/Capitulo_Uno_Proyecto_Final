<?php
session_start();
include 'base_de_datos/conexion.php';

//^verificamos que el usuario ha iniciado sesión
if(!isset($_SESSION['id_usuario'])){
    echo "<p style='text-align:center;margin-top:50px;font-size:1.5rem;'>Debes iniciar sesión para ver tu perfil.</p>";
    echo "<p style='text-align:center;'><a href='login.php'>Iniciar sesión</a></p>";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

//^Se obtiene la información del usuario que tiene la sesión iniciada para revisar su información
$stmt = $conexion->prepare("SELECT nombre, email, tipo_suscripcion, fecha_registro FROM usuarios WHERE id_usuario=?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();

//^Extraemos el historial de compras
$stmt = $conexion->prepare("
    SELECT c.id_compra, l.titulo, c.fecha_compra, c.precio_pagado
    FROM compras c
    JOIN libros l ON c.id_libro = l.id_libro
    WHERE c.id_usuario=?
    ORDER BY c.fecha_compra DESC
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

//^También extraemos las fechas de principio y fin de suscripción
$fecha_inicio = date("d/m/Y", strtotime($usuario['fecha_registro']));
$fecha_fin = date("d/m/Y", strtotime("+1 month", strtotime($usuario['fecha_registro'])));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perfil de Usuario - CapituloUno</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perfil de Usuario - CapituloUno</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/** Estilos generales */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #e8e1d6;
  color: #000;
  display: flex;
  flex-direction: column;
}

main { flex: 1; }

nav.site-header { background-color: #d4a85c; }
nav a { color: #f8f9fa; text-decoration: none; transition: color 0.3s; }
nav a:hover { color: #000; }

footer { background-color: #a18262; color: #fff; text-align: center; padding: 15px 0; }

.perfil-main { flex: 1; background-color: #e8e1d6; padding: 60px 20px; }
.perfil-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px;
  background-color: #ffffff;
  border-radius: 25px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}
.perfil-header { text-align: center; margin-bottom: 40px; }
.perfil-header h2 { font-size: 2.4rem; color: #5a4634; font-weight: 700; }
.perfil-section {
  background-color: #ffffff;
  border-radius: 25px;
  padding: 30px 25px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
  overflow-x: auto;
  transition: transform 0.3s, box-shadow 0.3s;
}
.perfil-section:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
.perfil-section h3 { font-size: 2rem; color: #5a4634; margin-bottom: 20px; font-weight: 600; }


.perfil-section table { width: 100%; border-collapse: collapse; font-size: 1rem; min-width: 500px; }
.perfil-section th, .perfil-section td { padding: 12px 15px; text-align: center; border-bottom: 1px solid #ddd; }
.perfil-section th { background-color: #d4a85c; color: white; font-weight: 600; text-transform: uppercase; }
.perfil-section tr:hover { background-color: #f0e6d1; }
.perfil-section td { color: #333; }


button, .btn-vermas, .btn-cart, .btn-actualizar, .btn-eliminar, .btn-finalizar { border-radius: 6px; border: none; cursor: pointer; transition: 0.3s; }
button:hover, .btn-vermas:hover, .btn-cart:hover, .btn-actualizar:hover, .btn-eliminar:hover, .btn-finalizar:hover { opacity: 0.85; }
.register-container, .subscription-form input { background-color: #fff; color: #000; border: 1px solid #ccc; border-radius: 5px; padding: 10px; }
.register-container label, .subscription-form label { color: #555; }


.dark-mode body { background-color: #2c2c2c !important; color: #f0f0f0 !important; }
.dark-mode nav.site-header { background-color: #222 !important; }
.dark-mode nav a { color: #f8f8f8 !important; }
.dark-mode nav a:hover { color: #d4a85c !important; }
.dark-mode footer { background-color: #222 !important; color: #fff !important; }
.dark-mode .perfil-main { background-color: #2c2c2c !important; }
.dark-mode .perfil-container { background-color: #333 !important; color: #f0f0f0 !important; }
.dark-mode .perfil-section { background-color: #333 !important; color: #f0f0f0 !important; }
.dark-mode .perfil-section h3,
.dark-mode .perfil-header h2,
.dark-mode .section-title,
.dark-mode .opiniones h2 { color: #f0f0f0 !important; }

.dark-mode .perfil-section table,
.dark-mode .perfil-section table th,
.dark-mode .perfil-section table td { background-color: #ffffff !important; color: #000 !important; border-color: #ccc !important; }
.dark-mode .perfil-section table tbody tr:hover { background-color: #f9f9f9 !important; }


.dark-mode .btn-actualizar,
.dark-mode .btn-vermas,
.dark-mode .btn-cart,
.dark-mode .btn-eliminar,
.dark-mode .btn-finalizar { background-color: #b78c47 !important; color: #fff !important; }


.dark-mode .register-container,
.dark-mode .subscription-form input { background-color: #444 !important; color: #f0f0f0 !important; border-color: #666 !important; }
.dark-mode .register-container label,
.dark-mode .subscription-form label { color: #fff !important; }

@media (max-width: 768px) {
  .perfil-main { padding: 20px 10px; }
  .perfil-section { padding: 20px 15px; }
  .perfil-section h3 { font-size: 1.6rem; }
  .perfil-section table { font-size: 0.9rem; }
}
</style>


<link href="css/estilos.css" rel="stylesheet">
  <link rel="stylesheet" href="css/nav_dark.css">

</head>
<body>
<nav class="navbar navbar-expand-md sticky-top py-1 site-header">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    

    <a class="py-2" href="inicio.php">
      <img src="multimedia/logo.png" alt="Logo" width="70" height="70" class="d-block mx-auto">
    </a>

   
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>


    <div class="collapse navbar-collapse justify-content-md-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="inicio.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="catalogo.php">Catálogo</a></li>
        <li class="nav-item"><a class="nav-link" href="suscripcion.php">Pásate a premium</a></li>

        <?php if(isset($_SESSION['id_usuario'])): ?>
          <li class="nav-item"><a class="nav-link" href="perfil.php">Mi Perfil</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar sesión</a></li>
          <li class="nav-item">
            <a class="nav-link" href="carrito.php">
              <img class="carro" src="multimedia/carrito.png" alt="Carrito" width="40" height="40">
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Inicio de sesión</a></li>
        <?php endif; ?>

 
        <li class="nav-item">
          <button id="modo" class="btn btn-outline-light ms-2">Oscuro</button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="perfil-main">
  <div class="perfil-container">
    <div class="perfil-header">
      <h2>Mi Perfil</h2>
    </div>

<!--Se muestrán los datos del usuario  -->
    <div class="perfil-section">
      <h3>Datos del Usuario</h3>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
      <p><strong>Suscripción:</strong> <?= htmlspecialchars(ucfirst($usuario['tipo_suscripcion'])) ?></p>
      <p><strong>Registro:</strong> <?= $fecha_inicio ?></p>
    </div>

    <!--Se nuestran sus suscripciones -->
    <div class="perfil-section suscripciones">
      <h3>Mis Suscripciones</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Plan</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?= ucfirst($usuario['tipo_suscripcion']) ?></td>
            <td><?= $fecha_inicio ?></td>
            <td><?= $fecha_fin ?></td>
            <td>Activo</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!--Se muestra el historial de compras -->
    <div class="perfil-section historial">
      <h3>Historial de Pedidos</h3>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Pedido #</th>
            <th>Libro</th>
            <th>Fecha</th>
            <th>Precio</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($historial) === 0): ?>
            <tr><td colspan="5">No has realizado compras aún.</td></tr>
          <?php else: ?>
            <?php foreach($historial as $pedido): ?>
            <tr>
              <td><?= $pedido['id_compra'] ?></td>
              <td><?= htmlspecialchars($pedido['titulo']) ?></td>
              <td><?= date("d/m/Y", strtotime($pedido['fecha_compra'])) ?></td>
              <td>$<?= number_format($pedido['precio_pagado'],2) ?></td>
              <td>Entregado</td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</main>

<footer class="text-white text-center py-3" style="background-color: #a18262;">
  <p class="mb-0">2025 CapituloUno</p>
</footer>


<script src="javascript/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

</body>
</html>
