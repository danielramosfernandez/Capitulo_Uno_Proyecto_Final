<?php
session_start();
include "base_de_datos/conexion.php";

//^En esta seccion no se podrá acceder si no esta iniciada sesión
if(!isset($_SESSION['id_usuario'])){
    header("Location: login.php");
    exit;
}

//^Se revisan los libros para mostrarlos en el desplegable
$sql_libros = "SELECT id_libro, titulo FROM libros ORDER BY titulo ASC";
$resultado_libros = $conexion->query($sql_libros);

//^Procesamiento del formulario
$mensaje = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id_libro = intval($_POST['id_libro']);
    $puntuacion = intval($_POST['puntuacion']);
    $comentario = trim($_POST['comentario']);
    $id_usuario = $_SESSION['id_usuario'];

    if($id_libro <= 0 || $puntuacion < 1 || $puntuacion > 5 || empty($comentario)){
        $mensaje = '<p class="error-msg">Por favor completa todos los campos correctamente.</p>';
    } else {
        $stmt = $conexion->prepare("INSERT INTO opiniones (id_usuario, id_libro, puntuacion, comentario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id_usuario, $id_libro, $puntuacion, $comentario);
        if($stmt->execute()){
            $mensaje = '<p class="success-msg">¡Opinión publicada con éxito!</p>';
        } else {
            $mensaje = '<p class="error-msg">Error al guardar la opinión. Intenta de nuevo.</p>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">   
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Escribir Opinión - CapituloUno</title> 

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="multimedia/logo.png">
  <link href="css/estilos.css" rel="stylesheet">
  <link rel="stylesheet" href="css/nav_dark.css">

<style>
/**Ajuste del footer*/
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}
body {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

/**Main crece para empujar el footer */
main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/**Este contenedor también debe expandirse */
.perfil-main {
    flex: 1;
}

/**Botón corregido */
.btn-cart {
    background-color: #0d6efd !important;
    color: white !important;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    width: auto;
    display: inline-block;
    text-align: center;
    font-weight: 500;
    transition: 0.2s;
}
.btn-cart:hover {
    background-color: #0b5ed7 !important;
}
</style>

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
        <li class="nav-item"><a class="nav-link" href="perfil.php">Mi Perfil</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar sesión</a></li>
        <li class="nav-item">
          <a class="nav-link" href="carrito.php">
            <img class="carro" src="multimedia/carrito.png" alt="Carrito" width="40" height="40">
          </a>
        </li>
        <li class="nav-item">
          <button id="modo" class="btn btn-outline-light ms-2">Oscuro</button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="perfil-main">
  <div class="perfil-container">
    <h2 class="section-title text-center">Escribir Opinión</h2>

    <?php if($mensaje) echo $mensaje; ?>

    <form action="" method="POST" class="p-4 bg-white rounded-4 shadow-sm">
      <div class="mb-3">
        <label for="id_libro" class="form-label">Selecciona el libro</label>
        <select name="id_libro" id="id_libro" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php while($libro = $resultado_libros->fetch_assoc()): ?>
            <option value="<?= $libro['id_libro'] ?>"><?= htmlspecialchars($libro['titulo']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="puntuacion" class="form-label">Puntuación (1-5)</label>
        <input type="number" name="puntuacion" id="puntuacion" class="form-control" min="1" max="5" required>
      </div>

      <div class="mb-3">
        <label for="comentario" class="form-label">Comentario</label>
        <textarea name="comentario" id="comentario" class="form-control" rows="4" placeholder="Escribe tu opinión..." required></textarea>
      </div>

      <button type="submit" class="btn btn-cart">Publicar Opinión</button>
    </form>
  </div>
</div>
</main>

<footer class="text-white py-3" style="background: linear-gradient(135deg, #a18262, #6b4f3b);">
  <div class="container">
    <div class="row">
      <div class="col-md-4 text-center text-md-start mb-2">
        <h6 class="fw-bold mb-1">CapituloUno</h6>
        <p class="small mb-0">© 2025 Todos los derechos reservados</p>
      </div>

      <div class="col-md-4 text-center mb-2">
        <h6 class="fw-bold mb-2">Enlaces</h6>
        <ul class="list-unstyled mb-0">
          <li><a href="#about" class="text-white text-decoration-none">Sobre nosotros</a></li>
          <li><a href="#services" class="text-white text-decoration-none">Servicios</a></li>
          <li><a href="#contact" class="text-white text-decoration-none">Contacto</a></li>
        </ul>
      </div>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const body = document.body;
const botonModo = document.getElementById('modo');
if(localStorage.getItem('modo') === 'oscuro') {
  body.classList.add('dark-mode');
  botonModo.textContent = 'Claro';
}
botonModo.addEventListener('click', () => {
  body.classList.toggle('dark-mode');
  if(body.classList.contains('dark-mode')) {
    localStorage.setItem('modo', 'oscuro');
    botonModo.textContent = 'Claro';
  } else {
    localStorage.setItem('modo', 'claro');
    botonModo.textContent = 'Oscuro';
  }
});
</script>

</body>
</html>
