<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BassCulture Chatbot</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>BassCulture Chatbot</h1>
        </div>
        <div id="chat" class="chat-messages">
            <!-- Los mensajes se mostrarán aquí -->
        </div>
        <div class="chat-input">
            <input type="text" id="userMessage" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button onclick="sendMessage()">Enviar</button>
        </div>
    </div>

    <script>
        // Función para enviar mensajes al servidor
        async function sendMessage() {
            const userMessage = document.getElementById('userMessage').value.trim();
            if (!userMessage) return;
            
            // Mostrar mensaje del usuario
            addMessage('user', userMessage);
            
            // Limpiar campo de entrada
            document.getElementById('userMessage').value = '';
            
            try {
                // Enviar mensaje al servidor
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        message: userMessage,
                        user_id: 21  // ID de usuario por defecto, puedes cambiarlo según necesites
                    })
                });
                
                const data = await response.json();
                
                // Mostrar respuesta del bot
                addMessage('bot', data.response);
            } catch (error) {
                console.error('Error:', error);
                addMessage('bot', 'Lo siento, ha ocurrido un error al procesar tu mensaje.');
            }
        }
        
        // Función para añadir mensajes al chat
        function addMessage(sender, message) {
            const chat = document.getElementById('chat');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            messageDiv.innerHTML = `<span class="message-text">${message}</span>`;
            chat.appendChild(messageDiv);
            
            // Scroll al final del chat
            chat.scrollTop = chat.scrollHeight;
        }
        
        // Evento para enviar mensaje al presionar Enter
        document.getElementById('userMessage').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Mensaje de bienvenida
        window.onload = function() {
            addMessage('bot', '¡Hola! Soy el asistente de BassCulture. ¿En qué puedo ayudarte hoy?');
        };
    </script>
</body>
</html>
