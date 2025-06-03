<?php
require 'conexion.php';
session_start();
// Si no tiene datos de sesión, especificamente el nombre e Id del usuario, redirige al formulario de inicio de sesión
if (!isset($_SESSION['Nombre']) || !isset($_SESSION['Id_usuario'])) {
    header('Location: formulario.html');
    exit();
}
// Creamos una variable para guardar el id del usuario
$Id_usuario = $_SESSION['Id_usuario'];

// Verifica que los datos se hayan enviado por el método POST, asi no acepta datos enviados por método GET y que exista el Id_clase, no es como tal completamente necesario, pero asi es más seguro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Id_clase'])) {
    // Convertir el Id_clase a entero
    $Id_clase = intval($_POST['Id_clase']);

    // Obtener el Id_monitor de la clase seleccionada
    $sql_monitor = "SELECT Id_monitor FROM clases WHERE Id_clase = ?";
    $stmt_monitor = $mysqli->prepare($sql_monitor);
    // Pasamos el id de la clase a la consulta
    $stmt_monitor->bind_param("i", $Id_clase);
    // Ejecutamos la consulta
    $stmt_monitor->execute();
    // Obtenemos el resultado de la consulta
    $result_monitor = $stmt_monitor->get_result();
    // Esto extrae una fila del resultado de la consulta SQL y la guarda como un array asociativo.
    $row_monitor = $result_monitor->fetch_assoc();

    // Si row_monitor es null, significa que no se encontró el monitor para esa clase
    // Si se encontró, extraemos el Id_monitor
    // Es lo mismo que hacer: if ($row_monitor) {
    //     $Id_monitor = $row_monitor['Id_monitor'];
    // } else {
    //     $Id_monitor = null;
    // }
    $Id_monitor = $row_monitor ? $row_monitor['Id_monitor'] : null;

    // Obtener la capacidad máxima de la clase
    $sql_cap = "SELECT Capacidad_clase FROM clases WHERE Id_clase = ?";
    $stmt_cap = $mysqli->prepare($sql_cap);
    $stmt_cap->bind_param("i", $Id_clase);
    $stmt_cap->execute();
    $result_cap = $stmt_cap->get_result();
    $capacidadClase = $result_cap->fetch_assoc();
    $capacidad_max = $capacidadClase ? intval($capacidadClase['Capacidad_clase']) : 0;

    // Contar inscritos actuales en la clase
    $sql_count = "SELECT COUNT(*) AS inscritos FROM inscripciones WHERE Id_clase = ?";
    $stmt_count = $mysqli->prepare($sql_count);
    $stmt_count->bind_param("i", $Id_clase);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $inscritosClase = $result_count->fetch_assoc();
    $inscritos = $inscritosClase ? intval($inscritosClase['inscritos']) : 0;

    // Si la clase está llena, redirige con mensaje de error
    if ($inscritos >= $capacidad_max) {
        header('Location: clases-usuarios.php?mensaje=sin_capacidad');
        exit();
    }

    $sql_insert = "INSERT INTO inscripciones (Id_usuario, Id_monitor, Id_clase) VALUES (?, ?, ?)";
    $stmt_insert = $mysqli->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $Id_usuario, $Id_monitor, $Id_clase);
    if ($stmt_insert->execute()) {
        header('Location: clases-usuarios.php?mensaje=clase_inscrita');
        exit();
    } else {
        header('Location: clases-usuarios.php?mensaje=error');
        exit();
    }
}

// Obtener las clases en las que NO está inscrito el usuario
$sql = "SELECT Id_clase, Nombre_clase FROM clases 
        WHERE Id_clase NOT IN (
            SELECT Id_clase FROM inscripciones WHERE Id_usuario = ?
        )";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $Id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Clase</title>
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
        /* Estilo morado para el select y options */
        .form-select.bg-purple, .form-select.bg-purple:focus {
            background-color: #7c3aed !important;
            color: #fff !important;
            border: none;
        }
        .form-select.bg-purple option {
            background: #a78bfa;
            color: #3b0764;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header bg-purple">
            <h2 class="text-center">Inscribirse en una nueva clase</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="Id_clase" class="form-label">Selecciona una clase:</label>
                    <select name="Id_clase" id="Id_clase" class="form-select bg-purple" required>
                        <option value="" disabled selected>Elige una clase</option>
                        <?php while ($clase = $result->fetch_assoc()): ?>
                            <option value="<?php echo $clase['Id_clase']; ?>">
                                <?php echo htmlspecialchars($clase['Nombre_clase']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-purple">Inscribirme</button>
                <a href="clases-usuarios.php" class="btn btn-outline-danger">Volver</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>