<?php
// Establezco conexión
require 'conexion.php';
session_start(); // Inicia la sesión

$Nombre = $_POST['nombre'];
$Contraseña = $_POST['contraseña'];
$sql = "SELECT * FROM usuarios WHERE Nombre='$Nombre' AND Contraseña='$Contraseña'";

// Ejecuto la sentencia y guardo su resultado en una variable
$resultado = $mysqli->query($sql);

$fila = $resultado->fetch_assoc();
if ($fila) {
    // Usuario encontrado en la tabla usuarios
    $Id_usuario = $fila['Id_usuario']; // <-- Primero asigna el valor
    $_SESSION['Nombre'] = $Nombre;
    $_SESSION['Id_usuario'] = $Id_usuario; // <-- Ahora sí, guarda el valor

    // Buscar el id_inscripcion en la tabla inscripciones
    $sql_inscripcion = "SELECT Id_inscripcion FROM inscripciones WHERE Id_usuario='$Id_usuario'";
    $resultado_inscripcion = $mysqli->query($sql_inscripcion);
    $fila_inscripcion = $resultado_inscripcion->fetch_assoc();

    if ($fila_inscripcion) {
        // Si el cliente tiene inscripción, buscar las clases asociadas
        $_SESSION['Id_inscripcion'] = $fila_inscripcion['Id_inscripcion']; // Guardar en la sesión
        $Id_inscripcion = $fila_inscripcion['Id_inscripcion'];

        // Buscar las clases en las que está inscrito el usuario
        $sql_clases = "SELECT Id_clase, Nombre_clase, Capacidad_clase, Id_monitor FROM clases WHERE Id_clase IN (
            SELECT Id_clase FROM inscripciones WHERE Id_inscripcion='$Id_inscripcion'
        )";
        $resultado_clases = $mysqli->query($sql_clases);

        if ($resultado_clases->num_rows > 0) {
            // Guardar las clases en la sesión
            $_SESSION['Clases'] = [];
            while ($fila_clase = $resultado_clases->fetch_assoc()) {
                $_SESSION['Clases'][] = $fila_clase; // Guardar cada clase en un array
            }
        } else {
            $_SESSION['Clases'] = []; // No hay clases asociadas
        }
    } else {
        $_SESSION['Clases'] = null; // No hay inscripción
    }

    // Redirigir a la página de clases
    header('Location: clases-usuarios.php');
    exit();
} else {
    // Verificar en la tabla monitores
    $sql = "SELECT * FROM monitores WHERE Nombre='$Nombre' AND Contraseña='$Contraseña'";
    $resultado = $mysqli->query($sql);
    $fila_monitor = $resultado->fetch_assoc();

    if ($fila_monitor) {
        $_SESSION['Nombre'] = $Nombre;
        $_SESSION['Id_monitor'] = $fila_monitor['Id_monitor']; // <-- Esto es para guardar el id del monitor  en una sesión
        header('Location: menu-monitor.php'); // Redirigir a otra página si es monitor
        exit();
    } else {
        // Redirigir a formulario.html con error y el nombre
        header('Location: formulario.html?error=' . urlencode($Nombre));
        exit();
    }
}
?>