<?php
session_start();
require 'conexion.php';

// Solo permite acceso a monitores autenticados
if (!isset($_SESSION['Nombre']) || !isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}

$Nombre = $_SESSION['Nombre'];

// Obtener todos los usuarios
$sql = "SELECT Id_usuario, Nombre, Apellido, Contraseña, Correo FROM usuarios";
$result = $mysqli->query($sql);

$usuarios = [];
while ($fila = $result->fetch_assoc()) {
    $usuarios[] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios Existentes</title>
    <link rel="icon" href="peso.png" type="img/x-icon" class="peso">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-purple { background: #7c3aed !important; color: #fff !important; }
        .btn-purple { background: #7c3aed; color: #fff; border: none; }
        .btn-purple:hover { background: #5b21b6; color: #fff; }
        .rounded-4 { border-radius: 1.5rem !important; }
        body.bg-light { background: linear-gradient(135deg, #ede9fe 0%, #c7d2fe 100%); min-height: 100vh; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-purple">
                <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($Nombre); ?></h1>
            </div>
            <div class="card-body">
                <p class="text-center">Estos son los usuarios existentes:</p>
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ID de Usuario</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Contraseña</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['Id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Apellido']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Contraseña']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Correo']); ?></td>
                                <td>
                                    <a href="eliminar-usuario-definitivo.php?Id_usuario=<?php echo $usuario['Id_usuario']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('¿Seguro que quieres eliminar este usuario?');">
                                       Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="crear-usuario-monitor.php" class="btn btn-purple">Crear Usuario</a>
                <a href="menu-monitor.php" class="btn btn-dark mx-2">Volver al Menú</a>
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>