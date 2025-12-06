<?php
session_start();
include 'base_de_datos/conexion.php'; // Conexión a la BD

//^Aqui verificamos si el usuario a inciado sesión, si no es así esta sección no aparecera
if(!isset($_SESSION['id_usuario'])){
    echo "<p style='text-align:center;margin-top:50px;font-size:1.5rem;'>Debes iniciar sesión para ver tu carrito.</p>";
    echo "<p style='text-align:center;'><a href='login.php'>Iniciar sesión</a></p>";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje_compra = "";

//^Se extrae de la base de datos del usuario el contenido del carrito
$stmt = $conexion->prepare("SELECT id_carrito FROM carrito WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    $productos = [];
    $total = 0;
} else {
    $carrito = $res->fetch_assoc();
    $id_carrito = $carrito['id_carrito'];

  //^Aqui se extrae la información del libro que se va a comprar como la cantidad de veces que se va a comprar o su precio unitario
    $sql = "SELECT ci.id_carrito_item, ci.id_libro, ci.cantidad, ci.precio_unitario, l.titulo, l.imagen
            FROM carrito_items ci
            JOIN libros l ON ci.id_libro = l.id_libro
            WHERE ci.id_carrito = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    $res = $stmt->get_result();

    $productos = [];
    $total = 0;
    //^Aqui se calcula el precio de la compra que haya en el carrito
    while($fila = $res->fetch_assoc()){
        $fila['subtotal'] = $fila['precio_unitario'] * $fila['cantidad'];
        $total += $fila['subtotal'];
        $productos[] = $fila;
    }
    $stmt->close();
}

//^En esta parte se hace la funcionalidad de los botones de actualización de compra, eliminar el elemento o finlizar la compra
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //^Se edita las opciones de la compra al pulsar el botón
    if(isset($_POST['update'])){
        foreach($_POST['cantidades'] as $id_carrito_item => $cantidad){
            $cantidad = max(1, (int)$cantidad);
            $stmt = $conexion->prepare("UPDATE carrito_items SET cantidad=? WHERE id_carrito_item=? AND id_carrito=?");
            $stmt->bind_param("iii", $cantidad, $id_carrito_item, $id_carrito);
            $stmt->execute();
            $stmt->close();
        }
        //^Este botón se verá una vez se le pulse ya que recargara la página
        header("Location: carrito.php");
        exit;
    }

    //^Se elimina el libro seleccionado
    if(isset($_POST['delete'])){
        $id_carrito_item = (int)$_POST['delete'];
        $stmt = $conexion->prepare("DELETE FROM carrito_items WHERE id_carrito_item=? AND id_carrito=?");
        $stmt->bind_param("ii", $id_carrito_item, $id_carrito);
        $stmt->execute();
        $stmt->close();
        header("Location: carrito.php");
        exit;
    }

    //^Se fibnaliza la compra  y por cada producto  se inserta cada compra con su información en la tabla "compras"
    if(isset($_POST['checkout'])){
        foreach($productos as $prod){
            $stmt_insert = $conexion->prepare("INSERT INTO compras (id_usuario, id_libro, fecha_compra, precio_pagado) VALUES (?, ?, NOW(), ?)");
            $stmt_insert->bind_param("iid", $id_usuario, $prod['id_libro'], $prod['precio_unitario']);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        //^Una vez que se haya realizado la compra se vacia la tabla de carritos
        $stmt_delete = $conexion->prepare("DELETE FROM carrito_items WHERE id_carrito=?");
        $stmt_delete->bind_param("i", $id_carrito);
        $stmt_delete->execute();
        $stmt_delete->close();

        $productos = [];
        $total = 0;
        $mensaje_compra = "¡Compra realizada con éxito!";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Carrito - CapituloUno</title>
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




<main>
<div class="container py-5">
    <h1 class="mb-4">Mi Carrito de Compras</h1>

    <?php if($mensaje_compra): ?>
        <div class="alert alert-success"><?= $mensaje_compra ?></div>
    <?php endif; ?>

    <?php if(count($productos) === 0): ?>
        <p>Tu carrito está vacío. <a href="catalogo.php">Ver catálogo</a></p>
    <?php else: ?>
        <form method="post">
        <table class="table table-bordered align-middle text-center">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Imagen</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Total parcial</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($productos as $prod): ?>
        <tr>
            <td><?= htmlspecialchars($prod['titulo']) ?></td>
            <td><img src="<?= $prod['imagen'] ?>" width="80" alt="<?= htmlspecialchars($prod['titulo']) ?>"></td>
            <td><?= number_format($prod['precio_unitario'],2) ?>€</td>
            <td>
                <input type="number" name="cantidades[<?= $prod['id_carrito_item'] ?>]" value="<?= $prod['cantidad'] ?>" min="1" class="form-control">
            </td>
            <td><?= number_format($prod['subtotal'],2) ?>€</td>
            <td>
                <button type="submit" name="delete" value="<?= $prod['id_carrito_item'] ?>" class="btn btn-danger btn-sm">Eliminar</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div><strong>Total: <?= number_format($total,2) ?>€</strong></div>
    <div>
        <button type="submit" name="update" class="btn btn-primary">Actualizar cantidades</button>
        <a href="catalogo.php" class="btn btn-secondary">Seguir comprando</a>
        <button type="submit" name="checkout" class="btn btn-success">Finalizar compra</button>
    </div>
</div>
        </form>
    <?php endif; ?>
</div>
</main>

<footer class="text-white py-3" style="background: linear-gradient(135deg, #a18262, #6b4f3b);">
  <div class="container">
    <div class="row">
      <!--En esta primera parte del footer tenemos los derechos  -->
      <div class="col-md-4 text-center text-md-start mb-2">
        <h6 class="fw-bold mb-1">CapituloUno</h6>
        <p class="small mb-0">© 2025 Todos los derechos reservados</p>
      </div>
      <!--En estA segunda columna ponemos un poco de información sobre contactos -->
      <div class="col-md-4 text-center mb-2">
        <h6 class="fw-bold mb-2">Enlaces</h6>
        <ul class="list-unstyled mb-0">
          <li><a href="#about" class="text-white text-decoration-none">Sobre nosotros</a></li>
          <li><a href="#services" class="text-white text-decoration-none">Servicios</a></li>
          <li><a href="#contact" class="text-white text-decoration-none">Contacto</a></li>
        </ul>
      </div>
      <!--En esta columna se dejan los enlaces a redes sociales -->
      <div class="col-md-4 text-center text-md-end mb-2">
        <h6 class="fw-bold mb-2">Síguenos</h6>
        <a href="https://facebook.com" target="_blank" class="mx-2">
          <img src="multimedia/facebook.png" alt="Facebook" width="28" height="28">
        </a>
        <a href="https://twitter.com" target="_blank" class="mx-2">
          <img src="multimedia/twitter.png" alt="Twitter" width="28" height="28">
        </a>
        <a href="https://instagram.com" target="_blank" class="mx-2">
          <img src="multimedia/instagram.png" alt="Instagram" width="28" height="28">
        </a>
      </div>
    </div>
  </div>
</footer>
<script src="javascript/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
