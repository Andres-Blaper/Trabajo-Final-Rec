<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['Id_monitor'])) {
    header('Location: formulario.html');
    exit();
}
// Se inicializa una variable llamada $mensaje y se le asigna un string vacío, esto, más adelante se usará para asignarle un valor, y con eso, mostrar un toast de cierta forma u otra.
$mensaje = '';
// // Se inicializa la variable $toast como false. Esta se usará como un interruptor para saber si mostrar un toast (mensaje emergente) al usuario.
$toast = false;
// Se asegura que el formulario se ha enviado por método POST y recoge los datos del formulario con un trim para eliminar espacios al inicio y al final.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    // Comprobar si ya existe un usuario con ese correo
    $sql_check = "SELECT COUNT(*) as total FROM usuarios WHERE Correo = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    // Comprobar si ya existe un usuario con ese nombre y apellido
    $sql_check_nombre = "SELECT COUNT(*) as total FROM usuarios WHERE Nombre = ? AND Apellido = ?";
    $stmt_check_nombre = $mysqli->prepare($sql_check_nombre);
    $stmt_check_nombre->bind_param("ss", $nombre, $apellido);
    $stmt_check_nombre->execute();
    $result_check_nombre = $stmt_check_nombre->get_result();
    $row_check_nombre = $result_check_nombre->fetch_assoc();
// Si el count de la sentencia SQL de "SELECT COUNT(*) as total FROM usuarios WHERE Correo = ?"; toma un valor mayor a 0, significa que ya existe un usuario con ese correo, y cuando esto pasa...
    if ($row_check['total'] > 0) {
        // Muestra un toast con un mensaje de error.
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Ya existe un usuario con ese correo.';
// Por otro lado, si el count de la sentencia SQL de "SELECT COUNT(*) as total FROM usuarios WHERE Nombre = ? AND Apellido = ?"; toma un valor mayor a 0, significa que ya existe un usuario con ese nombre y apellido,
// aquí solo se tiene en cuenta si los dos coinciden, obviamente, puede haber un mismo nombre entre usuarios o mismo apellido, pero no mismo nombre y apellido simultaneamente, y cuando esto pasa...
    } elseif ($row_check_nombre['total'] > 0) {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Ya existe un usuario con ese nombre y apellido.';
        // Se podria perfectamente hacer simplemente con un elseif, sin necesidad de esta comprobación ($nombre && $apellido && $contrasena && $correo), esto evita que esos campos esten vacíos, aunque esto ya lo evita el required, se podria quitar perfectamente.
    } elseif ($nombre && $apellido && $contrasena && $correo) {
         // Cifrar la contraseña antes de guardar
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        // Hacemos una sentencia SQL para insertar valores en la tabla usuarios.
        $sql = "INSERT INTO usuarios (Nombre, Apellido, Contraseña, Correo) VALUES (?, ?, ?, ?)";
        // Preparamos la sentencia SQL.
        $stmt = $mysqli->prepare($sql);
        // Vinculamos los parámetros a la sentencia preparada, ssss son 4 strings.
        $stmt->bind_param("ssss", $nombre, $apellido, $hash, $correo);
        // Ejecutamos la sentencia, y dependiendo del resultado, mostramos un toast de éxito o de error.
        if ($stmt->execute()) {
            $toast = true;
            $toastClass = 'bg-success text-white';
            $toastMsg = 'Usuario creado correctamente.';
        } else {
            $toast = true;
            $toastClass = 'bg-danger text-white';
            $toastMsg = 'Error al crear el usuario.';
        }
    } else {
        $toast = true;
        $toastClass = 'bg-danger text-white';
        $toastMsg = 'Rellena todos los campos correctamente.';
    }
}

// Obtener el próximo ID autoincremental, se podría eliminar esta consulta y dejar que la base de datos lo haga automáticamente, pero se ha decidido hacerlo así para mostrar el ID al usuario mientras el monitor está en el formulario de creacion de usuarios.
// Con esta consulta, se obtiene el próximo ID autoincremental de la tabla usuarios, information_schema.TABLES es como una base de datos que describe otras bases de datos, no contiene los datos, si no la estructura de la bd.
$sql_next_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'gimnasio' AND TABLE_NAME = 'usuarios'";
// Se ejecuta la consulta y se guarda el resultado en una variable.
$result_next_id = $mysqli->query($sql_next_id);
// Extrae la primera (y única) fila del resultado como un array asociativo
$row_next_id = $result_next_id->fetch_assoc();
// Si se ejecuta correctamente, se guarda la variable $next_id con el valor del ID autoincremental, si no, se guarda como un string vacío.
$next_id = $row_next_id ? $row_next_id['AUTO_INCREMENT'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="icon" type="image/png" href="peso.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ede9fe; }
        .form-container { max-width: 400px; margin: 60px auto; }
        .btn-purple {
            background: #7c3aed;
            color: #fff;
            border: none;
        }
        .btn-purple:hover {
            background: #5b21b6;
            color: #fff;
        }
    </style>
</head>
<body>
    <?php if ($toast): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
            <div id="mainToast" class="toast align-items-center <?php echo $toastClass; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $toastMsg; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('mainToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });
        </script>
    <?php endif; ?>
    <div class="form-container card shadow p-4 rounded-4">
        <h2 class="mb-4 text-center">Crear Nuevo Usuario</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="id_usuario" class="form-label">ID de Usuario</label>
                <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo $next_id; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" name="correo" readonly placeholder="nombre.apellido@email.com">
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" minlength="5" required>
            </div>  
            <button type="submit" class="btn btn-purple w-100">Crear Usuario</button>
            <a href="usuarios-monitor.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
    <script>
    // Declara una función llamada quitarTildes que recibe un parámetro texto.
function quitarTildes(texto) {
    // texto.normalize("NFD"): descompone caracteres acentuados (por ejemplo, "é" → "e" + "́").
    // .replace(/[\u0300-\u036f]/g, ""): elimina los caracteres "extra" que resultan de esa descomposición (como la tilde), usando una expresión regular que borra los caracteres Unicode de acentos.
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}
// Declara otra función llamada generarCorreo. Esta se usará para construir automáticamente el correo.
function generarCorreo() {
    // Toma el valor del campo nombre del formulario.
    // .trim(): elimina espacios al inicio y final.
    // .toLowerCase(): convierte a minúsculas.
    // .replace(/\s+/g, ''): elimina todos los espacios internos (si alguien escribió "Juan Carlos").
    // quitarTildes(...): quita acentos.
    // Guarda el resultado limpio en la variable nombre/apellido.
    let nombre = quitarTildes(document.getElementById('nombre').value.trim().toLowerCase().replace(/\s+/g, ''));
    let apellido = quitarTildes(document.getElementById('apellido').value.trim().toLowerCase().replace(/\s+/g, ''));
    // Si hay nombre y apellido, genera un correo del tipo: nombre.apellido@email.com y lo asigna automáticamente al campo correo.
    if(nombre && apellido) {
        document.getElementById('correo').value = nombre + '.' + apellido + '@email.com';
    } else {
        // Si no hay nombre o apellido, limpia el campo correo.
        document.getElementById('correo').value = '';
    }
}
// Cada vez que el usuario escriba algo en el campo nombre o apellido, se ejecuta la función generarCorreo.
document.getElementById('nombre').addEventListener('input', generarCorreo);
document.getElementById('apellido').addEventListener('input', generarCorreo);
</script>
</body>
</html>