<?php
session_start();
require_once '../base_de_datos/conexion.php';

//^Función para mostrar mensaje de error
function flash($msg = null) {
    if ($msg === null) {
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $f;
        }
        return null;
    } else {
        $_SESSION['flash'] = $msg;
    }
}
function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
//^Comprobación para realizar inserción de un libro nuevo 
    if ($action === 'add_libro') {
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha_publicacion = !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : null;
        $categoria = trim($_POST['categoria'] ?? null);
        $tipo_libro = isset($_POST['tipo_libro']) && in_array($_POST['tipo_libro'], ['estandar','premium']) ? $_POST['tipo_libro'] : 'estandar';
        $precio = isset($_POST['precio']) && is_numeric($_POST['precio']) ? (float)$_POST['precio'] : 0.00;
        if ($titulo === '' || $autor === '') {
            flash(['type'=>'danger','msg'=>'El título y el autor son obligatorios.']);
        } else {
            $stmt = $conexion->prepare("INSERT INTO libros (titulo, autor, descripcion, fecha_publicacion, categoria, tipo_libro, precio) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssd", $titulo, $autor, $descripcion, $fecha_publicacion, $categoria, $tipo_libro, $precio);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Libro añadido correctamente.']);
            } else {
                flash(['type'=>'danger','msg'=>'Error al añadir libro: '.$conexion->error]);
            }
            $stmt->close();
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }
//^Función para editar un libro
    if ($action === 'edit_libro') {
        $id = (int)($_POST['id_libro'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha_publicacion = !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : null;
        $categoria = trim($_POST['categoria'] ?? null);
        $tipo_libro = isset($_POST['tipo_libro']) && in_array($_POST['tipo_libro'], ['estandar','premium']) ? $_POST['tipo_libro'] : 'estandar';
        $precio = isset($_POST['precio']) && is_numeric($_POST['precio']) ? (float)$_POST['precio'] : 0.00;
        if ($id <= 0 || $titulo === '' || $autor === '') {
            flash(['type'=>'danger','msg'=>'Datos inválidos para actualizar libro.']);
        } else {
            $stmt = $conexion->prepare("UPDATE libros SET titulo=?, autor=?, descripcion=?, fecha_publicacion=?, categoria=?, tipo_libro=?, precio=? WHERE id_libro=?");
            $stmt->bind_param("ssssssdi", $titulo, $autor, $descripcion, $fecha_publicacion, $categoria, $tipo_libro, $precio, $id);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Libro actualizado correctamente.']);
            } else {
                flash(['type'=>'danger','msg'=>'Error al actualizar libro: '.$conexion->error]);
            }
            $stmt->close();
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }
//^Función para eliminar un libro
    if ($action === 'delete_libro') {
        $id = (int)($_POST['id_libro'] ?? 0);
        if ($id > 0) {
            $stmt = $conexion->prepare("DELETE FROM libros WHERE id_libro = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Libro eliminado correctamente.']);
            } else {
                flash(['type'=>'danger','msg'=>'Error al eliminar libro: '.$conexion->error]);
            }
            $stmt->close();
        } else {
            flash(['type'=>'danger','msg'=>'ID de libro inválido.']);
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-libros");
        exit;
    }
//^Función para añadir un usuario
    if ($action === 'add_usuario') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['contraseña'] ?? '';
        $tipo = isset($_POST['tipo_suscripcion']) && in_array($_POST['tipo_suscripcion'], ['gratuita','premium']) ? $_POST['tipo_suscripcion'] : 'gratuita';
        if ($nombre === '' || $email === '' || $password === '') {
            flash(['type'=>'danger','msg'=>'Nombre, email y contraseña son obligatorios.']);
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, contraseña, tipo_suscripcion) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $hash, $tipo);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Usuario añadido correctamente.']);
            } else {
                if ($conexion->errno === 1062) {
                    flash(['type'=>'danger','msg'=>'El email ya está en uso.']);
                } else {
                    flash(['type'=>'danger','msg'=>'Error al añadir usuario: '.$conexion->error]);
                }
            }
            $stmt->close();
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }
//^Función para editar un usuario
    if ($action === 'edit_usuario') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['contraseña'] ?? '';
        $tipo = isset($_POST['tipo_suscripcion']) && in_array($_POST['tipo_suscripcion'], ['gratuita','premium']) ? $_POST['tipo_suscripcion'] : 'gratuita';
        if ($id <= 0 || $nombre === '' || $email === '') {
            flash(['type'=>'danger','msg'=>'Datos inválidos para actualizar usuario.']);
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, email=?, contraseña=?, tipo_suscripcion=? WHERE id_usuario=?");
                $stmt->bind_param("ssssi", $nombre, $email, $hash, $tipo, $id);
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, email=?, tipo_suscripcion=? WHERE id_usuario=?");
                $stmt->bind_param("sssi", $nombre, $email, $tipo, $id);
            }
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Usuario actualizado correctamente.']);
            } else {
                if ($conexion->errno === 1062) {
                    flash(['type'=>'danger','msg'=>'El email ya está en uso por otro usuario.']);
                } else {
                    flash(['type'=>'danger','msg'=>'Error al actualizar usuario: '.$conexion->error]);
                }
            }
            $stmt->close();
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }
//^Función para eliminar un usuario
    if ($action === 'delete_usuario') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        if ($id > 0) {
            $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Usuario eliminado correctamente.']);
            } else {
                flash(['type'=>'danger','msg'=>'Error al eliminar usuario: '.$conexion->error]);
            }
            $stmt->close();
        } else {
            flash(['type'=>'danger','msg'=>'ID de usuario inválido.']);
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-usuarios");
        exit;
    }
//^Función para modificar una suscripción desde el admin
    if ($action === 'cambiar_suscripcion') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        $tipo = isset($_POST['tipo_suscripcion']) && in_array($_POST['tipo_suscripcion'], ['gratuita','premium']) ? $_POST['tipo_suscripcion'] : 'gratuita';
        if ($id > 0) {
            $stmt = $conexion->prepare("UPDATE usuarios SET tipo_suscripcion = ? WHERE id_usuario = ?");
            $stmt->bind_param("si", $tipo, $id);
            if ($stmt->execute()) {
                flash(['type'=>'success','msg'=>'Tipo de suscripción actualizado.']);
            } else {
                flash(['type'=>'danger','msg'=>'Error al actualizar suscripción: '.$conexion->error]);
            }
            $stmt->close();
        } else {
            flash(['type'=>'danger','msg'=>'ID de usuario inválido.']);
        }
        header("Location: ".$_SERVER['PHP_SELF']."#panel-suscripciones");
        exit;
    }
}
//^Función para enseñar los libros
$libros_res = $conexion->query("SELECT * FROM libros ORDER BY id_libro DESC"); 
//^Usuarios ordenandos por id descendiente
$usuarios_res = $conexion->query("SELECT id_usuario, nombre, email, tipo_suscripcion, fecha_registro FROM usuarios ORDER BY id_usuario DESC");
//^A partir de aqui se trata de la estdisticas de venta
$compras_res = $conexion->query("SELECT id_compra, id_usuario, id_libro, fecha_compra, precio_pagado FROM compras ORDER BY fecha_compra DESC LIMIT 1000");
$stats_data = [];
if ($compras_res) {
    while ($r = $compras_res->fetch_assoc()) {
        //^Agrupar ventas por día
        $d = date('Y-m-d', strtotime($r['fecha_compra']));
        if (!isset($stats_data[$d])) $stats_data[$d] = 0;
        $stats_data[$d] += (float)$r['precio_pagado'];
    }
}
$stats_labels = array_keys($stats_data);
$stats_values = array_values($stats_data);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Zona de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/estilos_panel_admin.css" rel="stylesheet">
  <style>
    body {padding:20px;background:#f8f9fa}
    .admin-container {max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
    .btn-admin {min-width:160px}
    .admin-panel {margin-top:20px}
    .table-responsive {max-height:420px;overflow:auto}
    .form-inline {display:flex;gap:8px;flex-wrap:wrap}
  </style>
</head>
<body>
<main class="flex-grow-1">
  <div class="admin-container">
    <h1 class="admin-title text-center">Zona de Administración</h1>
    <div class="admin-buttons d-flex justify-content-center flex-wrap gap-3 my-4">
      <a class="btn btn-primary btn-admin" href="#panel-libros">Gestión de Libros</a>
      <a class="btn btn-primary btn-admin" href="#panel-usuarios">Gestión de Usuarios</a>
      <a class="btn btn-primary btn-admin" href="#panel-suscripciones">Gestión de Suscripciones</a>
      <a class="btn btn-primary btn-admin" href="#panel-estadisticas">Estadísticas de Ventas</a>
       <a class="btn btn-primary btn-admin" href="../logout.php">Cerrar sesión</a>
    </div>

    <?php $f = flash(); if ($f): ?>
      <div class="alert alert-<?php echo e($f['type']); ?>"><?php echo e($f['msg']); ?></div>
    <?php endif; ?>

    <!-- resto de paneles de libros y usuarios idénticos -->

    <div id="panel-suscripciones" class="admin-panel">
      <h2>Gestión de Suscripciones</h2>
      <div class="mb-3">
        <p>Modificar el tipo de suscripción de un usuario.</p>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Suscripción</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $u2 = $conexion->query("SELECT id_usuario, nombre, email, tipo_suscripcion FROM usuarios ORDER BY id_usuario DESC");
          while ($uu = $u2->fetch_assoc()):
          ?>
            <tr>
              <td><?php echo e($uu['id_usuario']); ?></td>
              <td><?php echo e($uu['nombre']); ?></td>
              <td><?php echo e($uu['email']); ?></td>
              <td><?php echo e($uu['tipo_suscripcion']); ?></td>
              <td>
                <form method="post" class="d-inline-flex align-items-center">
                  <input type="hidden" name="action" value="cambiar_suscripcion">
                  <input type="hidden" name="id_usuario" value="<?php echo e($uu['id_usuario']); ?>">
                  <select name="tipo_suscripcion" class="form-select form-select-sm me-2" style="width:auto">
                    <option value="gratuita" <?php if($uu['tipo_suscripcion']==='gratuita') echo 'selected'; ?>>gratuita</option>
                    <option value="premium" <?php if($uu['tipo_suscripcion']==='premium') echo 'selected'; ?>>premium</option>
                  </select>
                  <button class="btn btn-sm btn-primary" type="submit">Actualizar</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="panel-estadisticas" class="admin-panel">
      <h2>Estadísticas de Ventas</h2>
      <p>Ventas totales por día.</p>
      <canvas id="ventasChart" height="120"></canvas>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  //?En esta sección de aquí se utiliza la libreria chart para crear las estadisticas
  const ctx = document.getElementById('ventasChart');
  if (ctx) {
    const chart = new Chart(ctx, {
      //?En concreto la estadistica se crea en este punto exacto
      type: 'bar',
      data: {
        labels: <?php echo json_encode($stats_labels); ?>,
        datasets: [{
          label: 'Ventas (€)',
          data: <?php echo json_encode($stats_values); ?>,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: { ticks: { autoSkip: true, maxRotation: 90, minRotation: 45 } },
          y: { beginAtZero: true }
        }
      }
    });
  }
  //?Por otra parte esta función sirve para validar 
  //?Es decir se activara si una persona accede aquí 
  //?Sin las credenciales necesarias 
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
</body>
</html>
