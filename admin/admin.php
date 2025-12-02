<?php
session_start();
require_once '../base_de_datos/conexion.php';

//^funcion para mostrar la informacion arriba en la pagina
function flash($msg = null) {
    if ($msg === null) {
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $f;
        }
        return null;
    } else $_SESSION['flash'] = $msg;
}
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

//^Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
//^Consultas de crear, editar, eliminar de los libros
    if ($_POST['action'] === 'add_libro') {
        $titulo = trim($_POST['titulo']);
        $autor = trim($_POST['autor']);
        $descripcion = trim($_POST['descripcion']);
        $fecha = $_POST['fecha_publicacion'] ?: null;
        $categoria = trim($_POST['categoria']);
        $tipo = $_POST['tipo_libro'];
        $precio = floatval($_POST['precio']);
        $imagen = trim($_POST['imagen']); // URL directo

        if ($titulo === '' || $autor === '') {
            flash(['type'=>'danger','msg'=>'Título y autor son obligatorios.']);
        } else {
            $stmt = $conexion->prepare(
                "INSERT INTO libros (titulo, autor, descripcion, fecha_publicacion, categoria, tipo_libro, precio, imagen)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssds", $titulo, $autor, $descripcion, $fecha, $categoria, $tipo, $precio, $imagen);
            $stmt->execute();
            flash(['type'=>'success','msg'=>'Libro añadido con éxito.']);
        }

        header("Location:".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }

    if ($_POST['action'] === 'edit_libro') {
        $id = intval($_POST['id_libro']);
        $titulo = trim($_POST['titulo']);
        $autor = trim($_POST['autor']);
        $descripcion = trim($_POST['descripcion']);
        $fecha = $_POST['fecha_publicacion'] ?: null;
        $categoria = trim($_POST['categoria']);
        $tipo = $_POST['tipo_libro'];
        $precio = floatval($_POST['precio']);
        $imagen = trim($_POST['imagen']); 

        $stmt = $conexion->prepare(
            "UPDATE libros SET titulo=?, autor=?, descripcion=?, fecha_publicacion=?, categoria=?, tipo_libro=?, precio=?, imagen=? WHERE id_libro=?"
        );
        $stmt->bind_param("ssssssdsi", $titulo, $autor, $descripcion, $fecha, $categoria, $tipo, $precio, $imagen, $id);
        $stmt->execute();

        flash(['type'=>'success','msg'=>'Libro actualizado.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }

    if ($_POST['action'] === 'delete_libro') {
        $id = intval($_POST['id_libro']);
        $stmt = $conexion->prepare("DELETE FROM libros WHERE id_libro=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();

        flash(['type'=>'success','msg'=>'Libro eliminado.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }

//^Formularios de crear, editar, eliminar de usuarios
    if ($_POST['action'] === 'add_usuario') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $pass = $_POST['contraseña'];
        $tipo = $_POST['tipo_suscripcion'];

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (nombre, email, contraseña, tipo_suscripcion)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $nombre, $email, $hash, $tipo);
        $stmt->execute();

        flash(['type'=>'success','msg'=>'Usuario añadido.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }

    if ($_POST['action'] === 'edit_usuario') {
        $id = intval($_POST['id_usuario']);
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $tipo = $_POST['tipo_suscripcion'];
        $pass = $_POST['contraseña'];

        if ($pass !== "") {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare(
                "UPDATE usuarios SET nombre=?, email=?, contraseña=?, tipo_suscripcion=? WHERE id_usuario=?"
            );
            $stmt->bind_param("ssssi", $nombre, $email, $hash, $tipo, $id);
        } else {
            $stmt = $conexion->prepare(
                "UPDATE usuarios SET nombre=?, email=?, tipo_suscripcion=? WHERE id_usuario=?"
            );
            $stmt->bind_param("sssi", $nombre, $email, $tipo, $id);
        }
        $stmt->execute();
        flash(['type'=>'success','msg'=>'Usuario actualizado.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }

    if ($_POST['action'] === 'delete_usuario') {
        $id = intval($_POST['id_usuario']);
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        flash(['type'=>'success','msg'=>'Usuario eliminado.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }

//^Edicion de suscripcion
    if ($_POST['action'] === 'cambiar_suscripcion') {
        $id = intval($_POST['id_usuario']);
        $tipo = $_POST['tipo_suscripcion'];
        $stmt = $conexion->prepare("UPDATE usuarios SET tipo_suscripcion=? WHERE id_usuario=?");
        $stmt->bind_param("si",$tipo,$id);
        $stmt->execute();
        flash(['type'=>'success','msg'=>'Suscripción cambiada.']);
        header("Location:".$_SERVER['PHP_SELF']."#panel-suscripciones");
        exit;
    }
}

//^Cargar las consultas a la base de datos
$libros_res = $conexion->query("SELECT * FROM libros ORDER BY id_libro DESC");
$usuarios_res = $conexion->query("SELECT * FROM usuarios ORDER BY id_usuario DESC");

//^Estadisticas
$stats_labels = [];
$stats_values = [];
$ventas_res = $conexion->query("SELECT DATE(fecha_compra) AS dia, SUM(precio_pagado) AS total_ventas FROM compras GROUP BY DATE(fecha_compra) ORDER BY DATE(fecha_compra) ASC");
while ($v = $ventas_res->fetch_assoc()) {
    $stats_labels[] = $v['dia'];
    $stats_values[] = floatval($v['total_ventas']);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow">


<?php if ($f=flash()): ?>
<div class="alert alert-<?=e($f['type'])?>"><?=e($f['msg'])?></div>
<?php endif; ?>

<!--Panel de libros -->
<div id="panel-libros" class="mt-5">

<h1 class="text-center mb-4">Panel de Administración</h1>

<div class="d-flex justify-content-end mb-4">
    <a href="../logout.php" class="btn btn-danger">Cerrar sesión</a>
</div>

<!--Formulario de libros -->
<div class="card mb-4">
<div class="card-header">Añadir Libro</div>
<div class="card-body">
<form method="post">
<input type="hidden" name="action" value="add_libro">
<div class="row mb-3">
    <div class="col"><label>Título</label><input type="text" name="titulo" class="form-control" required></div>
    <div class="col"><label>Autor</label><input type="text" name="autor" class="form-control" required></div>
</div>
<div class="mb-3"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
<div class="row mb-3">
    <div class="col"><label>Fecha publicación</label><input type="date" name="fecha_publicacion" class="form-control"></div>
    <div class="col"><label>Categoría</label><input type="text" name="categoria" class="form-control"></div>
</div>
<div class="row mb-3">
    <div class="col"><label>Tipo de libro</label>
        <select name="tipo_libro" class="form-select">
            <option value="estandar">Estándar</option>
            <option value="premium">Premium</option>
        </select>
    </div>
    <div class="col"><label>Precio</label><input type="number" step="0.01" name="precio" class="form-control"></div>
</div>
<div class="mb-3"><label>URL Imagen</label><input type="text" placeholder="https://..." name="imagen" class="form-control"></div>
<button class="btn btn-success">Añadir</button>
</form>
</div></div>

<!--Mostrar lista de libros -->
<table class="table table-bordered table-striped">
<tr><th>ID</th><th>Título</th><th>Autor</th><th>Imagen</th><th>Acciones</th></tr>
<?php while ($l = $libros_res->fetch_assoc()): ?>
<tr>
<td><?= $l['id_libro'] ?></td>
<td><?= e($l['titulo']) ?></td>
<td><?= e($l['autor']) ?></td>
<td><?php if ($l['imagen']): ?><img src="<?= e($l['imagen']) ?>" style="width:60px;height:80px;object-fit:cover;"><?php endif; ?></td>
<td>
<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editLibro<?=$l['id_libro']?>">Editar</button>
<form method="post" class="d-inline">
    <input type="hidden" name="action" value="delete_libro">
    <input type="hidden" name="id_libro" value="<?=$l['id_libro']?>">
    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar libro?')">Eliminar</button>
</form>
</td>
</tr>

<!--Modal para editar el libro-->
<div class="modal fade" id="editLibro<?=$l['id_libro']?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="post">
<input type="hidden" name="action" value="edit_libro">
<input type="hidden" name="id_libro" value="<?=$l['id_libro']?>">
<div class="modal-header"><h5>Editar Libro</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="row mb-3">
  <div class="col"><label>Título</label><input type="text" name="titulo" value="<?=e($l['titulo'])?>" class="form-control"></div>
  <div class="col"><label>Autor</label><input type="text" name="autor" value="<?=e($l['autor'])?>" class="form-control"></div>
</div>
<label>Descripción</label>
<textarea name="descripcion" class="form-control mb-3"><?=e($l['descripcion'])?></textarea>
<div class="row mb-3">
  <div class="col"><label>Fecha</label><input type="date" name="fecha_publicacion" value="<?=e($l['fecha_publicacion'])?>" class="form-control"></div>
  <div class="col"><label>Categoría</label><input type="text" name="categoria" value="<?=e($l['categoria'])?>" class="form-control"></div>
</div>
<div class="row mb-3">
  <div class="col"><label>Tipo</label>
    <select name="tipo_libro" class="form-select">
      <option value="estandar" <?=$l['tipo_libro']=='estandar'?'selected':''?>>Estándar</option>
      <option value="premium" <?=$l['tipo_libro']=='premium'?'selected':''?>>Premium</option>
    </select>
  </div>
  <div class="col"><label>Precio</label><input type="number" step="0.01" name="precio" value="<?=$l['precio']?>" class="form-control"></div>
</div>
<div class="mb-3"><label>URL Imagen</label><input type="text" name="imagen" value="<?=e($l['imagen'])?>" class="form-control"></div>
</div>
<div class="modal-footer"><button class="btn btn-primary">Guardar</button></div>
</form></div></div></div>
<?php endwhile; ?>
</table>
<!--Panel de usuarios -->
<div id="panel-usuarios" class="mt-5">
<h2>Usuarios</h2>
<!--Formulario para añadir usuario -->
<div class="card mb-4">
<div class="card-header">Añadir Usuario</div>
<div class="card-body">
<form method="post">
<input type="hidden" name="action" value="add_usuario">
<div class="row mb-3">
  <div class="col"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
  <div class="col"><label>Email</label><input type="email" name="email" class="form-control" required></div>
</div>
<div class="row mb-3">
  <div class="col"><label>Contraseña</label><input type="password" name="contraseña" class="form-control" required></div>
  <div class="col"><label>Tipo Suscripción</label>
    <select name="tipo_suscripcion" class="form-select">
      <option value="gratuita">Gratuita</option>
      <option value="premium">Premium</option>
    </select>
  </div>
</div>
<button class="btn btn-success">Añadir Usuario</button>
</form>
</div></div>

<!--Lista de usuarios -->
<table class="table table-bordered table-striped">
<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Suscripción</th><th>Acciones</th></tr>
<?php while($u=$usuarios_res->fetch_assoc()): ?>
<tr>
<td><?=$u['id_usuario']?></td>
<td><?=e($u['nombre'])?></td>
<td><?=e($u['email'])?></td>
<td><?=e($u['tipo_suscripcion'])?></td>
<td>
<!--Edicion de usuarios -->
<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUsuario<?=$u['id_usuario']?>">Editar</button>
<form method="post" class="d-inline">
<input type="hidden" name="action" value="delete_usuario">
<input type="hidden" name="id_usuario" value="<?=$u['id_usuario']?>">
<button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')">Eliminar</button>
</form>
</td>
</tr>

<!--Modal para editar el usuario -->
<div class="modal fade" id="editUsuario<?=$u['id_usuario']?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="post">
<input type="hidden" name="action" value="edit_usuario">
<input type="hidden" name="id_usuario" value="<?=$u['id_usuario']?>">
<div class="modal-header"><h5>Editar Usuario</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label>Nombre</label><input type="text" name="nombre" value="<?=e($u['nombre'])?>" class="form-control"></div>
<div class="mb-3"><label>Email</label><input type="email" name="email" value="<?=e($u['email'])?>" class="form-control"></div>
<div class="mb-3"><label>Contraseña (dejar vacío para no cambiar)</label><input type="password" name="contraseña" class="form-control"></div>
<div class="mb-3"><label>Tipo Suscripción</label>
<select name="tipo_suscripcion" class="form-select">
  <option value="gratuita" <?=$u['tipo_suscripcion']=='gratuita'?'selected':''?>>Gratuita</option>
  <option value="premium" <?=$u['tipo_suscripcion']=='premium'?'selected':''?>>Premium</option>
</select>
</div>
</div>
<div class="modal-footer"><button class="btn btn-primary">Guardar</button></div>
</form></div></div></div>
<?php endwhile; ?>
</table>
</div>

<!-- Estadisticas de ventas -->
<div id="panel-estadisticas" class="mt-5">
<h2>Estadísticas de Ventas</h2>
<canvas id="ventasChart" height="100"></canvas>
</div>

</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ventasChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($stats_labels) ?>,
        datasets: [{
            label: 'Ventas por día (€)',
            data: <?= json_encode($stats_values) ?>,
            fill: true,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero:true }
        }
    }
});
</script>
</body>
</html>

