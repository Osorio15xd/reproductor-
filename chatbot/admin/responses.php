<?php
// Incluir archivo de conexión
require_once '../db_connection.php';

// Procesar formulario de agregar respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_response'])) {
    $pattern = $_POST['pattern'];
    $response = $_POST['response'];
    $priority = $_POST['priority'];
    $category = $_POST['category'];
    
    $query = "INSERT INTO ai_responses (pattern, response, priority, category, active) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssis", $pattern, $response, $priority, $category);
    
    if ($stmt->execute()) {
        $success_message = "Respuesta agregada correctamente";
    } else {
        $error_message = "Error al agregar respuesta: " . $conn->error;
    }
    
    $stmt->close();
}

// Procesar eliminación de respuesta
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Primero eliminar las palabras clave asociadas
    $query = "DELETE FROM ai_keywords WHERE response_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Luego eliminar la respuesta
    $query = "DELETE FROM ai_responses WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Respuesta eliminada correctamente";
    } else {
        $error_message = "Error al eliminar respuesta: " . $conn->error;
    }
    
    $stmt->close();
}

// Obtener todas las respuestas
$query = "SELECT id, pattern, response, priority, category, active FROM ai_responses ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas - BassCulture Chatbot</title>
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
                <li><a href="responses.php" class="active">Respuestas</a></li>
                <li><a href="keywords.php">Palabras Clave</a></li>
                <li><a href="history.php">Historial</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Gestión de Respuestas</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-grid">
                <div class="content-main">
                    <div class="card">
                        <h3>Respuestas Existentes</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patrón</th>
                                        <th>Respuesta</th>
                                        <th>Categoría</th>
                                        <th>Prioridad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td class="truncate"><?php echo htmlspecialchars($row['pattern']); ?></td>
                                                <td class="truncate"><?php echo htmlspecialchars($row['response']); ?></td>
                                                <td><?php echo $row['category']; ?></td>
                                                <td><?php echo $row['priority']; ?></td>
                                                <td>
                                                    <span class="status <?php echo $row['active'] ? 'active' : 'inactive'; ?>">
                                                        <?php echo $row['active'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edit_response.php?id=<?php echo $row['id']; ?>" class="btn-small">Editar</a>
                                                    <a href="responses.php?delete=<?php echo $row['id']; ?>" class="btn-small danger" onclick="return confirm('¿Estás seguro de eliminar esta respuesta?')">Eliminar</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay respuestas disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="content-sidebar">
                    <div class="card">
                        <h3>Agregar Nueva Respuesta</h3>
                        <form method="post" action="responses.php">
                            <div class="form-group">
                                <label for="pattern">Patrón</label>
                                <input type="text" id="pattern" name="pattern" required placeholder="Ej: hola, ayuda, música">
                            </div>
                            
                            <div class="form-group">
                                <label for="response">Respuesta</label>
                                <textarea id="response" name="response" rows="4" required placeholder="Escribe la respuesta del chatbot..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Categoría</label>
                                <select id="category" name="category" required>
                                    <option value="greeting">Saludo</option>
                                    <option value="info" selected>Información</option>
                                    <option value="account">Cuenta</option>
                                    <option value="features">Funciones</option>
                                    <option value="support">Soporte</option>
                                    <option value="farewell">Despedida</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="priority">Prioridad</label>
                                <select id="priority" name="priority" required>
                                    <option value="1">1 (Baja)</option>
                                    <option value="3">3</option>
                                    <option value="5" selected>5 (Media)</option>
                                    <option value="8">8</option>
                                    <option value="10">10 (Alta)</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="add_response" class="btn">Agregar Respuesta</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> BassCulture - Panel de Administración</p>
        </footer>
    </div>
    
    <style>
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .card h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f9f9f9;
        }
        
        .truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .status.active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-small {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: #1db954;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
        
        .btn-small.danger {
            background-color: #dc3545;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
