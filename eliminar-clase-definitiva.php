<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}

if (isset($_GET['Id_clase'])) {
    $Id_clase = intval($_GET['Id_clase']);

    // Eliminar la clase solo si pertenece al monitor logueado
    $sql = "DELETE FROM clases WHERE Id_clase = ? AND Id_monitor = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $Id_clase, $_SESSION['Id_monitor']);

    if ($stmt->execute()) {
        header('Location: clases-monitor.php?mensaje=clase_eliminada');
        exit();
    } else {
        header('Location: clases-monitor.php?mensaje=error');
        exit();
    }
} else {
    header('Location: clases-monitor.php?mensaje=error');
    exit();
}
?>