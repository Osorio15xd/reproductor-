<?php
// Configuración de cabeceras para permitir JSON
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once '../config/db_connect.php';

// Obtener datos de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar si hay datos
if (!$data || !isset($data['message'])) {
    echo json_encode(['success' => false, 'response' => 'Error: No se recibió un mensaje válido']);
    exit;
}

// Obtener mensaje y usuario
$userMessage = $data['message'];
$userId = isset($data['user_id']) ? $data['user_id'] : 21; // Usuario por defecto

try {
    // Obtener respuesta
    $response = getResponse($userMessage, $conn);
    
    // Guardar en historial
    saveChatHistory($userId, $userMessage, $response, $conn);
    
    // Devolver respuesta
    echo json_encode(['success' => true, 'response' => $response]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'response' => 'Error en el servidor: ' . $e->getMessage()]);
}

/**
 * Obtiene una respuesta basada en el mensaje del usuario
 */
function getResponse($userMessage, $conn) {
    // Buscar la respuesta más relevante
    $stmt = $conn->prepare("
        SELECT r.response
        FROM ai_responses r
        JOIN ai_keywords k ON r.id = k.response_id
        WHERE LOWER(:message) LIKE CONCAT('%', LOWER(k.keyword), '%')
        ORDER BY k.weight DESC, r.priority DESC
        LIMIT 1
    ");
    
    $stmt->bindParam(':message', $userMessage, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    if ($result) {
        $response = $result['response'];
        
        // Reemplazar placeholders si existen
        if (strpos($response, '{greeting}') !== false) {
            $greeting = getGreeting();
            $response = str_replace('{greeting}', $greeting, $response);
        }
        
        return $response;
    } else {
        return "Lo siento, no tengo una respuesta para eso. ¿Puedes ser más específico? Puedo ayudarte con información sobre BassCulture, música o artistas.";
    }
}

/**
 * Guarda el historial de chat en la base de datos
 */
function saveChatHistory($userId, $message, $response, $conn) {
    $stmt = $conn->prepare("
        INSERT INTO ai_chat_history (user_id, message, response)
        VALUES (:user_id, :message, :response)
    ");
    
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':response', $response, PDO::PARAM_STR);
    
    return $stmt->execute();
}

/**
 * Obtiene un saludo según la hora del día
 */
function getGreeting() {
    $hour = date('H');
    
    if ($hour >= 5 && $hour < 12) {
        return "Buenos días, ";
    } elseif ($hour >= 12 && $hour < 20) {
        return "Buenas tardes, ";
    } else {
        return "Buenas noches, ";
    }
}
?>
