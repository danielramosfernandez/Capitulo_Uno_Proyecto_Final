<?php
session_start();
include "base_de_datos/conexion.php"; 

//^Primero se comprueba que el Id enviado esta correcto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Libro no encontrado.");
}

//^Se recoge el Id del libro y se guarda en un entero
$id_libro = (int) $_GET['id'];

//^Se consultan los datos de el libro 
$stmt = $conexion->prepare("SELECT * FROM libros WHERE id_libro = ?");
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Libro no encontrado.");
}

$libro = $resultado->fetch_assoc();
$stmt->close();

//^Mensaje de que se debe iniciar sesión para accder al carrito 
$mensaje = "";
if(isset($_POST['agregar_carrito'])){
    if(!isset($_SESSION['id_usuario'])){
        $mensaje = "Debes iniciar sesión para agregar productos al carrito.";
    } else {
        $id_usuario = $_SESSION['id_usuario'];

        //^Se consulta el tipo de suscripción del usuario para verificar su agregaciónb al carrito
        $stmt = $conexion->prepare("SELECT tipo_suscripcion FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc();
        $stmt->close();

        //^Si el usuario tiene cuenta gratuita no podrá agregar un libro premium
        if($libro['tipo_libro'] === 'premium' && $usuario['tipo_suscripcion'] !== 'premium'){
            $mensaje = "No puedes agregar este libro premium con tu suscripción gratuita.";
        } else {
            //^Se comprueba si el usuario tiene ya un carrito con libros previos
            $stmt = $conexion->prepare("SELECT id_carrito FROM carrito WHERE id_usuario=?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $res = $stmt->get_result();

            if($res->num_rows > 0){
                $carrito = $res->fetch_assoc();
                $id_carrito = $carrito['id_carrito'];
            } else {
                //^Si no tiene el carrito creado se le crea
                $stmt_insert = $conexion->prepare("INSERT INTO carrito (id_usuario) VALUES (?)");
                $stmt_insert->bind_param("i", $id_usuario);
                $stmt_insert->execute();
                $id_carrito = $stmt_insert->insert_id;
                $stmt_insert->close();
            }
            $stmt->close();

            //^Se comprueba si el libro ya fue introducido 
            $stmt = $conexion->prepare("SELECT * FROM carrito_items WHERE id_carrito=? AND id_libro=?");
            $stmt->bind_param("ii", $id_carrito, $id_libro);
            $stmt->execute();
            $res = $stmt->get_result();

            if($res->num_rows > 0){
                //^Si el libro fue metido anteriormente en el carrito se aumenta la cantidad
                $stmt_update = $conexion->prepare("UPDATE carrito_items SET cantidad = cantidad + 1 WHERE id_carrito=? AND id_libro=?");
                $stmt_update->bind_param("ii", $id_carrito, $id_libro);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                //^Si no fue introducido previamente se añade por primera vez
                $stmt_insert = $conexion->prepare("INSERT INTO carrito_items (id_carrito, id_libro, cantidad, precio_unitario) VALUES (?, ?, 1, ?)");
                $stmt_insert->bind_param("iid", $id_carrito, $id_libro, $libro['precio']);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
            $stmt->close();

            $mensaje = "Producto agregado al carrito con éxito.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($libro['titulo']) ?> - Información del Libro</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
<link href="css/estilos.css" rel="stylesheet">


  <link rel="stylesheet" href="css/nav_dark.css">

<link rel="icon" type="image/x-icon" href="multimedia/logo.png">
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



<main class="flex-fill">
<section class="book-container">
<div class="row">
<!--imagen del libro y el titulo -->
  <div class="col-md-5 text-center">
    <img src="<?= htmlspecialchars($libro['imagen'] ?? 'multimedia/default.png') ?>"
         alt="<?= htmlspecialchars($libro['titulo']) ?>"
         class="book-img">
  </div>

<!--información de el libro -->
  <div class="col-md-7">
    <h1 class="book-title"><?= htmlspecialchars($libro['titulo']) ?></h1>
    <h3 class="book-author"><?= htmlspecialchars($libro['autor']) ?></h3>
    <p class="book-description"><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></p>
    <p class="book-price"><strong>Precio:</strong> <?= number_format($libro['precio'], 2, ',', '.') ?>€</p>

    <?php if($mensaje): ?>
      <div class="alert alert-info mt-3"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="post">
      <button type="submit" name="agregar_carrito" class="btn btn-primary btn-cart">Agregar al carrito</button>
    </form>
  </div>
</div>
</section>
</main>

<footer class="text-white text-center py-3 footer-fixed">
<p class="mb-0">2025 CapituloUno</p>
</footer>

<script src="javascript/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
