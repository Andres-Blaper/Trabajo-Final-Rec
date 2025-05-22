<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['Id_usuario'])) {
    header('Location: formulario.html');
    exit();
}

$Id_usuario = $_SESSION['Id_usuario'];

if (isset($_GET['Id_clase'])) {
    $Id_clase = intval($_GET['Id_clase']);

    // Elimina la inscripción del usuario a la clase
    $sql = "DELETE FROM inscripciones WHERE Id_usuario = ? AND Id_clase = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $Id_usuario, $Id_clase);

    if ($stmt->execute()) {
        header('Location: clases-usuarios.php?mensaje=clase_eliminada');
        exit();
    } else {
        header('Location: clases-usuarios.php?mensaje=error');
        exit();
    }
} else {
    header('Location: clases-usuarios.php?mensaje=error');
    exit();
}
?>