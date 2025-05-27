<?php
require 'conexion.php';
session_start();
// Si no se encuentra la sesión iniciada, redirige al formulario de inicio de sesión
if (!isset($_SESSION['Id_usuario'])) {
    header('Location: formulario.html');
    exit();
}
// Guardamos la información/datos del usuario, en este caso la Id del usuario en variable Id_usuario
$Id_usuario = $_SESSION['Id_usuario'];

// Verifica si se recibió el parámetro 'Id_clase' por la URL (método GET)
if (isset($_GET['Id_clase'])) {
    $Id_clase = intval($_GET['Id_clase']); // Convierte el valor recibido a entero por seguridad

    // Elimina la inscripción del usuario a la clase
    $sql = "DELETE FROM inscripciones WHERE Id_usuario = ? AND Id_clase = ?"; // Prepara la consulta SQL para eliminar la inscripción
    $stmt = $mysqli->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("ii", $Id_usuario, $Id_clase); // Asocia los parámetros: ambos son enteros (i = integer)

    // Si ejecuta correctamente al consulta, nos redirige a clases-usuarios.php con un mensaje de éxito, asociando el valor del mensaje con 'clase_eliminada', para que el toast detecte ese valor y luego aparezca con cierta apariencia previamente asociada.
    if ($stmt->execute()) {
        header('Location: clases-usuarios.php?mensaje=clase_eliminada');
        exit();
    } else {
        // Si no, lo envia a clases-usuarios.php con un valor de 'error', para que el toast detecte ese valor y luego aparezca con cierta apariencia previamente asociada.
        header('Location: clases-usuarios.php?mensaje=error');
        exit();
    }
} else {
    header('Location: clases-usuarios.php?mensaje=error');
    exit();
}
?>