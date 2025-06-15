<?php
require 'conexion.php';

// Recoge el ID de la clase desde la URL.
// Primero verifica si existe el parámetro 'Id_clase' en la URL (por ejemplo: pagina.php?Id_clase=3).
// Si existe, lo convierte a entero con intval() para evitar que sea texto o caracteres especiales.
// Si no existe, se asigna 0 como valor por defecto.
// Esto es una forma segura de evitar errores y validar el dato.
$Id_clase = isset($_GET['Id_clase']) ? intval($_GET['Id_clase']) : 0;


// Verifica si el ID de la clase es menor o igual a 0.
// Si es así, muestra un mensaje de error y detiene la ejecución del script con exit().
// Esto previene que se intente consultar la base de datos con un ID inválido.
if ($Id_clase <= 0) {
    echo "<div class='alert alert-danger text-center mt-5'>ID de clase no válido.</div>";
    exit;
}

// Inicializa la variable $toast como una cadena vacía.
// Esta variable se usará para almacenar un mensaje que se mostrará al usuario si se actualiza la clase.
$toast = "";
// Verifica si el formulario fue enviado usando el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos del formulario usando $_POST.
    $nombre = $_POST['nombreClase'];
    $capacidad = $_POST['capacidadClase'];
    $hora = $_POST['horaClase'];
    $dias_semana = isset($_POST['dias_semana']) ? implode(', ', $_POST['dias_semana']) : '';
    // Prepara la consulta para acutalizar los datos en la tabla clases.
    if (empty($dias_semana)) {
        $toast = "Debes seleccionar al menos un día de la semana.";
        $toast_type = "danger";
    } else {
        $stmt = $mysqli->prepare("UPDATE clases SET Nombre_clase = ?, Capacidad_clase = ?, Hora_clase = ?, Dias_semana = ? WHERE Id_clase = ?");
        $stmt->bind_param("sissi", $nombre, $capacidad, $hora, $dias_semana, $Id_clase);
        if ($stmt->execute()) {
            $toast = "Clase actualizada correctamente.";
            $toast_type = "success";
        } else {
            $toast = "Error al actualizar la clase.";
            $toast_type = "danger";
        }
    }
}

// Consulta la base de datos para obtener los datos actualizados de la clase
$stmt = $mysqli->prepare("SELECT Id_clase, Nombre_clase, Capacidad_clase, Id_monitor, Hora_clase, Dias_semana FROM clases WHERE Id_clase = ?");
$stmt->bind_param("i", $Id_clase);
$stmt->execute();
$result = $stmt->get_result();
$clase = $result->fetch_assoc();

// Verifica si no se encontró ninguna clase con ese ID (es decir, $clase es false).
// Esto podría suceder si alguien intenta acceder a una clase que no existe.
// En ese caso, se muestra un mensaje de error y se termina la ejecución.
if (!$clase) {
    echo "<div class='alert alert-danger text-center mt-5'>Clase no encontrada.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Clase</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="icon" type="image/png" href="peso.png">
    <style>
        body {
            background: #f3f0ff;
            min-height: 100vh;
        }
        .form-container {
            background: #fff;
            border-radius: 2rem;
            box-shadow: 0 8px 32px rgba(161, 22, 253, 0.13);
            padding: 2.5rem 2rem 2rem 2rem;
            max-width: 500px;
            margin: 4rem auto;
        }
        .btn-purple {
            background: #7c3aed;
            color: #fff;
            border: none;
        }
        .btn-purple:hover {
            background: #a116fd;
            color: #fff;
        }
        .form-label {
            font-size: 1.2rem;
            margin-top: 0.3rem;
        }
        .form-control:focus {
            border-color: #a116fd;
            box-shadow: 0 0 8px #a116fd33;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="text-center mb-4">Editar Clase</h1>
        <form method="post">
            <input type="hidden" name="idClase" value="<?php echo htmlspecialchars($clase['Id_clase']); ?>">
            <div class="mb-3">
                <label for="nombreClase" class="form-label">Nombre de la Clase</label>
                <input type="text" class="form-control" id="nombreClase" name="nombreClase" value="<?php echo htmlspecialchars($clase['Nombre_clase']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="capacidadClase" class="form-label">Capacidad</label>
                <!-- Importante, se limita la capacidad de 1 a 20, para continuar con ese limite y que tenga sentido, aunque no lo he tenido en cuenta a los usuarios intentar inscribirse a clases. -->
                <input type="number" class="form-control" id="capacidadClase" name="capacidadClase"
                    value="<?php echo htmlspecialchars($clase['Capacidad_clase']); ?>" required min="1" max="20">
            </div>
                <div class="mb-3">
                    <label for="horaClase" class="form-label">Hora de la Clase</label>
                    <input type="time" class="form-control" id="horaClase" name="horaClase"
                        value="<?php echo substr(htmlspecialchars($clase['Hora_clase']), 0, 5); ?>" required step="60">
                </div>
            <div class="mb-3">
                <label class="form-label">Días de la semana</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    $dias_guardados = explode(', ', $clase['Dias_semana']);
                    foreach ($dias as $dia): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="dias_semana[]" value="<?php echo $dia; ?>"
                                id="dia_<?php echo $dia; ?>"
                                <?php if (in_array($dia, $dias_guardados)) echo 'checked'; ?>>
                            <label class="form-check-label" for="dia_<?php echo $dia; ?>"><?php echo $dia; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="idMonitor" value="<?php echo htmlspecialchars($clase['Id_monitor']); ?>">
            <button type="submit" class="btn btn-purple w-100 mb-2">Actualizar Datos</button>
            <a href="clases-monitor.php" class="btn btn-secondary w-100">Volver</a>
        </form>
    </div>

    <!-- Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <!-- Verifica si la variable $toast_type existe y no es null. Si sí existe, usa su valor (por ejemplo: 'danger', 'info', 'warning'...). Si no existe, entonces usa 'success' por defecto.Porque evita errores y asegura que el toast tenga siempre una clase de estilo válida, incluso si $toast_type no fue definida.-->
        <div id="liveToast" class="toast align-items-center text-bg-<?php echo isset($toast_type) ? $toast_type : 'success'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo htmlspecialchars($toast); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($toast): ?>
    <script>
        var toast = new bootstrap.Toast(document.getElementById('liveToast'));
        toast.show();
        setTimeout(function() {
            window.location.href = "clases-monitor.php";
        }, 1800);
    </script>
    <?php endif; ?>
</body>
</html>