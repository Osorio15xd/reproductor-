<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - BassCulture Chatbot</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Panel de Administración - BassCulture Chatbot</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="responses.php">Respuestas</a></li>
                <li><a href="keywords.php">Palabras Clave</a></li>
                <li><a href="history.php">Historial</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="dashboard">
                <h2>Dashboard</h2>
                
                <div class="stats-container">
                    <?php
                    // Incluir archivo de conexión
                    require_once '../db_connection.php';
                    
                    // Obtener estadísticas
                    $stats = [
                        'Respuestas' => "SELECT COUNT(*) FROM ai_responses",
                        'Palabras Clave' => "SELECT COUNT(*) FROM ai_keywords",
                        'Mensajes' => "SELECT COUNT(*) FROM ai_chat_history",
                        'Usuarios Activos' => "SELECT COUNT(DISTINCT user_id) FROM ai_chat_history WHERE timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)"
                    ];
                    
                    foreach ($stats as $title => $query) {
                        $result = $conn->query($query);
                        $count = $result->fetch_row()[0];
                        
                        echo "<div class='stat-card'>";
                        echo "<h3>$title</h3>";
                        echo "<p class='stat-number'>$count</p>";
                        echo "</div>";
                    }
                    
                    $conn->close();
                    ?>
                </div>
                
                <div class="quick-actions">
                    <h3>Acciones Rápidas</h3>
                    <div class="action-buttons">
                        <a href="responses.php?action=add" class="btn">Añadir Respuesta</a>
                        <a href="keywords.php?action=add" class="btn">Añadir Palabra Clave</a>
                        <a href="../test_connection.php" class="btn">Probar Conexión</a>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> BassCulture - Panel de Administración</p>
        </footer>
    </div>
</body>
</html>
