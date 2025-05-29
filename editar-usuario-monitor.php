<?php
require 'conexion.php';

// Recoge el ID del usuario desde la URL
$Id_usuario = isset($_GET['Id_usuario']) ? intval($_GET['Id_usuario']) : 0;

// Si no hay ID, muestra error
if ($Id_usuario <= 0) {
    echo "<div class='alert alert-danger text-center mt-5'>ID de usuario no válido.</div>";
    exit;
}

// Si se envió el formulario, actualiza los datos
$toast = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $contrasena = trim($_POST['contrasena']);
    $correo = trim($_POST['correo']);

    $stmt = $mysqli->prepare("UPDATE usuarios SET Nombre = ?, Apellido = ?, Contraseña = ?, Correo = ? WHERE Id_usuario = ?");
    $stmt->bind_param("ssssi", $nombre, $apellido, $contrasena, $correo, $Id_usuario);
    if ($stmt->execute()) {
        $toast = "Usuario actualizado correctamente.";
        $toast_type = "success";
    } else {
        $toast = "Error al actualizar el usuario.";
        $toast_type = "danger";
    }
}

// Consulta la base de datos para obtener los datos actualizados del usuario
$stmt = $mysqli->prepare("SELECT Id_usuario, Nombre, Apellido, Contraseña, Correo FROM usuarios WHERE Id_usuario = ?");
$stmt->bind_param("i", $Id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo "<div class='alert alert-danger text-center mt-5'>Usuario no encontrado.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        body { background: #ede9fe; min-height: 100vh; }
        .form-container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 1.5rem; box-shadow: 0 8px 32px rgba(161, 22, 253, 0.13); padding: 2.5rem 2rem 2rem 2rem; }
        .btn-purple { background: #7c3aed; color: #fff; border: none; }
        .btn-purple:hover { background: #5b21b6; color: #fff; }
    </style>
</head>
<body>
    <div class="form-container card shadow p-4 rounded-4">
        <h2 class="mb-4 text-center">Editar Usuario</h2>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="id_usuario" class="form-label">ID de Usuario</label>
                <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($usuario['Id_usuario']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['Apellido']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['Correo']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="text" class="form-control" id="contrasena" name="contrasena" value="<?php echo htmlspecialchars($usuario['Contraseña']); ?>" minlength="5" required>
            </div>
            <button type="submit" class="btn btn-purple w-100">Actualizar Datos</button>
            <a href="usuarios-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>

    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div id="mainToast" class="toast align-items-center text-bg-<?php echo isset($toast_type) ? $toast_type : 'success'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1800">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo htmlspecialchars($toast); ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Quitar tildes y generar correo automáticamente
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

    <?php if ($toast): ?>
        var toast = new bootstrap.Toast(document.getElementById('mainToast'));
        toast.show();
        setTimeout(function() {
            window.location.href = "usuarios-monitor.php";
        }, 1800);
    <?php endif; ?>
    </script>
</body>
</html>