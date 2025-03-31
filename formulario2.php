<?php
// Establezco conexión
require 'conexion.php';

$Nombre = $_POST['nombre'];
$Contraseña = $_POST['contraseña'];
$sql = "SELECT * FROM usuarios WHERE Nombre='$Nombre' AND Contraseña='$Contraseña'";

        // Ejecuto la sentencia y guardo su resultado en una variable
        $resultado = $mysqli->query($sql);
       
            $fila = $resultado->fetch_assoc();
            if ($fila){
                $_SESSION['Nombre'] = $Nombre;
                echo "<p>Hola, $Nombre. Eres un usuario.</p>";
            }else{
                $sql = "SELECT * FROM monitores WHERE Nombre='$Nombre' AND Contraseña='$Contraseña'";
                $resultado = $mysqli->query($sql);
                $fila_monitor = $resultado->fetch_assoc();

            if ($fila_monitor) {
            $_SESSION['Nombre'] = $Nombre;
            echo "<p>Hola, $Nombre. Eres un monitor.</p>";
            } else {
            echo "<p>No se encuentra el usuario '$Nombre' con la contraseña proporcionada.</p>";
            echo "<p><a href='formulario.html'>Volver a formulario</a></p>";
            }
            }
?>