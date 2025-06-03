<?php
session_start();
require 'conexion.php';

// Solo permite acceso a monitores autenticados
if (!isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}
// Se inicializa una variable llamada $mensaje y se le asigna un string vacío, esto, más adelante se usará para asignarle un valor, y con eso, mostrar un toast de cierta forma u otra.
$mensaje = '';
// Se inicializa la variable $toast como false. Esta se usará como un interruptor para saber si mostrar un toast (mensaje emergente) al usuario.
$toast = false;
// Si el formulario se ha enviado por método POST...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se obtiene y limpia el nombre de la clase del formulario, eliminando espacios al inicio y al final.
    $nombre = trim($_POST['nombre']);
    // Se convierte la capacidad y el id del monitor recibida en un número entero.
    $capacidad = intval($_POST['capacidad']);
    $id_monitor = intval($_POST['id_monitor']);
    $hora_clase = $_POST['hora_clase']; // Recoge la hora

    // Comprobar si ya existe una clase con ese nombre
    $sql_check = "SELECT COUNT(*) as total FROM clases WHERE Nombre_clase = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    // Si el total es mayor a 0, es decir, si el count a tomado una columna. Significa que ya existe una clase con ese nombre.
    if ($row_check['total'] > 0) {
        // Muestra el toast con un mensaje de error.
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Ya existe una clase con ese nombre.';
        // Si el nombre no está vacío, la capacidad está entre 1 y 20, y el id del monitor es válido..
    } elseif ($nombre && $capacidad >= 1 && $capacidad <= 20 && $id_monitor > 0 && $hora_clase) {
        // Inserta la nueva clase en la base de datos y muestra un toast de éxito.
        $sql = "INSERT INTO clases (Nombre_clase, Capacidad_clase, Id_monitor, Hora_clase) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("siis", $nombre, $capacidad, $id_monitor, $hora_clase);
        if ($stmt->execute()) {
            $toast = true;
            $toastClass = 'bg-success text-white';
            $toastMsg = 'Clase creada correctamente.';
        } else {
            $toast = true;
            $toastClass = 'bg-danger text-white';
            $toastMsg = 'Error al crear la clase.';
        }
    } else {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Rellena todos los campos correctamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Clase</title>
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
                <label for="hora_clase" class="form-label">Hora de la Clase</label>
                <input type="time" class="form-control" id="hora_clase" name="hora_clase" required>
            </div>
            <div class="mb-3">
                <label for="id_monitor" class="form-label">ID del Monitor</label>
                <!-- Con esto, no se podrá modificar el campo y además se autocompletará con el valor del ID del monitor con el que ha iniciado sesion -->
                <input type="number" class="form-control" id="id_monitor" name="id_monitor" value="<?php echo $_SESSION['Id_monitor']; ?>" readonly>
            </div>
            <button type="submit" class="btn btn-purple w-100">Crear Clase</button>
            <a href="clases-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
</body>
</html>