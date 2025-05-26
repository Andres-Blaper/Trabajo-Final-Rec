<?php
require 'conexion.php';
session_start();

$mensaje = '';
$toast = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    // Comprobar si ya existe un usuario con ese correo
    $sql_check = "SELECT COUNT(*) as total FROM usuarios WHERE Correo = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    // Comprobar si ya existe un usuario con ese nombre y apellido
    $sql_check_nombre = "SELECT COUNT(*) as total FROM usuarios WHERE Nombre = ? AND Apellido = ?";
    $stmt_check_nombre = $mysqli->prepare($sql_check_nombre);
    $stmt_check_nombre->bind_param("ss", $nombre, $apellido);
    $stmt_check_nombre->execute();
    $result_check_nombre = $stmt_check_nombre->get_result();
    $row_check_nombre = $result_check_nombre->fetch_assoc();

    if ($row_check['total'] > 0) {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Ya existe un usuario con ese correo.';
    } elseif ($row_check_nombre['total'] > 0) {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Ya existe un usuario con ese nombre y apellido.';
    } elseif ($nombre && $apellido && $contrasena && $correo) {
        $sql = "INSERT INTO usuarios (Nombre, Apellido, Contraseña, Correo) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $apellido, $contrasena, $correo);
        if ($stmt->execute()) {
            $toast = true;
            $toastClass = 'bg-success text-white';
            $toastMsg = 'Usuario creado correctamente.';
        } else {
            $toast = true;
            $toastClass = 'bg-danger text-white';
            $toastMsg = 'Error al crear el usuario.';
        }
    } else {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Rellena todos los campos correctamente.';
    }
}

// Obtener el próximo ID autoincremental
$sql_next_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'gimnasio' AND TABLE_NAME = 'usuarios'";
$result_next_id = $mysqli->query($sql_next_id);
$row_next_id = $result_next_id->fetch_assoc();
$next_id = $row_next_id ? $row_next_id['AUTO_INCREMENT'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="icon" type="image/png" href="peso.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ede9fe; }
        .form-container { max-width: 400px; margin: 60px auto; }
        .btn-purple {
            background: #7c3aed;
            color: #fff;
            border: none;
        }
        .btn-purple:hover {
            background: #5b21b6;
            color: #fff;
        }
    </style>
</head>
<body>
    <?php if ($toast): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
            <div id="mainToast" class="toast align-items-center <?php echo $toastClass; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $toastMsg; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('mainToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });
        </script>
    <?php endif; ?>
    <div class="form-container card shadow p-4 rounded-4">
        <h2 class="mb-4 text-center">Crear Nuevo Usuario</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="id_usuario" class="form-label">ID de Usuario</label>
                <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo $next_id; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" name="correo" readonly placeholder="nombre.apellido@email.com">
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" minlength="5" required>
            </div>  
            <button type="submit" class="btn btn-purple w-100">Crear Usuario</button>
            <a href="usuarios-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
    <script>
function quitarTildes(texto) {
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}
function generarCorreo() {
    let nombre = quitarTildes(document.getElementById('nombre').value.trim().toLowerCase().replace(/\s+/g, ''));
    let apellido = quitarTildes(document.getElementById('apellido').value.trim().toLowerCase().replace(/\s+/g, ''));
    if(nombre && apellido) {
        document.getElementById('correo').value = nombre + '.' + apellido + '@email.com';
    } else {
        document.getElementById('correo').value = '';
    }
}
document.getElementById('nombre').addEventListener('input', generarCorreo);
document.getElementById('apellido').addEventListener('input', generarCorreo);
</script>
</body>
</html>