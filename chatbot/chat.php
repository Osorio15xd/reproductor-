<?php
// Configuración de cabeceras para permitir JSON
header('Content-Type: application/json');

// Obtener datos de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar si hay datos
if (!$data || !isset($data['message'])) {
    echo json_encode(['response' => 'Error: No se recibió un mensaje válido']);
    exit;
}

// Obtener mensaje y usuario
$userMessage = $data['message'];
$userId = isset($data['user_id']) ? $data['user_id'] : 21; // Usuario por defecto

// Incluir archivo de conexión a la base de datos
require_once 'db_connection.php';

// Obtener respuesta
$response = getResponse($userMessage, $conn);

// Guardar en historial
saveChatHistory($userId, $userMessage, $response, $conn);

// Cerrar conexión
$conn->close();

// Devolver respuesta
echo json_encode(['response' => $response]);

/**
 * Obtiene una respuesta basada en el mensaje del usuario
 */
function getResponse($userMessage, $conn) {
    // Buscar la respuesta más relevante
    $query = "
        SELECT r.response
        FROM ai_responses r
        JOIN ai_keywords k ON r.id = k.response_id
        WHERE ? LIKE CONCAT('%', k.keyword, '%')
        ORDER BY k.weight DESC
        LIMIT 1
    ";
    
    // Preparar la consulta
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        return "Error en la consulta: " . $conn->error;
    }
    
    // Vincular parámetros
    $stmt->bind_param("s", $userMessage);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener resultado
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = $row['response'];
    } else {
        $response = "Lo siento, no tengo una respuesta para eso. ¿Puedes ser más específico? Puedo ayudarte con información sobre BassCulture, música o artistas.";
    }
    
    $stmt->close();
    return $response;
}

/**
 * Guarda el historial de chat en la base de datos
 */
function saveChatHistory($userId, $message, $response, $conn) {
    $query = "
        INSERT INTO ai_chat_history (user_id, message, response)
        VALUES (?, ?, ?)
    ";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("iss", $userId, $message, $response);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}
?>
