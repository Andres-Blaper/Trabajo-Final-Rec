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
    $capacidad = intval($_POST['capacidad']);
    $id_monitor = intval($_POST['id_monitor']);

    // Comprobar si ya existe una clase con ese nombre
    $sql_check = "SELECT COUNT(*) as total FROM clases WHERE Nombre_clase = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    if ($row_check['total'] > 0) {
        $mensaje = '<div class="alert alert-warning mt-3">Ya existe una clase con ese nombre.</div>';
    } elseif ($nombre && $capacidad >= 1 && $capacidad <= 20 && $id_monitor > 0) {
        $sql = "INSERT INTO clases (Nombre_clase, Capacidad_clase, Id_monitor) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sii", $nombre, $capacidad, $id_monitor);
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success mt-3">Clase creada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger mt-3">Error al crear la clase.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning mt-3">Rellena todos los campos correctamente.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Clase</title>
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
        <h2 class="mb-4 text-center">Crear Nueva Clase</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Clase</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" max="20" required>
            </div>
            <div class="mb-3">
                <label for="id_monitor" class="form-label">ID del Monitor</label>
                <input type="number" class="form-control" id="id_monitor" name="id_monitor" value="<?php echo $_SESSION['Id_monitor']; ?>" readonly>
            </div>
            <button type="submit" class="btn btn-purple w-100">Crear Clase</button>
            <a href="clases-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
        <?php echo $mensaje; ?>
    </div>
</body>
</html>