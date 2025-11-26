<?php
session_start();
include 'base_de_datos/conexion.php';

//^Se realiza la consulta de todos los libros
$sqlTodos = "SELECT * FROM libros"; 
$resultTodos = $conexion->query($sqlTodos);

//^Se realiza la consulta de los libros de categoria estandar 
$sqlEstandar = "SELECT * FROM libros WHERE tipo_libro='estandar'";
$resultEstandar = $conexion->query($sqlEstandar);

//^Se realiza la consulta de los libros de categoria premium
$sqlPremium = "SELECT * FROM libros WHERE tipo_libro='premium'";
$resultPremium = $conexion->query($sqlPremium);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo de Libros</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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




<section class="catalogo py-5">
  <div class="container">
    <h2 class="text-center mb-4">Catálogo de Libros</h2>

    <ul class="nav nav-tabs justify-content-center mb-4" id="catalogoTabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#todos">Todos</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#estandar">Estándar</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#premium">Premium</button></li>
    </ul>

    <div class="tab-content" id="catalogoContent">
      
      <!--Sección de todos los libros -->
      <div class="tab-pane fade show active" id="todos">
        <div class="row g-4">
          <?php while($libro = $resultTodos->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6">
              <div class="card h-100 book-card">
                <img src="<?= $libro['imagen'] ?>" alt="<?= $libro['titulo'] ?>" class="card-img-top">
                <div class="card-body text-center">
                  <h5 class="card-title"><?= $libro['titulo'] ?></h5>
                  <p class="card-text"><?= $libro['autor'] ?></p>
                  <a href="libro.php?id=<?= $libro['id_libro'] ?>" class="btn btn-vermas">Ver más</a>

                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!--Sección de los libros estandar -->
      <div class="tab-pane fade" id="estandar">
        <div class="row g-4">
          <?php while($libro = $resultEstandar->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6">
              <div class="card h-100 book-card">
                <img src="<?= $libro['imagen'] ?>" alt="<?= $libro['titulo'] ?>" class="card-img-top">
                <div class="card-body text-center">
                  <h5 class="card-title"><?= $libro['titulo'] ?></h5>
                  <p class="card-text"><?= $libro['autor'] ?></p>
                 <a href="libro.php?id=<?= $libro['id_libro'] ?>" class="btn btn-vermas">Ver más</a>

                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!--Se muestra la tabla de la categoria premium -->
      <div class="tab-pane fade" id="premium">
        <div class="row g-4">
          <?php while($libro = $resultPremium->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6">
              <div class="card h-100 book-card">
                <img src="<?= $libro['imagen'] ?>" alt="<?= $libro['titulo'] ?>" class="card-img-top">
                <div class="card-body text-center">
                  <h5 class="card-title"><?= $libro['titulo'] ?></h5>
                  <p class="card-text"><?= $libro['autor'] ?></p>
                  <a href="libro.php?id=<?= $libro['id_libro'] ?>" class="btn btn-vermas">Ver más</a>

                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

    </div>
  </div>
</section>
<footer class="text-white text-center py-3" style="background-color: #a18262;">
  <p class="mb-0">2025 CapituloUno</p>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="javascript/scripts.js"></script> <!-- Incluimos tu JS para modo oscuro -->
</body>
</html>
