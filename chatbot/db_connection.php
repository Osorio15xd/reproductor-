<?php
// Configuración de la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "bassculture";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer conjunto de caracteres
$conn->set_charset("utf8mb4");
?>
