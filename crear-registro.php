<?php
require 'conexion.php';
session_start();

// Obtener el próximo ID autoincremental para registros
$sql_next_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'gimnasio' AND TABLE_NAME = 'registros'";
$result_next_id = $mysqli->query($sql_next_id);
$row_next_id = $result_next_id->fetch_assoc();
$next_id = $row_next_id ? $row_next_id['AUTO_INCREMENT'] : '';

// Obtener todos los usuarios/clientes para el select
$sql_usuarios = "SELECT Id_usuario, Nombre, Apellido FROM usuarios";
$result_usuarios = $mysqli->query($sql_usuarios);

// Procesar el formulario
$toast = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];
    $id_monitor = $_SESSION['Id_monitor'];

    $sql_insert = "INSERT INTO registros (Hora_entrada, Hora_salida, Id_monitor, Id_cliente) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert);
    $stmt->bind_param("ssii", $hora_entrada, $hora_salida, $id_monitor, $id_cliente);

    if ($stmt->execute()) {
        $toast = true;
        $toastClass = 'bg-success text-white';
        $toastMsg = 'Registro creado correctamente.';
    } else {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Error al crear el registro.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Registro</title>
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
        <h2 class="mb-4 text-center">Crear Nuevo Registro</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="id_registro" class="form-label">ID de Registro</label>
                <input type="text" class="form-control" id="id_registro" name="id_registro" value="<?php echo $next_id; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="id_cliente" class="form-label">Cliente</label>
                <select class="form-control" id="id_cliente" name="id_cliente" required>
                    <option value="">Selecciona un cliente...</option>
                    <?php while($usuario = $result_usuarios->fetch_assoc()): ?>
                        <option value="<?php echo $usuario['Id_usuario']; ?>">
                            <?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido'] . ' (ID: ' . $usuario['Id_usuario'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" required>
            </div>
            <div class="mb-3">
                <label for="hora_salida" class="form-label">Hora de Salida</label>
                <input type="time" class="form-control" id="hora_salida" name="hora_salida" required>
            </div>
            <div class="mb-3">
                <label for="id_monitor" class="form-label">ID de Monitor</label>
                <input type="text" class="form-control" id="id_monitor" name="id_monitor" value="<?php echo $_SESSION['Id_monitor']; ?>" readonly>
            </div>
            <button type="submit" class="btn btn-purple w-100">Crear Registro</button>
            <a href="registros-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
</body>
</html>