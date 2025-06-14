<?php
session_start();

// Comprobar que el monitor ha iniciado sesión correctamente
if (!isset($_SESSION['Nombre']) || !isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}
// Creamos 2 variables para guardar el nombre del monitor y su Id
// Estas variables se usan para mostrar el nombre del monitor en la página y para realizar consultas a la base de datos
$Nombre = $_SESSION['Nombre'];
$Id_monitor = $_SESSION['Id_monitor'];

require 'conexion.php';

// --- OBTENER LAS CLASES QUE IMPARTE EL MONITOR ---
$sql = "SELECT Id_clase, Nombre_clase, Capacidad_clase, Hora_clase, Dias_semana FROM clases WHERE Id_monitor = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $Id_monitor);
$stmt->execute();
$result = $stmt->get_result();

// Creamos un array vacio en la sesión llamado ClasesMonitor, que contendrá las clases que imparte el monitor
$_SESSION['ClasesMonitor'] = [];
// Recorremos el resultado de la consulta y guardamos cada fila en el array ClasesMonitor
while ($fila = $result->fetch_assoc()) { 
    $_SESSION['ClasesMonitor'][] = $fila;
}
// Los datos del array los pasamos a la variable $Clases
$Clases = $_SESSION['ClasesMonitor'];

// --- COMPROBAR SI EXISTEN CLASES EN EL SISTEMA ---
$res = $mysqli->query("SELECT COUNT(*) as total FROM clases");
$row = $res->fetch_assoc();
if ($row['total'] == 0) {
    $Clases = null; // No existen clases en el sistema
}

// --- GESTIÓN DE MENSAJES TOAST (notificaciones flotantes) ---
$toastClass = '';
$toastMsg = '';
if (isset($_GET['mensaje'])) {
    $toastClass = 'bg-primary text-white';
    if ($_GET['mensaje'] === 'clase_eliminada') {
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Clase eliminada correctamente.';
    } elseif ($_GET['mensaje'] === 'clase_añadida') {
        $toastClass = 'bg-purple text-white';
        $toastMsg = 'Te has añadido correctamente a la clase.';
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
    <title>Clases del Monitor</title>
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
    th {
        vertical-align: middle !important;
        text-align: left !important;
        white-space: normal;
    }
    </style>
</head>
<body class="bg-light">

    <!-- Contenedor para los mensajes toast (notificaciones flotantes) -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
            <!-- Si hay datos en mensaje, lo envia, mostrandolo por pantalla junto con la clase para que dependiendo de esta se vea de cierta forma u otra -->
            <?php if (isset($_GET['mensaje'])): ?>
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

    <!-- Contenido principal -->
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-purple">
                <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($Nombre); ?></h1>
            </div>
            <div class="card-body">
                <?php if ($Clases === null): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        No existen clases actualmente.
                    </div>
                <?php elseif (empty($Clases)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        No monitorizas ninguna clase actualmente.
                    </div>
                <?php else: ?>
                    <p class="text-center">Monitorizas las siguientes clases:</p>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Capacidad</th>
                                    <th scope="col">Hora</th>
                                    <th scope="col">Días</th>
                                    <th scope="col">Editar</th>
                                    <th scope="col">Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($Clases as $clase): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($clase['Nombre_clase']); ?></td>
                                        <td><?php echo htmlspecialchars($clase['Capacidad_clase']); ?></td>
                                        <td><?php echo htmlspecialchars($clase['Hora_clase']); ?></td>
                                        <td><?php echo htmlspecialchars($clase['Dias_semana']); ?></td>
                                        <td>
                                            <!-- Botón para editar la clase -->
                                            <a href="editar-clase-monitor.php?Id_clase=<?php echo $clase['Id_clase']; ?>"
                                               class="btn btn-warning btn-sm">
                                               Editar
                                            </a>
                                        </td>
                                        <td>
                                            <!-- Botón para eliminar la clase -->
                                            <a href="eliminar-clase-definitiva.php?Id_clase=<?php echo $clase['Id_clase']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Seguro que quieres eliminar esta clase? Esta acción no se puede deshacer.');">
                                               Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <!-- Botón para monitorizar (añadir) una nueva clase -->
                <a href="crear-clase-monitor.php" class="btn btn-purple">Crear Clase</a>
                <!-- Botón para volver al menú del monitor -->
                <a href="menu-monitor.php" class="btn btn-dark mx-2">Volver al Menú</a>
                <!-- Botón para cerrar sesión -->
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
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

            // Ordenar tabla al hacer clic en los th
            // Busca todas las celdas de encabezado (<th>) dentro del <thead> de una tabla con clase .table.
            document.querySelectorAll('.table thead th').forEach(function(th, colIndex) {
                // Se indica que el th es clickable
                th.style.cursor = 'pointer';
                // Cuando se clicka en un th
                th.addEventListener('click', function() {
                    //  Busca la tabla más cercana al encabezado
                    let table = th.closest('table');
                    // Obtiene todas las filas de la tabla y las convierte en un array para ordenarlas
                    let tbody = table.querySelector('tbody');
                    let rows = Array.from(tbody.querySelectorAll('tr'));
                    // Determina el sentido del orden: Si la columna ya estaba en orden ascendente (data-asc="true"), se cambia a descendente (false), y viceversa.
                    let asc = th.dataset.asc === 'true' ? false : true;
                    rows.sort(function(a, b) {
                        // Toma el texto de la columna clicada (por índice) en cada fila a y b. Usa .trim() para limpiar espacios en blanco.
                        let aText = a.children[colIndex].textContent.trim();
                        let bText = b.children[colIndex].textContent.trim();

                        // Si la columna es la de hora (ajusta el índice si cambia el orden de columnas)
                        if (colIndex === 3) { // 3 si la columna Hora es la cuarta, como es el caso
                            // Convierte HH:MM o HH:MM:SS a minutos totales para comparar
                            function horaToMinutos(hora) {
                                let partes = hora.split(':');
                                return parseInt(partes[0], 10) * 60 + parseInt(partes[1], 10);
                            }
                            let aMin = horaToMinutos(aText);
                            let bMin = horaToMinutos(bText);
                            return asc ? aMin - bMin : bMin - aMin;
                        }

                        // Si es número, compara como número
                        if (!isNaN(aText) && !isNaN(bText)) {
                            return asc ? aText - bText : bText - aText;
                        }
                        // Si es texto, compara como texto
                        return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
                    });
                    // Quita el orden de los demás th
                    table.querySelectorAll('th').forEach(t => t.removeAttribute('data-asc'));
                    // Marca el th actual con el estado de orden
                    th.setAttribute('data-asc', asc);
                    // Reemplaza el tbody con las filas ordenadas
                    // Vacía el <tbody> actual.
                    tbody.innerHTML = '';
                    // Agrega las filas ordenadas de nuevo, en el nuevo orden.
                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    </script>
</body>
</html>