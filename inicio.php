<?php
session_start();
include "base_de_datos/conexion.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">   
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CapituloUno Inicio</title> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="multimedia/logo.png">
  
<link href="css/estilos.css" rel="stylesheet">


  <link rel="stylesheet" href="css/nav_dark.css">
</head>
<body>
<main>
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



  <section class="central text-center py-5">
    <div>
      <h1 class="display-4">CapituloUno</h1>
      <h2 class="lead">Explora, descubre y lee tus libros favoritos</h2>
    </div>
  </section>


  <section class="inf py-5 text-center">
    <div class="info container">
      <h2 class="por mb-4">¿Por qué elegirnos?</h2>
      <div class="row g-4">
        <div class="col-md-4 card h-100 book-card">
          <h5>Envíos Rápidos</h5>
          <p>Recibe tus libros en menos de 48 horas.</p>
        </div>
        <div class="col-md-4 card h-100 book-card">
          <h5>Gran Catálogo</h5>
          <p>Disfruta de miles de títulos y géneros literarios.</p>
        </div>
        <div class="col-md-4 card h-100 book-card">
          <h5>Paquetes Sorpresa</h5>
          <p>Recibe increíbles cajas con ediciones especiales.</p>
        </div>
      </div>
    </div>
  </section>


  <nav class="separador py-3">
    <span class="navbar-text mx-auto text-center">
      Prueba un mes con nosotros y disfruta de grandes ventajas como cajas sorpresa, ediciones especiales y regalos exclusivos.
    </span>
  </nav>


  <section class="slider-container py-5">
    <h2 class="text-center mb-4" style="color:#5a4634;">Productos Destacados</h2>
    <div id="carouselDestacados" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
      <div class="carousel-inner">
        <?php
        //^Se selecciona los libros en orden de fecha de publicación 
        $sql_libros = "SELECT * FROM libros ORDER BY fecha_publicacion DESC LIMIT 5";
        $resultado_libros = $conexion->query($sql_libros);

        if($resultado_libros && $resultado_libros->num_rows > 0){
            $active = "active";
            while($libro = $resultado_libros->fetch_assoc()){
                $descripcion = htmlspecialchars($libro['descripcion']);
                if(strlen($descripcion) > 150){
                    $descripcion = substr($descripcion, 0, 147) . '...';
                }
                //^Se mostrará la imagen, el nombre, la descripción y el botón para ver la información completa
                echo '<div class="carousel-item '.$active.'">';
                echo '<a href="libro.php?id='.$libro['id_libro'].'"><img src="'.htmlspecialchars($libro['imagen']).'" class="d-block w-100" alt="'.htmlspecialchars($libro['titulo']).'"></a>';
                echo '<div class="caption-box">';
                echo '<h5>'.htmlspecialchars($libro['titulo']).'</h5>';
                echo '<p>'.$descripcion.'</p>';
                echo '<a href="libro.php?id='.$libro['id_libro'].'" class="btn btn-warning mt-2">Ver más</a>';
                echo '</div></div>';
                $active = "";
            }
        } else {
          //^si no hubiese libros destacados se mostraria un aviso comentadolo
            echo '<div class="carousel-item active"><p class="text-center">No hay libros destacados.</p></div>';
        }
        ?>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestacados" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselDestacados" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
      </button>
    </div>
  </section>

  <!-- Opiniones de usuarios -->
  <section class="opiniones py-5">
    <div class="container">
      <h2 class="text-center mb-5" >Opiniones de Usuarios</h2>
      <div class="row g-4">
        <?php
        //^Aqwi se hace la selección de los comentarios de cada persona 
        $sql = "SELECT o.comentario, o.puntuacion, u.nombre 
                FROM opiniones o 
                JOIN usuarios u ON o.id_usuario = u.id_usuario
                ORDER BY o.fecha DESC LIMIT 6";
        $resultado = $conexion->query($sql);
        //^Si encuentra las opiniones las muestra con el nombre y el comentario
        if($resultado && $resultado->num_rows > 0){
            while($fila = $resultado->fetch_assoc()){
                echo '<div class="col-md-4">';
                echo '<div class="card h-100 shadow-sm p-3">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">'.htmlspecialchars($fila['nombre']).'</h5>';
                echo '<p class="card-text">“'.htmlspecialchars($fila['comentario']).'”</p>';
                echo '</div></div></div>';
            }
        } else {
          //^Si no hay opiniones se avisa y se propone que comente
            echo '<p class="text-center">No hay opiniones aún. Sé el primero en opinar!</p>';
        }
        ?>
      </div>

      <?php 
        //^el botón de "Escribir opión solo se mostrará si se ha iniciado sesión
        if(isset($_SESSION['id_usuario'])): 
      ?>

        <div class="text-center mt-4">
          <a href="crear_opinion.php" class="btn btn-primary">Escribir Opinión</a>
        </div>
      <?php 
        //^Si no esta iniciada la sesión desaparece el botón y se avisa al usuario
        else: 
      ?>
        <div class="text-center mt-4">
          <p><em>Inicia sesión para dejar tu opinión.</em></p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer class="text-white text-center py-3" style="background-color: #a18262;">
  <p class="mb-0">2025 CapituloUno</p>
</footer>

<script src="javascript/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
