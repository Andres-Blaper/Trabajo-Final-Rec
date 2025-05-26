<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}

if (isset($_GET['Id_usuario'])) {
    $Id_usuario = intval($_GET['Id_usuario']);

    $sql = "DELETE FROM usuarios WHERE Id_usuario = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $Id_usuario);

    if ($stmt->execute()) {
        header('Location: usuarios-monitor.php?mensaje=usuario_eliminado');
        exit();
    } else {
        header('Location: usuarios-monitor.php?mensaje=error');
        exit();
    }
} else {
    header('Location: usuarios-monitor.php?mensaje=error');
    exit();
}
?>