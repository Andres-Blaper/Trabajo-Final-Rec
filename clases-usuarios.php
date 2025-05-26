<?php
session_start();

// Comprobamos que el usuario ha iniciado sesión correctamente
if (!isset($_SESSION['Nombre']) || !isset($_SESSION['Id_usuario'])) {
    header('Location: formulario.html');
    exit();
}

// Guardamos el nombre y el id del usuario en variables para usarlos más adelante
$Nombre = $_SESSION['Nombre'];
$Id_usuario = $_SESSION['Id_usuario'];

require 'conexion.php'; // Incluimos la conexión a la base de datos

// --- OBTENER LAS CLASES EN LAS QUE ESTÁ INSCRITO EL USUARIO ---
// Consulta SQL para obtener las clases en las que el usuario está inscrito
$sql = "SELECT c.Id_clase, c.Nombre_clase, c.Capacidad_clase, c.Id_monitor
        FROM clases c
        INNER JOIN inscripciones i ON c.Id_clase = i.Id_clase
        WHERE i.Id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $Id_usuario); // Pasamos el id del usuario a la consulta
$stmt->execute();
$result = $stmt->get_result();

// Guardamos las clases en la sesión y en una variable local
$_SESSION['Clases'] = [];
while ($fila = $result->fetch_assoc()) {
    // Por cada clase encontrada, la añadimos al array de clases
    $_SESSION['Clases'][] = $fila;
}
$Clases = $_SESSION['Clases']; // Variable local para trabajar más cómodo

// --- COMPROBAR SI EXISTEN CLASES EN EL SISTEMA ---
// Si no hay ninguna clase en la base de datos, ponemos $Clases a null
$res = $mysqli->query("SELECT COUNT(*) as total FROM clases");
$row = $res->fetch_assoc();
if ($row['total'] == 0) {
    $Clases = null; // No existen clases en el sistema
}

// --- GESTIÓN DE MENSAJES TOAST (notificaciones flotantes) ---
// Variables para el estilo y el mensaje del toast
$toastClass = '';
$toastMsg = '';
if (isset($_GET['mensaje'])) {
    $toastClass = 'bg-primary text-white'; // Clase por defecto
    // Cambiamos el mensaje y el color según el tipo de mensaje recibido por GET
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
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clases del Usuario</title>
    <!-- Bootstrap CSS para estilos rápidos y responsive -->
    <link rel="icon" type="image/png" href="peso.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Colores y estilos personalizados */
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

    <!-- Contenedor para los mensajes toast (notificaciones flotantes) -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
            <?php if (isset($_GET['mensaje'])): ?>
                <!-- Toast de Bootstrap, se muestra si hay mensaje -->
                <div class="toast align-items-center <?php echo $toastClass; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" id="mainToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?php echo $toastMsg; // Mostramos el mensaje del toast ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-purple">
                <!-- Saludo personalizado con el nombre del usuario -->
                <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($Nombre); ?></h1>
            </div>
            <div class="card-body">
                <?php if ($Clases === null): ?>
                    <!-- Si no existen clases en el sistema -->
                    <div class="alert alert-danger text-center" role="alert">
                        No existen clases actualmente.
                    </div>
                <?php elseif (empty($Clases)): ?>
                    <!-- Si existen clases pero el usuario no está inscrito en ninguna -->
                    <div class="alert alert-warning text-center" role="alert">
                        No estás inscrito a ninguna clase.
                    </div>
                <?php else: ?>
                    <!-- Si el usuario está inscrito en alguna clase, mostramos la tabla -->
                    <p class="text-center">Estás inscrito en las siguientes clases:</p>
                    <div class="table-responsive">
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
                                <!-- Recorremos cada clase en la que está inscrito el usuario -->
                                <tr>
                                    <td><?php echo htmlspecialchars($clase['Id_clase']); // ID de la clase ?></td>
                                    <td><?php echo htmlspecialchars($clase['Nombre_clase']); // Nombre de la clase ?></td>
                                    <td><?php echo htmlspecialchars($clase['Capacidad_clase']); // Capacidad de la clase ?></td>
                                    <td><?php echo htmlspecialchars($clase['Id_monitor']); // ID del monitor ?></td>
                                    <td>
                                        <!-- Botón para desapuntarse de la clase (elimina la inscripción) -->
                                        <a href="eliminar-clase-usuario.php?Id_clase=<?php echo $clase['Id_clase']; ?>" 
                                        class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; // Fin del foreach ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <!-- Botón para apuntarse a una nueva clase -->
                <a href="añadir-clase-usuario.php" class="btn btn-purple">Añadir Clase</a>
                <!-- Botón para cerrar sesión -->
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS para los componentes interactivos como Toast -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar el toast automáticamente si existe
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