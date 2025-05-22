<?php
session_start();

if (!isset($_SESSION['Nombre']) || !isset($_SESSION['Id_usuario'])) {
    header('Location: formulario.html');
    exit();
}

$Nombre = $_SESSION['Nombre'];
$Id_usuario = $_SESSION['Id_usuario'];

require 'conexion.php';

// Actualizar las clases del usuario cada vez que se carga la página
$sql = "SELECT c.Id_clase, c.Nombre_clase, c.Capacidad_clase, c.Id_monitor
        FROM clases c
        INNER JOIN inscripciones i ON c.Id_clase = i.Id_clase
        WHERE i.Id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $Id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['Clases'] = [];
while ($fila = $result->fetch_assoc()) {
    $_SESSION['Clases'][] = $fila;
}

$Clases = $_SESSION['Clases'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clases del Usuario</title>
    <!-- Bootstrap CSS -->
    <link rel="icon" type="image/png" href="peso.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .bg-purple {
        background: #7c3aed !important;
        color: #fff !important;
    }
    .btn-purple {
        background: #7c3aed;
        color: #fff;
        border: none;
    }
    .btn-purple:hover {
        background: #5b21b6;
        color: #fff;
    }
    .rounded-4 {
        border-radius: 1.5rem !important;
    }
    body.bg-light {
        background: linear-gradient(135deg, #ede9fe 0%, #c7d2fe 100%);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }
    body.bg-light::before {
        content: "";
        position: absolute;
        top: -100px;
        left: -100px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle at 30% 30%, #7c3aed55 0%, transparent 70%);
        z-index: 0;
    }
    body.bg-light > .container,
    body.bg-light > .position-relative {
        position: relative;
        z-index: 1;
    }
</style>
</head>
<body class="bg-light">

    <!-- Toast container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
            <?php if (isset($_GET['mensaje'])): ?>
                <?php
                    $toastClass = 'bg-primary text-white';
                    $toastMsg = '';
                    if ($_GET['mensaje'] === 'clase_eliminada') {
                        $toastClass = 'bg-danger text-white';
                        $toastMsg = 'Clase eliminada correctamente.';
                    } elseif ($_GET['mensaje'] === 'clase_inscrita') {
                        $toastClass = 'bg-purple text-white';
                        $toastMsg = 'Te has inscrito correctamente en la clase.';
                    } elseif ($_GET['mensaje'] === 'error') {
                        $toastClass = 'bg-danger text-white';
                        $toastMsg = 'Ocurrió un error al procesar la solicitud.';
                    }
                ?>
                <div class="toast align-items-center <?php echo $toastClass; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" id="mainToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?php echo $toastMsg; ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-purple">
                <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($Nombre); ?></h1>
            </div>
            <div class="card-body">
                <?php if ($Clases === null): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        No estás inscrito a ninguna clase.
                    </div>
                <?php elseif (empty($Clases)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Tienes una inscripción, pero no estás inscrito en ninguna clase.
                    </div>
                <?php else: ?>
                    <p class="text-center">Estás inscrito en las siguientes clases:</p>
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID de la Clase</th>
                                <th scope="col">Nombre de la Clase</th>
                                <th scope="col">Capacidad</th>
                                <th scope="col">ID del Monitor</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Clases as $clase): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($clase['Id_clase']); ?></td>
                                    <td><?php echo htmlspecialchars($clase['Nombre_clase']); ?></td>
                                    <td><?php echo htmlspecialchars($clase['Capacidad_clase']); ?></td>
                                    <td><?php echo htmlspecialchars($clase['Id_monitor']); ?></td>
                                    <td>
                                        <a href="eliminar-clase-usuario.php?Id_clase=<?php echo $clase['Id_clase']; ?>" 
                                        class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="añadir-clase-usuario.php" class="btn btn-purple">Añadir Clase</a>
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
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
</body>
</html>