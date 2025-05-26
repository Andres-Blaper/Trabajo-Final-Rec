<?php
require 'conexion.php';
session_start();

// Consulta para obtener los registros de usuarios
$sql = "SELECT Id_registro, Hora_entrada, Hora_salida, Id_cliente, Id_monitor FROM registros";
$result = $mysqli->query($sql);
$registros = [];
while ($row = $result->fetch_assoc()) {
    $registros[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registros de Usuarios</title>
    <link rel="icon" href="peso.png" type="img/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                <h1 class="text-center">Registros de Usuarios</h1>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="buscadorRegistros" placeholder="Buscar registro...">
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaRegistros">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID de Registro</th>
                                <th scope="col">Hora de Entrada</th>
                                <th scope="col">Hora de Salida</th>
                                <th scope="col">ID Cliente</th>
                                <th scope="col">ID Monitor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registro['Id_registro']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['Hora_entrada']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['Hora_salida']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['Id_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['Id_monitor']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="crear-registro.php" class="btn btn-purple">Crear Registro</a>
                <a href="menu-monitor.php" class="btn btn-dark mx-2">Volver al Menú</a>
                <a href="cerrarsesion.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('buscadorRegistros').addEventListener('input', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('#tablaRegistros tbody tr');
            filas.forEach(function(fila) {
                let texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    </script>
    <script>
    // Ordenar tabla al hacer clic en los th
    document.querySelectorAll('#tablaRegistros th').forEach(function(th, colIndex) {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            let table = th.closest('table');
            let tbody = table.querySelector('tbody');
            let rows = Array.from(tbody.querySelectorAll('tr'));
            let asc = th.dataset.asc === 'true' ? false : true;
            rows.sort(function(a, b) {
                let aText = a.children[colIndex].textContent.trim();
                let bText = b.children[colIndex].textContent.trim();
                // Si es número, compara como número
                if (!isNaN(aText) && !isNaN(bText)) {
                    return asc ? aText - bText : bText - aText;
                }
                // Si es hora, compara como hora
                if (/^\d{2}:\d{2}/.test(aText) && /^\d{2}:\d{2}/.test(bText)) {
                    return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
                // Si es texto, compara como texto
                return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            // Quita el orden de los demás th
            table.querySelectorAll('th').forEach(t => t.removeAttribute('data-asc'));
            th.dataset.asc = asc;
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    </script>
</body>
</html>