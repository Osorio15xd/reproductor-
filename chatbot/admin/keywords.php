<?php
// Incluir archivo de conexión
require_once '../db_connection.php';

// Procesar formulario de agregar palabra clave
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_keyword'])) {
    $keyword = $_POST['keyword'];
    $response_id = $_POST['response_id'];
    $weight = $_POST['weight'];
    
    $query = "INSERT INTO ai_keywords (response_id, keyword, weight) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isd", $response_id, $keyword, $weight);
    
    if ($stmt->execute()) {
        $success_message = "Palabra clave agregada correctamente";
    } else {
        $error_message = "Error al agregar palabra clave: " . $conn->error;
    }
    
    $stmt->close();
}

// Procesar eliminación de palabra clave
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $query = "DELETE FROM ai_keywords WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Palabra clave eliminada correctamente";
    } else {
        $error_message = "Error al eliminar palabra clave: " . $conn->error;
    }
    
    $stmt->close();
}

// Obtener todas las palabras clave
$query = "SELECT k.id, k.response_id, k.keyword, k.weight, r.pattern 
          FROM ai_keywords k
          JOIN ai_responses r ON k.response_id = r.id
          ORDER BY k.id DESC";
$result = $conn->query($query);

// Obtener todas las respuestas para el formulario
$responses_query = "SELECT id, pattern FROM ai_responses ORDER BY id";
$responses_result = $conn->query($responses_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palabras Clave - BassCulture Chatbot</title>
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
                <li><a href="keywords.php" class="active">Palabras Clave</a></li>
                <li><a href="history.php">Historial</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Gestión de Palabras Clave</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-grid">
                <div class="content-main">
                    <div class="card">
                        <h3>Palabras Clave Existentes</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Palabra Clave</th>
                                        <th>ID Respuesta</th>
                                        <th>Patrón de Respuesta</th>
                                        <th>Peso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['keyword']); ?></td>
                                                <td><?php echo $row['response_id']; ?></td>
                                                <td class="truncate"><?php echo htmlspecialchars($row['pattern']); ?></td>
                                                <td><?php echo $row['weight']; ?></td>
                                                <td>
                                                    <a href="edit_keyword.php?id=<?php echo $row['id']; ?>" class="btn-small">Editar</a>
                                                    <a href="keywords.php?delete=<?php echo $row['id']; ?>" class="btn-small danger" onclick="return confirm('¿Estás seguro de eliminar esta palabra clave?')">Eliminar</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No hay palabras clave disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="content-sidebar">
                    <div class="card">
                        <h3>Agregar Nueva Palabra Clave</h3>
                        <form method="post" action="keywords.php">
                            <div class="form-group">
                                <label for="keyword">Palabra Clave</label>
                                <input type="text" id="keyword" name="keyword" required placeholder="Ej: saludar, ayuda, música">
                            </div>
                            
                            <div class="form-group">
                                <label for="response_id">Respuesta</label>
                                <select id="response_id" name="response_id" required>
                                    <?php if ($responses_result->num_rows > 0): ?>
                                        <?php while ($response = $responses_result->fetch_assoc()): ?>
                                            <option value="<?php echo $response['id']; ?>">
                                                <?php echo $response['id'] . ': ' . htmlspecialchars(substr($response['pattern'], 0, 50)); ?>
                                                <?php if (strlen($response['pattern']) > 50) echo '...'; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay respuestas disponibles</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="weight">Peso (0.1 - 1.0)</label>
                                <input type="number" id="weight" name="weight" step="0.1" min="0.1" max="1" value="0.8" required>
                                <small>Mayor peso = mayor prioridad</small>
                            </div>
                            
                            <button type="submit" name="add_keyword" class="btn">Agregar Palabra Clave</button>
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
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
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
