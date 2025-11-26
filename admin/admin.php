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
//^Compras realizadas en orden de fecha descendiente
$compras_res = $conexion->query("SELECT id_compra, id_usuario, id_libro, fecha_compra, precio_pagado FROM compras ORDER BY fecha_compra DESC LIMIT 1000");
$stats_data = [];
if ($compras_res) {
    while ($r = $compras_res->fetch_assoc()) {
        $m = date('Y-m', strtotime($r['fecha_compra']));
        if (!isset($stats_data[$m])) $stats_data[$m] = 0;
        $stats_data[$m] += (float)$r['precio_pagado'];
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

    <div id="panel-libros" class="admin-panel">
      <h2>Gestión de Libros</h2>
      <div class="row">
        <div class="col-md-5">
          <form method="post" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="add_libro">
            <div class="mb-2">
              <label class="form-label">Título</label>
              <input class="form-control" name="titulo" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Autor</label>
              <input class="form-control" name="autor" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="descripcion" rows="3"></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Fecha de publicación</label>
              <input class="form-control" name="fecha_publicacion" type="date">
            </div>
            <div class="mb-2">
              <label class="form-label">Categoría</label>
              <input class="form-control" name="categoria">
            </div>
            <div class="mb-2">
              <label class="form-label">Tipo de libro</label>
              <select class="form-select" name="tipo_libro">
                <option value="estandar">estandar</option>
                <option value="premium">premium</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio</label>
              <input class="form-control" name="precio" type="number" step="0.01" min="0" value="0.00">
            </div>
            <button class="btn btn-success" type="submit">Añadir Libro</button>
          </form>
        </div>
        <div class="col-md-7">
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Título</th>
                  <th>Autor</th>
                  <th>Tipo</th>
                  <th>Precio</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
              <?php while ($libro = $libros_res->fetch_assoc()): ?>
                <tr>
                  <td><?php echo e($libro['id_libro']); ?></td>
                  <td><?php echo e($libro['titulo']); ?></td>
                  <td><?php echo e($libro['autor']); ?></td>
                  <td><?php echo e($libro['tipo_libro']); ?></td>
                  <td><?php echo number_format((float)$libro['precio'],2,',','.'); ?> €</td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLibroModal<?php echo e($libro['id_libro']); ?>">Editar</button>
                    <form method="post" style="display:inline-block" onsubmit="return confirm('Eliminar libro ID <?php echo e($libro['id_libro']); ?>?')">
                      <input type="hidden" name="action" value="delete_libro">
                      <input type="hidden" name="id_libro" value="<?php echo e($libro['id_libro']); ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                    </form>
                  </td>
                </tr>

                <div class="modal fade" id="editLibroModal<?php echo e($libro['id_libro']); ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <form method="post">
                        <input type="hidden" name="action" value="edit_libro">
                        <input type="hidden" name="id_libro" value="<?php echo e($libro['id_libro']); ?>">
                        <div class="modal-header">
                          <h5 class="modal-title">Editar libro <?php echo e($libro['id_libro']); ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-2">
                            <label class="form-label">Título</label>
                            <input class="form-control" name="titulo" value="<?php echo e($libro['titulo']); ?>" required>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Autor</label>
                            <input class="form-control" name="autor" value="<?php echo e($libro['autor']); ?>" required>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion"><?php echo e($libro['descripcion']); ?></textarea>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Fecha de publicación</label>
                            <input class="form-control" name="fecha_publicacion" type="date" value="<?php echo e($libro['fecha_publicacion']); ?>">
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Categoría</label>
                            <input class="form-control" name="categoria" value="<?php echo e($libro['categoria']); ?>">
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Tipo de libro</label>
                            <select class="form-select" name="tipo_libro">
                              <option value="estandar" <?php if($libro['tipo_libro']==='estandar') echo 'selected'; ?>>estandar</option>
                              <option value="premium" <?php if($libro['tipo_libro']==='premium') echo 'selected'; ?>>premium</option>
                            </select>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Precio</label>
                            <input class="form-control" name="precio" type="number" step="0.01" min="0" value="<?php echo e($libro['precio']); ?>">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                          <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div id="panel-usuarios" class="admin-panel">
      <h2>Gestión de Usuarios</h2>
      <div class="row">
        <div class="col-md-5">
          <form method="post">
            <input type="hidden" name="action" value="add_usuario">
            <div class="mb-2">
              <label class="form-label">Nombre</label>
              <input class="form-control" name="nombre" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input class="form-control" name="email" type="email" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Contraseña</label>
              <input class="form-control" name="contraseña" type="password" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Tipo de suscripción</label>
              <select class="form-select" name="tipo_suscripcion">
                <option value="gratuita">gratuita</option>
                <option value="premium">premium</option>
              </select>
            </div>
            <button class="btn btn-success" type="submit">Añadir Usuario</button>
          </form>
        </div>
        <div class="col-md-7">
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Suscripción</th>
                  <th>Registro</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
              <?php while ($usuario = $usuarios_res->fetch_assoc()): ?>
                <tr>
                  <td><?php echo e($usuario['id_usuario']); ?></td>
                  <td><?php echo e($usuario['nombre']); ?></td>
                  <td><?php echo e($usuario['email']); ?></td>
                  <td><?php echo e($usuario['tipo_suscripcion']); ?></td>
                  <td><?php echo e($usuario['fecha_registro']); ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUsuarioModal<?php echo e($usuario['id_usuario']); ?>">Editar</button>
                    <form method="post" style="display:inline-block" onsubmit="return confirm('Eliminar usuario ID <?php echo e($usuario['id_usuario']); ?>?')">
                      <input type="hidden" name="action" value="delete_usuario">
                      <input type="hidden" name="id_usuario" value="<?php echo e($usuario['id_usuario']); ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                    </form>
                  </td>
                </tr>

                <div class="modal fade" id="editUsuarioModal<?php echo e($usuario['id_usuario']); ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="post">
                        <input type="hidden" name="action" value="edit_usuario">
                        <input type="hidden" name="id_usuario" value="<?php echo e($usuario['id_usuario']); ?>">
                        <div class="modal-header">
                          <h5 class="modal-title">Editar usuario <?php echo e($usuario['id_usuario']); ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-2">
                            <label class="form-label">Nombre</label>
                            <input class="form-control" name="nombre" value="<?php echo e($usuario['nombre']); ?>" required>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input class="form-control" name="email" type="email" value="<?php echo e($usuario['email']); ?>" required>
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Nueva contraseña (dejar en blanco para no cambiar)</label>
                            <input class="form-control" name="contraseña" type="password">
                          </div>
                          <div class="mb-2">
                            <label class="form-label">Tipo de suscripción</label>
                            <select class="form-select" name="tipo_suscripcion">
                              <option value="gratuita" <?php if($usuario['tipo_suscripcion']==='gratuita') echo 'selected'; ?>>gratuita</option>
                              <option value="premium" <?php if($usuario['tipo_suscripcion']==='premium') echo 'selected'; ?>>premium</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                          <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

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
      <p>Ventas totales por mes.</p>
      <canvas id="ventasChart" height="120"></canvas>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const ctx = document.getElementById('ventasChart');
  if (ctx) {
    const chart = new Chart(ctx, {
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
          y: { beginAtZero: true }
        }
      }
    });
  }
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
