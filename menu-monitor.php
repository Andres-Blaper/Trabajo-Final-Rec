<?php
session_start();
if (!isset($_SESSION['Nombre'])) {
    header('Location: formulario.html');
    exit();
}
$nombre_monitor = $_SESSION['Nombre'];

// Conexión a la base de datos (nombre correcto: gimnasio)
$mysqli = new mysqli("localhost", "root", "", "gimnasio");

// Obtener número de clases
$res_clases = $mysqli->query("SELECT COUNT(*) AS total FROM clases");
$num_clases = $res_clases ? $res_clases->fetch_assoc()['total'] : 0;

// Obtener número de usuarios
$res_usuarios = $mysqli->query("SELECT COUNT(*) AS total FROM usuarios");
$num_usuarios = $res_usuarios ? $res_usuarios->fetch_assoc()['total'] : 0;

// Obtener número de registros (inscripciones)
$res_registros = $mysqli->query("SELECT COUNT(*) AS total FROM inscripciones");
$num_registros = $res_registros ? $res_registros->fetch_assoc()['total'] : 0;

// Obtener número de monitores
$res_monitores = $mysqli->query("SELECT COUNT(*) AS total FROM monitores");
$num_monitores = $res_monitores ? $res_monitores->fetch_assoc()['total'] : 0;

// Obtener número de inscripciones activas (puedes ajustar la condición si tienes un campo de estado)
$res_inscripciones = $mysqli->query("SELECT COUNT(*) AS total FROM inscripciones");
$num_inscripciones = $res_inscripciones ? $res_inscripciones->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Monitor</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        body {
            background: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #7c3aed;
            color: #fff;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 270px; /* Aumenta el ancho fijo de la columna */
            max-width: 100%;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100% !important;
                min-width: unset;
            }
        }
        .sidebar h2 {
            font-size: 2.8rem;
            margin-bottom: 2.2rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .sidebar .bienvenida {
            font-size: 2rem;
            margin-bottom: 2.5rem;
            font-weight: bold;
        }
        .sidebar .nav-link {
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            padding-left: 0;
            transition: transform 0.18s cubic-bezier(.4,0,.2,1), color 0.18s;
            display: inline-block;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link:focus {
            color: #f5f6fa;
            transform: scale(1.10) translateX(10px);
            text-decoration: none;
        }
        .sidebar .logout-btn {
            margin-top: auto;
            width: 100%;
        }
        .dashboard-cards {
            gap: 0rem !important;
        }
        .dashboard-card {
            width: 100%; /* Ocupa todo el ancho de la columna */
            border: none;
            border-radius: 1rem;
            margin-bottom: 0.8rem;
        }
        .dashboard-card .card-body {
            padding: 2.7rem 0.5rem; /* Antes era 1.2rem, ahora menos */
        }
        @media (max-width: 1600px) {
            .dashboard-card {
                width: 16rem;
            }
        }
        @media (max-width: 1400px) {
            .dashboard-card {
                width: 15rem;
            }
        }
        @media (max-width: 1200px) {
            .dashboard-card {
                width: 14rem;
            }
        }
        @media (max-width: 1100px) {
            .dashboard-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar izquierda -->
        <div class="col-12 col-md-4 col-lg-3 sidebar">
            <h2>Panel Monitor</h2>
            <div class="bienvenida">
                Bienvenido, <?php echo htmlspecialchars($nombre_monitor); ?>
            </div>
            <nav class="nav flex-column w-100 mb-4">
                <a class="nav-link" href="monitor-clases.php">Clases</a>
                <a class="nav-link" href="monitor-usuarios.php">Usuarios</a>
                <a class="nav-link" href="monitor-registros.php">Registros</a>
            </nav>
            <a href="cerrarsesion.php" class="btn btn-light text-dark logout-btn mt-auto">Cerrar sesión</a>
        </div>
        <!-- Contenido principal con tarjetas resumen -->
        <div class="col-12 col-md-8 col-lg-9 p-4 d-flex align-items-center justify-content-center">
            <div class="row w-100 justify-content-center dashboard-cards gx-3 gy-3">
                <!-- Tarjeta Clases -->
                <div class="col-12 col-lg-6 col-xl-4 col-xxl-2 d-flex justify-content-center">
                    <div class="card shadow-sm text-center dashboard-card">
                        <div class="card-body">
                            <!-- Calendario -->
                            <svg width="48" height="48" fill="#ef4444" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zm0-13H5V6h14v1z"/></svg>
                            <h4 class="mt-3 mb-1">Clases</h4>
                            <div class="display-5 fw-bold"><?php echo $num_clases; ?></div>
                        </div>
                    </div>
                </div>
                <!-- Tarjeta Usuarios -->
                <div class="col-12 col-lg-6 col-xl-4 col-xxl-2 d-flex justify-content-center">
                    <div class="card shadow-sm text-center dashboard-card">
                        <div class="card-body">
                            <!-- Usuario -->
                            <svg width="48" height="48" fill="#22c55e" viewBox="0 0 24 24"><path d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                            <h4 class="mt-3 mb-1">Usuarios</h4>
                            <div class="display-5 fw-bold"><?php echo $num_usuarios; ?></div>
                        </div>
                    </div>
                </div>
                <!-- Tarjeta Registros -->
                <div class="col-12 col-lg-6 col-xl-4 col-xxl-2 d-flex justify-content-center">
                    <div class="card shadow-sm text-center dashboard-card">
                        <div class="card-body">
                            <!-- Clipboard check -->
                            <svg width="48" height="48" fill="#3b82f6" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-7-3l-5-5 1.41-1.41L12 13.17l4.59-4.58L18 10l-6 6z"/></svg>
                            <h4 class="mt-3 mb-1">Registros</h4>
                            <div class="display-5 fw-bold"><?php echo $num_registros; ?></div>
                        </div>
                    </div>
                </div>
                <!-- Tarjeta Monitores -->
                <div class="col-12 col-lg-6 col-xl-4 col-xxl-2 d-flex justify-content-center">
                    <div class="card shadow-sm text-center dashboard-card">
                        <div class="card-body">
                            <!-- Icono monitor (persona con silbato) -->
                            <svg width="48" height="48" fill="#f59e42" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M12 14c-4 0-8 2-8 4v2h16v-2c0-2-4-4-8-4z"/></svg>
                            <h4 class="mt-3 mb-1">Monitores</h4>
                            <div class="display-5 fw-bold"><?php echo $num_monitores; ?></div>
                        </div>
                    </div>
                </div>
                <!-- Tarjeta Inscripciones -->
                <div class="col-12 col-lg-6 col-xl-4 col-xxl-2 d-flex justify-content-center">
                    <div class="card shadow-sm text-center dashboard-card">
                        <div class="card-body">
                            <!-- Icono inscripciones (documento) -->
                            <svg width="48" height="48" fill="#8b5cf6" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6H6zm7 1.5V9h5.5L13 3.5zM6 4h6v5a1 1 0 0 0 1 1h5v10a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/></svg>
                            <h4 class="mt-3 mb-1">Inscripciones</h4>
                            <div class="display-5 fw-bold"><?php echo $num_inscripciones; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>