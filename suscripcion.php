<?php
session_start();
include "base_de_datos/conexion.php"; 

$mensaje = "";

//^Se verifica la sesión porque si no no se puede acceder
if (!isset($_SESSION['id_usuario'])) {
    $mensaje = "Debes iniciar sesión para acceder a esta página.";
    $acceso = false;
} else {
    $acceso = true;

  //^este es el formulario donde se actualiza la suscripción del usuario entre premium o gratuita 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo_suscripcion = $_POST['suscripcion'] ?? 'gratuita';

        $stmt = $conexion->prepare("UPDATE usuarios SET tipo_suscripcion = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $tipo_suscripcion, $_SESSION['id_usuario']);

        if ($stmt->execute()) {
            $mensaje = "Tu suscripción ha sido actualizada a '".htmlspecialchars($tipo_suscripcion)."' correctamente.";
            $_SESSION['tipo_suscripcion'] = $tipo_suscripcion; // actualizar sesión
        } else {
            $mensaje = "Error al actualizar la suscripción. Intenta de nuevo.";
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
  <title>CapituloUno - Suscripción</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="multimedia/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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



  <section class="container py-4">
      <?php if(!$acceso): ?>
          <div class="alert alert-warning text-center"><?= $mensaje ?></div>
      <?php elseif($mensaje): ?>
          <div class="alert alert-success text-center"><?= $mensaje ?></div>
      <?php endif; ?>
  </section>

  <section class="content-section bg-highlight">
    <img src="https://img.freepik.com/foto-gratis/enfermera-cuidando-persona-mayor_23-2150216365.jpg?semt=ais_hybrid&w=740&q=80" alt="Lectura inspiradora">
    <div class="content-text">
      <h3 class="section-title">¿Quieres quedarte en estandar?</h3>
      <p>Si te quedas en estadar podras disfrutar de muchos libros, pero, ¿Porqué no compruebas el modo premium? quiza sea el momento de darle 
        una oportunidad y conocer mundos nuevos.</p>
    </div>
  </section>

  <section class="content-section bg-highlight">
    <img src="https://content.elmueble.com/medio/2024/12/02/cofre-especial-de-navidad-de-la-saga-blackwater-de-blackie-books_1098c135_241202171316_1200x630.jpg" alt="Lectura 2">
    <div class="content-text">
      <h3 class="section-title">¿Quieres probar el premium?</h3>
      <p>Veo que quieres vivir experiencias nuevas en la lectura. ¿Qué tal una portada de edición limitada o que tal que el borde de las páginas 
        sea muy colorido? Sea lo que sea que estas buscando, con esta suscripción estas en el lugar indicado. Podras disfrutar de verdaderas obras de 
        arte con algunas de las portadas y decoraciones que jamas ahayas visto. Así que aventuror, ¿Porqué no te das una vuelta por aqui para encontrar 
        tu proxima aventura?
      </p>
    </div>
  </section>


  <?php
  //^Comprobamos que el usuario tiene acceso a esta zona y vemos el formulario del tipo de suscripción 
    if($acceso): 
  ?>
  <section class="subscription-form bg-highlight">
    <h4>Elige tu suscripción</h4>
    <form method="post">
      <div class="form-check">
        <input class="form-check-input" type="radio" name="suscripcion" id="estandar" value="gratuita" <?= ($_SESSION['tipo_suscripcion'] ?? '') === 'gratuita' ? 'checked' : '' ?>>
        <label class="form-check-label" for="estandar">Suscripción Estándar</label>
      </div>
      <div class="form-check mt-2">
        <input class="form-check-input" type="radio" name="suscripcion" id="premium" value="premium" <?= ($_SESSION['tipo_suscripcion'] ?? '') === 'premium' ? 'checked' : '' ?>>
        <label class="form-check-label" for="premium">Suscripción Premium</label>
      </div>
      <button type="submit" class="btn btn-warning mt-3">Actualizar suscripción</button>
    </form>
  </section>
  <?php endif; ?>

</main>

<footer class="text-white text-center py-3" style="background-color: #a18262;">
  <p class="mb-0">2025 CapituloUno</p>
</footer>

<script src="javascript/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
