<?php
// Incluir archivo de conexión
require_once '../db_connection.php';

// Configuración de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Filtro de usuario
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Construir consulta
$query = "SELECT h.id, h.user_id, h.message, h.response, h.timestamp, u.nombre_usuario 
          FROM ai_chat_history h
          LEFT JOIN usuario u ON h.user_id = u.id_usuario";

$count_query = "SELECT COUNT(*) as total FROM ai_chat_history";

if ($user_filter) {
    $query .= " WHERE h.user_id = $user_filter";
    $count_query .= " WHERE user_id = $user_filter";
}

$query .= " ORDER BY h.timestamp DESC LIMIT $offset, $records_per_page";

// Ejecutar consultas
$result = $conn->query($query);
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Obtener lista de usuarios para el filtro
$users_query = "SELECT DISTINCT u.id_usuario, u.nombre_usuario 
                FROM usuario u
                JOIN ai_chat_history h ON u.id_usuario = h.user_id
                ORDER BY u.nombre_usuario";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Chat - BassCulture Chatbot</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Panel de Administración - BassCulture Chatbot</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="responses.php">Respuestas</a></li>
                <li><a href="keywords.php">Palabras Clave</a></li>
                <li><a href="history.php" class="active">Historial</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Historial de Conversaciones</h2>
            
            <div class="filters">
                <form method="get" action="history.php">
                    <div class="filter-group">
                        <label for="user_id">Filtrar por Usuario:</label>
                        <select id="user_id" name="user_id" onchange="this.form.submit()">
                            <option value="">Todos los usuarios</option>
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <option value="<?php echo $user['id_usuario']; ?>" <?php if ($user_filter == $user['id_usuario']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($user['nombre_usuario']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Mensaje</th>
                                <th>Respuesta</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_usuario'] ?? 'Usuario #' . $row['user_id']); ?></td>
                                        <td class="truncate"><?php echo htmlspecialchars($row['message']); ?></td>
                                        <td class="truncate"><?php echo htmlspecialchars($row['response']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay registros de historial disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?><?php if ($user_filter) echo '&user_id=' . $user_filter; ?>" class="page-link">&laquo; Anterior</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php if ($user_filter) echo '&user_id=' . $user_filter; ?>" class="page-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?><?php if ($user_filter) echo '&user_id=' . $user_filter; ?>" class="page-link">Siguiente &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> BassCulture - Panel de Administración</p>
        </footer>
    </div>
    
    <style>
        .filters {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .filter-group label {
            font-weight: 500;
            min-width: 150px;
        }
        
        .filter-group select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 0.5rem;
        }
        
        .page-link {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
        }
        
        .page-link.active {
            background-color: #1db954;
            color: white;
            border-color: #1db954;
        }
        
        .page-link:hover:not(.active) {
            background-color: #e9e9e9;
        }
    </style>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
