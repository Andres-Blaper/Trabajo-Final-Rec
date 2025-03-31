<?php
    $mysqli = new mysqli("localhost", "root", "", "gimnasio");
    if($mysqli->connect_errno){
        echo "<p>Fallo al conectar a MySQL: (", $mysqli->connect_errno, ") ", $mysqli->connect_error, "</p>";
    } 
    // else{
    //     echo "<p>Conexión realizada con éxito</p>";
    // }
?>