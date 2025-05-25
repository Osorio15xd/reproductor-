<?php
// Incluir archivo de conexión
require_once 'db_connection.php';

// Verificar si la conexión fue exitosa
if ($conn) {
    echo "<h2 style='color: green;'>Conexión a la base de datos exitosa</h2>";
    
    // Probar consulta a la tabla ai_responses
    $query = "SELECT COUNT(*) as total FROM ai_responses";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total de respuestas en la base de datos: " . $row['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>Error al consultar la tabla ai_responses: " . $conn->error . "</p>";
    }
    
    // Probar consulta a la tabla ai_keywords
    $query = "SELECT COUNT(*) as total FROM ai_keywords";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total de palabras clave en la base de datos: " . $row['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>Error al consultar la tabla ai_keywords: " . $conn->error . "</p>";
    }
    
    // Cerrar conexión
    $conn->close();
} else {
    echo "<h2 style='color: red;'>Error al conectar con la base de datos</h2>";
}
?>
