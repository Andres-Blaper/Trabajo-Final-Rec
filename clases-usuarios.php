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
// Consulta SQL para obtener las clases en las que el usuario está inscrito, necesitamos unir entre las tablas 'clases' e 'inscripciones', junto con 'monitores' con un join para obtener los datos de la tabla inscripcion que tenga el id del usuario',
//  el AS Nombre_monitor permite distinguir el nombre del monitor en el resultado ya que hay varios 'Nombre' (el nombre del monitor y el nombre del usuario), y así evitamos confusiones.
$sql = "SELECT c.Id_clase, c.Nombre_clase, c.Capacidad_clase, c.Hora_clase, c.Dias_semana, m.Nombre AS Nombre_monitor
        FROM clases c
        INNER JOIN inscripciones i ON c.Id_clase = i.Id_clase
        INNER JOIN monitores m ON c.Id_monitor = m.Id_monitor
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
    } elseif ($_GET['mensaje'] === 'sin_capacidad') {
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'La clase ya no tiene capacidad.';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.3.4/dist/css/datepicker.min.css">
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
    /* Este selector aplica estilos solo a los elementos .container que son hijos directos de un <body> que tiene la clase bg-light. */
    body.bg-light > .container,
    body.bg-light > .position-relative {
        position: relative;
        z-index: 1;
    }
    .text-purple {
        color: #7c3aed !important;
    }
    .text-purple:hover {
        color: #5b21b6 !important;
    }
    /* Centra verticalmente el texto de los encabezados y lo alinea a la izquierda */
    th {
        vertical-align: middle !important;
        text-align: left !important;
    }
    </style>
</head>
<body class="bg-light">

    <!-- Contenedor para los mensajes toast (notificaciones flotantes) -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
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
                <!-- Saludo personalizado con el nombre del usuario, los htmlspecialchars me lo recomendó chatgpt para evitar inyecciones, y hacer el código más seguro -->
                <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($Nombre); ?></h1>
            </div>
            <div class="card-body">
                <?php 
                if ($Clases === null):
                ?>
                    <!-- Si no existen clases en el sistema, esto se ejecuta solo si $Clases es null (ni '', ni 0, ni false) gracias al === -->
                    <div class="alert alert-danger text-center" role="alert">
                        No existen clases actualmente.
                    </div>
                <?php 
                elseif (empty($Clases)): 
                ?>
                    <!-- Si existen clases pero el usuario no está inscrito en ninguna, tambien se podria poner como $Clases == 0 -->
                    <div class="alert alert-warning text-center" role="alert">
                        No estás inscrito a ninguna clase.
                    </div>
                <?php else: 
                    ?>
                    <!-- Si el usuario está inscrito en alguna clase, mostramos la tabla -->
                    <p class="text-center">Estás inscrito en las siguientes clases:</p>
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Capacidad</th>
                                <th scope="col">Hora</th>
                                <th scope="col">Días</th>
                                <th scope="col">Monitor</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Clases as $clase): ?>
                                <!-- Recorremos cada clase en la que está inscrito el usuario -->
                                <tr>
                                    <td><?php echo htmlspecialchars($clase['Nombre_clase']); // Nombre de la clase ?></td>
                                    <td><?php echo htmlspecialchars($clase['Capacidad_clase']); // Capacidad de la clase ?></td>
                                    <td><?php echo htmlspecialchars($clase['Hora_clase']); ?></td>
                                    <td><?php echo htmlspecialchars($clase['Dias_semana']); ?></td>
                                    <td><?php echo htmlspecialchars($clase['Nombre_monitor']); // Nombre del monitor ?></td>
                                    <td>
                                        <!-- Botón para desapuntarse de la clase (elimina la inscripción, no la clase. Y solo la seleccionada dentro del foreach, no todas a las que está inscrita) -->
                                        <a href="eliminar-clase-usuario.php?Id_clase=<?php echo $clase['Id_clase']; ?>" 
                                        class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php
                            endforeach; // Fin del foreach 
                            ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer d-flex flex-row align-items-center justify-content-center w-100 gap-3" style="flex-wrap: wrap;">
                <!-- Icono de calendario alineado a la izquierda -->
                <button type="button"
                    class="btn btn-link text-purple p-0 m-0"
                    style="font-size: 2.2rem; background: none; border: none; box-shadow: none;"
                    data-bs-toggle="modal" data-bs-target="#calendarioModal" title="Ver calendario de clases">
                    <i class="bi bi-calendar-event"></i>
                </button>
                <!-- Botón para apuntarse a una nueva clase -->
                <a href="añadir-clase-usuario.php" class="btn btn-purple">Añadir Clase</a>
                <!-- Botón para cerrar sesión, redirige a cerrarsesion.php -->
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <!-- Modal de Bootstrap para el calendario -->
    <div class="modal fade" id="calendarioModal" tabindex="-1" aria-labelledby="calendarioModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header bg-purple text-white">
            <h5 class="modal-title" id="calendarioModalLabel">Calendario de Clases</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div id="calendar"></div>
            <div id="clases-dia" class="mt-4"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS para los componentes interactivos como Toast -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.3.4/dist/js/datepicker-full.min.js"></script>
    <script>
        // Mostrar el toast automáticamente si existe
        // Cuando el HTML de la página ya esté completamente cargado (pero sin esperar imágenes o CSS), ejecuta esta función.
        window.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('mainToast');
            // Verifica si encontró ese elemento. Si no existe, no hace nada (evita errores).
            if (toastEl) {
                // Muestra el toast usando Bootstrap
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });

// Espera a que toda la estructura HTML de la página esté cargada antes de ejecutar el código JavaScript.
// El DOM (Document Object Model) es la representación en memoria de la estructura HTML de la página.
// Esto permite que el JS acceda y modifique los elementos de la página.
document.addEventListener('DOMContentLoaded', function() {

    // Crea un nuevo calendario en el elemento HTML que tiene el id 'calendar'.
    // 'const' es una forma de declarar una variable que no va a cambiar su valor.
    // Aquí, 'calendar' es una referencia al calendario que se muestra en pantalla.
    const calendar = new Datepicker(document.getElementById('calendar'), {
        language: 'es',         // El calendario estará en español.
        autohide: true,         // El calendario se oculta automáticamente al seleccionar una fecha.
        todayHighlight: true    // El día actual aparece resaltado en el calendario.
    });

    // Aquí se crea una lista (array) de objetos con la información de las clases del usuario.
    // Cada objeto representa una clase, con su fecha, nombre y hora.
    // Este array se rellena usando PHP, que genera el código JS con los datos de la base de datos.
    const clases = [
        <?php foreach ($Clases as $clase): ?>
        {
            fecha: '<?php echo date('Y-m-d'); ?>', // Fecha de la clase (actualmente siempre la de hoy, deberías cambiarlo si quieres la fecha real de la clase)
            nombre: '<?php echo htmlspecialchars($clase['Nombre_clase']); ?>', // Nombre de la clase
            hora: '<?php echo htmlspecialchars(substr($clase['Hora_clase'], 0, 5)); ?>' // Hora de la clase (solo horas y minutos)
        },
        <?php endforeach; ?>
    ];

    // Añade un "escuchador de eventos" al calendario.
    // Esto significa que cuando el usuario selecciona una fecha en el calendario, se ejecuta la función que hay dentro.
    document.getElementById('calendar').addEventListener('changeDate', function(e) {
        // Obtiene la fecha seleccionada por el usuario y la convierte a texto con el formato 'YYYY-MM-DD'.
        // 'const' declara una variable que no cambiará.
        // 'e' es el evento que ocurre cuando el usuario cambia la fecha.
        const fecha = e.detail.date.toISOString().slice(0,10);

        // Busca en el array 'clases' todas las clases que tienen la misma fecha que la seleccionada.
        // 'filter' crea un nuevo array solo con las clases de ese día.
        const clasesHoy = clases.filter(c => c.fecha === fecha);

        // 'let' declara una variable que puede cambiar su valor.
        // Aquí se usará para guardar el HTML que se va a mostrar al usuario.
        let html = '';

        // Si hay alguna clase ese día, recorre todas y añade su nombre y hora al HTML.
        if (clasesHoy.length > 0) {
            clasesHoy.forEach(c => {
                // Añade el nombre en negrita y la hora en gris.
                html += `<div class="fw-bold">${c.nombre}</div><div class="text-muted">${c.hora}</div>`;
            });
        } else {
            // Si no hay clases ese día, muestra un mensaje informativo.
            html = '<div class="text-secondary">No tienes clases este día.</div>';
        }

        // Busca el elemento HTML con id 'clases-dia' y pone dentro el HTML generado arriba.
        // Así, el usuario ve las clases de ese día (o el mensaje si no hay ninguna).
        document.getElementById('clases-dia').innerHTML = html;
    });
});
    </script>
</body>
</html>