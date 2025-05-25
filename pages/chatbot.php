<?php
// Verificar si el usuario está logueado
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 21; // Usuario por defecto si no hay sesión

// Obtener historial de chat del usuario
$chat_history = [];
try {
    $stmt = $conn->prepare("
        SELECT message, response, DATE_FORMAT(timestamp, '%d/%m/%Y %H:%i') as formatted_time
        FROM ai_chat_history
        WHERE user_id = :user_id
        ORDER BY timestamp DESC
        LIMIT 20
    ");
    
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $chat_history = $stmt->fetchAll();
    // Invertir el array para mostrar los mensajes más antiguos primero
    $chat_history = array_reverse($chat_history);
    
} catch (PDOException $e) {
    $error_message = "Error al cargar el historial de chat: " . $e->getMessage();
}

// Obtener sugerencias de preguntas frecuentes desde la base de datos
$suggestions = [];
try {
    $stmt = $conn->prepare("
        SELECT pattern 
        FROM ai_responses 
        WHERE category IN ('info', 'features', 'account') 
        AND active = 1 
        ORDER BY priority DESC 
        LIMIT 6
    ");
    
    $stmt->execute();
    $suggestions_data = $stmt->fetchAll();
    
    foreach ($suggestions_data as $suggestion) {
        $suggestions[] = ucfirst($suggestion['pattern']);
    }
    
} catch (PDOException $e) {
    // Si hay error, usar sugerencias predeterminadas
    $suggestions = [
        "¿Cómo funciona BassCulture?",
        "¿Cómo puedo crear una playlist?",
        "¿Cómo puedo subir música?",
        "¿Cuáles son los planes de suscripción?",
        "¿Cómo puedo descargar música?",
        "Contactar con soporte técnico"
    ];
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="fas fa-robot me-2"></i>Asistente Virtual</h1>
            <p class="text-muted mb-4">Pregúntame cualquier cosa sobre BassCulture y te ayudaré a encontrar lo que necesitas.</p>
        </div>
    </div>
    
    <div class="chatbot-container">
        <div class="chatbot-header">
            <i class="fas fa-robot"></i>
            <h2>BassCulture Assistant</h2>
        </div>
        
        <div class="chatbot-messages" id="chat-messages">
            <?php if (empty($chat_history)): ?>
                <div class="message bot">
                    <div class="message-content">¡Hola! Soy el asistente virtual de BassCulture. ¿En qué puedo ayudarte hoy?</div>
                    <span class="message-time"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
            <?php else: ?>
                <?php foreach ($chat_history as $chat): ?>
                    <div class="message user">
                        <div class="message-content"><?php echo htmlspecialchars($chat['message']); ?></div>
                        <span class="message-time"><?php echo $chat['formatted_time']; ?></span>
                    </div>
                    <div class="message bot">
                        <div class="message-content"><?php echo htmlspecialchars($chat['response']); ?></div>
                        <span class="message-time"><?php echo $chat['formatted_time']; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="chatbot-input">
            <input type="text" id="user-message" placeholder="Escribe tu pregunta aquí..." autocomplete="off">
            <button id="send-message"><i class="fas fa-paper-plane"></i></button>
        </div>
        
        <div class="chatbot-suggestions p-3">
            <?php foreach ($suggestions as $suggestion): ?>
                <div class="suggestion-chip"><?php echo htmlspecialchars($suggestion); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const userMessageInput = document.getElementById('user-message');
    const sendMessageBtn = document.getElementById('send-message');
    const suggestionChips = document.querySelectorAll('.suggestion-chip');
    
    // Scroll al final del chat
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Función para enviar mensaje
    function sendMessage() {
        const message = userMessageInput.value.trim();
        if (message === '') return;
        
        // Añadir mensaje del usuario al chat
        addMessageToChat(message, 'user');
        
        // Limpiar input
        userMessageInput.value = '';
        
        // Mostrar indicador de carga
        const loadingMessage = document.createElement('div');
        loadingMessage.className = 'message bot';
        loadingMessage.innerHTML = '<div class="message-content"><i class="fas fa-spinner fa-spin"></i> Escribiendo...</div>';
        chatMessages.appendChild(loadingMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Enviar mensaje al servidor
        fetch('api/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                user_id: <?php echo $user_id; ?>
            }),
        })
        .then(response => response.json())
        .then(data => {
            // Eliminar indicador de carga
            chatMessages.removeChild(loadingMessage);
            
            // Añadir respuesta del bot
            addMessageToChat(data.response, 'bot');
        })
        .catch(error => {
            // Eliminar indicador de carga
            chatMessages.removeChild(loadingMessage);
            
            // Mostrar error
            addMessageToChat('Lo siento, ha ocurrido un error al procesar tu mensaje. Por favor, inténtalo de nuevo.', 'bot');
            console.error('Error:', error);
        });
    }
    
    // Función para añadir mensaje al chat
    function addMessageToChat(message, type) {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}`;
        
        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        messageContent.textContent = message;
        
        const messageTime = document.createElement('span');
        messageTime.className = 'message-time';
        
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const year = now.getFullYear();
        
        messageTime.textContent = `${day}/${month}/${year} ${hours}:${minutes}`;
        
        messageElement.appendChild(messageContent);
        messageElement.appendChild(messageTime);
        
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Event listeners
    sendMessageBtn.addEventListener('click', sendMessage);
    
    userMessageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Event listener para sugerencias
    suggestionChips.forEach(chip => {
        chip.addEventListener('click', function() {
            userMessageInput.value = this.textContent;
            sendMessage();
        });
    });
});
</script>
