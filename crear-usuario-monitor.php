<?php
session_start();
require 'conexion.php';

// Solo permite acceso a monitores autenticados
if (!isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $contrasena = trim($_POST['contrasena']);
    $correo = trim($_POST['correo']);

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
        $mensaje = '<div class="alert alert-warning mt-3">Ya existe un usuario con ese correo.</div>';
    } elseif ($row_check_nombre['total'] > 0) {
        $mensaje = '<div class="alert alert-warning mt-3">Ya existe un usuario con ese nombre y apellido.</div>';
    } elseif ($nombre && $apellido && $contrasena && $correo) {
        $sql = "INSERT INTO usuarios (Nombre, Apellido, Contraseña, Correo) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $apellido, $contrasena, $correo);
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success mt-3">Usuario creado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger mt-3">Error al crear el usuario.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning mt-3">Rellena todos los campos correctamente.</div>';
    }
}

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
                <input type="email" class="form-control" id="correo" name="correo" readonly placeholder="nombre.apellido@gmail.com">
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" minlength="5" required>
            </div>  
            <button type="submit" class="btn btn-purple w-100">Crear Usuario</button>
            <a href="usuarios-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
        <?php echo $mensaje; ?>
    </div>
    <script>
document.getElementById('nombre').addEventListener('input', generarCorreo);
document.getElementById('apellido').addEventListener('input', generarCorreo);

function generarCorreo() {
    let nombre = document.getElementById('nombre').value.trim().toLowerCase().replace(/\s+/g, '');
    let apellido = document.getElementById('apellido').value.trim().toLowerCase().replace(/\s+/g, '');
    if(nombre && apellido) {
        document.getElementById('correo').value = nombre + '.' + apellido + '@gmail.com';
    } else {
        document.getElementById('correo').value = '';
    }
}
</script>
</body>
</html>