<?php
// Establezco conexión
require 'conexion.php';
session_start(); // Inicia la sesión

// Verifico si se ha enviado el formulario, tomando los datos de $_POST (correo y contraseña)
$Correo = $_POST['correo'];
$Contraseña = $_POST['contraseña'];
// Seleccionamos los datos de la tabla usuarios donde el correo y la contraseña coincidan con los ingresados en el formulario
$sql = "SELECT * FROM usuarios WHERE Correo='$Correo'";

// Ejecuto la sentencia y guardo su resultado en una variable
$resultado = $mysqli->query($sql);
// Esta función devuelve un array asociativo, es decir, un array donde las claves son los nombres de las columnas de la tabla y los valores son los datos correspondientes de esa fila.
$fila = $resultado->fetch_assoc();
if ($fila && password_verify($Contraseña, $fila['Contraseña'])) {
    // Si $fila tiene datos, significa que el usuario fue encontrado en la tabla usuarios
    $Id_usuario = $fila['Id_usuario']; // Guardamos el Id_usuario extraído de la fila en una variable local
    $_SESSION['Nombre'] = $fila['Nombre']; // Guardamos el nombre del usuario en la sesión para usarlo en otras páginas
    $_SESSION['Id_usuario'] = $Id_usuario; // Guardamos el Id_usuario también en la sesión

     // Ahora buscamos si este usuario tiene alguna inscripción en la tabla inscripciones
    $sql_inscripcion = "SELECT Id_inscripcion FROM inscripciones WHERE Id_usuario='$Id_usuario'";
    // Ejecutamos la consulta y obtenemos el resultado
    $resultado_inscripcion = $mysqli->query($sql_inscripcion);
    // fetch_assoc() obtiene la siguiente fila del resultado como un array asociativo
    // Ejemplo: ['Id_inscripcion' => 5]
    $fila_inscripcion = $resultado_inscripcion->fetch_assoc();

    if ($fila_inscripcion) {
        // Si la consulta devolvió una fila, quiere decir que el usuario tiene inscripción
        $_SESSION['Id_inscripcion'] = $fila_inscripcion['Id_inscripcion']; // Guardamos el Id_inscripcion en la sesión
        $Id_inscripcion = $fila_inscripcion['Id_inscripcion']; // También en una variable local

        // Ahora buscamos todas las clases relacionadas a esta inscripción
        $sql_clases = "SELECT Id_clase, Nombre_clase, Capacidad_clase, Id_monitor FROM clases WHERE Id_clase IN (
            SELECT Id_clase FROM inscripciones WHERE Id_inscripcion='$Id_inscripcion'
        )";
        $resultado_clases = $mysqli->query($sql_clases); // Ejecutamos la consulta para obtener las clases

        if ($resultado_clases->num_rows > 0) {
            // Si hay clases asociadas, inicializamos un array vacío para guardar las clases
            $_SESSION['Clases'] = [];
             // fetch_assoc() lee fila por fila y devuelve un array asociativo con la info de la clase
            while ($fila_clase = $resultado_clases->fetch_assoc()) {
                // Por ejemplo $fila_clase = ['Id_clase' => 3, 'Nombre_clase' => 'Yoga', 'Capacidad_clase' => 15, 'Id_monitor' => 2]
                
                // Guardamos cada array con los datos de la clase dentro del array $_SESSION['Clases']
                // Es decir, construimos un array de arrays, con todas las clases que encontró
                $_SESSION['Clases'][] = $fila_clase; // Guardar cada clase en un array
            }
        } else {
            $_SESSION['Clases'] = []; // Si no hay clases asociadas, dejamos el array vacío para indicar que no hay clases
        }
    } else {
        $_SESSION['Clases'] = null; // Si no tiene inscripción, guardamos null para indicar que no está inscrito en ninguna clase
    }

    // Redirigir a la página de clases
    header('Location: clases-usuarios.php');
    exit();
} else {
    // Verificar en la tabla monitores por correo
    $sql = "SELECT * FROM monitores WHERE Correo='$Correo'";
    $resultado = $mysqli->query($sql);
    $fila_monitor = $resultado->fetch_assoc();

    if ($fila_monitor && password_verify($Contraseña, $fila_monitor['Contraseña'])) {
        // Guarda el nombre del monitor en una variable de sesión
        $_SESSION['Nombre'] = $fila_monitor['Nombre'];
        // Guarda el id del monitor en la sesión
        $_SESSION['Id_monitor'] = $fila_monitor['Id_monitor']; // <-- Esto es para guardar el id del monitor  en una sesión
        header('Location: menu-monitor.php'); // Redirigir a otra página si es monitor
        exit();
    } else {
        // Redirigir a formulario.html con error y el nombre, urlencode es una función de PHP que convierte el valor de $Correo a un formato seguro para usarlo en una URL.
        header('Location: formulario.html?error=' . urlencode($Correo));
        exit();
    }
}
?>