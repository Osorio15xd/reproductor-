<section id="page-configuraciones" class="page active" aria-label="Configuraciones">
  <h1>Configuraciones</h1>
  <p>Personaliza tu experiencia y gestiona tu cuenta</p>
  
  <div id="theme-customizer" class="settings-card">
    <h3><i class="fas fa-palette"></i> Personaliza tu experiencia</h3>
    <div class="mb-3">
      <h4>Color principal</h4>
      <div id="color-options">
        <div class="color-option" style="background-color: #1db954;" data-color="#1db954"></div>
        <div class="color-option" style="background-color: #e91e63;" data-color="#e91e63"></div>
        <div class="color-option" style="background-color: #3f51b5;" data-color="#3f51b5"></div>
        <div class="color-option" style="background-color: #ff9800;" data-color="#ff9800"></div>
        <div class="color-option" style="background-color: #9c27b0;" data-color="#9c27b0"></div>
      </div>
    </div>
    <div>
      <h4>Tema</h4>
      <div id="theme-options">
        <div class="theme-option" data-theme="dark">Oscuro</div>
        <div class="theme-option" data-theme="light">Claro</div>
        <div class="theme-option" data-theme="contrast">Alto contraste</div>
      </div>
    </div>
    
    <button id="save-settings-btn" class="btn-primary mt-4">
      <i class="fas fa-save"></i> Guardar Configuración
    </button>
  </div>
  
  <div id="settings-chat" class="settings-card" aria-live="polite">
    <h3><i class="fas fa-headset"></i> Atención al cliente</h3>
    <div id="chat-messages" role="log" aria-relevant="additions" aria-atomic="true">
      <!-- Los mensajes se cargarán dinámicamente -->
    </div>
    <div id="chat-input">
      <input type="text" id="chat-text" placeholder="Escribe tu pregunta o consulta..." aria-label="Escribe tu pregunta o consulta" />
      <button id="chat-send-btn"><i class="fas fa-paper-plane"></i></button>
    </div>
  </div>
  
  <?php if (isset($_SESSION['user_id'])): ?>
    <div id="account-settings" class="settings-card">
      <h3><i class="fas fa-user-cog"></i> Configuración de la cuenta</h3>
      
      <div class="card settings-inner-card">
        <h4>Cambiar contraseña</h4>
        <form id="change-password-form">
          <div class="form-group">
            <label for="current-password">Contraseña actual</label>
            <div class="password-input-container">
              <input type="password" id="current-password" required>
              <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="form-group">
            <label for="new-password">Nueva contraseña</label>
            <div class="password-input-container">
              <input type="password" id="new-password" required minlength="6">
              <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="form-group">
            <label for="confirm-password">Confirmar nueva contraseña</label>
            <div class="password-input-container">
              <input type="password" id="confirm-password" required minlength="6">
              <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="btn-primary mt-3">
            <i class="fas fa-key"></i> Cambiar Contraseña
          </button>
        </form>
      </div>
      
      <div class="card settings-inner-card">
        <h4>Notificaciones</h4>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="email-notifications" checked>
          <label class="form-check-label" for="email-notifications">
            Recibir notificaciones por correo electrónico
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="new-music-notifications" checked>
          <label class="form-check-label" for="new-music-notifications">
            Notificarme sobre nueva música
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="subscription-notifications" checked>
          <label class="form-check-label" for="subscription-notifications">
            Notificarme sobre mi suscripción
          </label>
        </div>
        <button id="save-notifications-btn" class="btn-primary mt-3">
          <i class="fas fa-bell"></i> Guardar Preferencias
        </button>
      </div>
      
      <div class="card settings-inner-card">
        <h4>Privacidad</h4>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="public-profile" checked>
          <label class="form-check-label" for="public-profile">
            Perfil público
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="share-listening-history" checked>
          <label class="form-check-label" for="share-listening-history">
            Compartir historial de escucha
          </label>
        </div>
        <button id="save-privacy-btn" class="btn-primary mt-3">
          <i class="fas fa-user-shield"></i> Guardar Configuración
        </button>
      </div>
    </div>
  <?php endif; ?>
</section>

<style>
.settings-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 25px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  border: 1px solid var(--border-color);
}

.settings-card h3 {
  margin-top: 0;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.settings-inner-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 20px;
  margin-top: 15px;
  border-radius: 8px;
}

#color-options {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin: 15px 0;
}

.color-option {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.color-option:hover {
  transform: scale(1.2);
}

.color-option.active {
  border: 3px solid white;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

#theme-options {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin: 15px 0;
}

.theme-option {
  padding: 10px 20px;
  background: var(--bg-card);
  color: var(--text-color);
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.theme-option:hover {
  transform: translateY(-3px);
}

.theme-option.active {
  background: var(--primary-color);
  color: var(--bg-color);
  border: 2px solid white;
}

#chat-messages {
  height: 250px;
  overflow-y: auto;
  padding: 15px;
  background: var(--bg-card);
  border-radius: 8px;
  margin-bottom: 15px;
  border: 1px solid var(--border-color);
}

#chat-input {
  display: flex;
  gap: 10px;
}

#chat-text {
  flex-grow: 1;
  padding: 12px 15px;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-color);
}

#chat-send-btn {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.form-check {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
}

.form-check-input {
  margin-right: 10px;
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.form-check-label {
  cursor: pointer;
}

.mt-3 {
  margin-top: 15px;
}

.mt-4 {
  margin-top: 20px;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.password-input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-container input {
  flex-grow: 1;
  padding-right: 40px;
}

.toggle-password {
  position: absolute;
  right: 10px;
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
}

.toggle-password:hover {
  color: var(--primary-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar chat
  initChat();
  
  // Inicializar configuración de tema
  initThemeSettings();
  
  // Inicializar formularios
  initForms();
  
  // Inicializar botones para mostrar/ocultar contraseña
  initPasswordToggles();
  
  // Función para inicializar el chat
  function initChat() {
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-text');
    const chatSendBtn = document.getElementById('chat-send-btn');
    
    // Mensaje de bienvenida
    appendChatMessage('IA', '¡Bienvenido al servicio de atención al cliente! ¿En qué puedo ayudarte hoy?');
    
    // Evento para enviar mensaje
    if (chatSendBtn) {
      chatSendBtn.addEventListener('click', sendMessage);
    }
    
    if (chatInput) {
      chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          sendMessage();
        }
      });
    }
    
    // Función para enviar mensaje
    function sendMessage() {
      const text = chatInput.value.trim();
      if (text === '') return;
      
      appendChatMessage('Tú', text);
      chatInput.value = '';
      
      // Simular respuesta de IA
      setTimeout(() => {
        let response = 'Lo siento, no entendí tu solicitud. Por favor intenta con otra pregunta.';
        const lowerTxt = text.toLowerCase();
        
        if (lowerTxt.includes('suscripción')) {
          response = 'Puedes comprar una suscripción en la sección "Suscripción". Tenemos planes mensuales y anuales con diferentes beneficios.';
        } else if (lowerTxt.includes('precio')) {
          response = 'El plan mensual cuesta $5 y el anual $50. Con el plan anual ahorras un 17% comparado con el pago mensual.';
        } else if (lowerTxt.includes('ayuda')) {
          response = 'Estoy aquí para ayudarte. Puedo asistirte con información sobre tu cuenta, música, suscripciones o problemas técnicos. ¿Qué necesitas?';
        } else if (lowerTxt.includes('error')) {
          response = 'Por favor describe el error para que pueda ayudarte. Si es posible, indica en qué sección ocurrió y qué estabas haciendo.';
        } else if (lowerTxt.includes('hola') || lowerTxt.includes('saludos')) {
          response = '¡Hola! Bienvenido a nuestro servicio de atención al cliente. ¿En qué puedo ayudarte hoy?';
        } else if (lowerTxt.includes('gracias')) {
          response = 'De nada. Estamos para servirte. Si necesitas algo más, no dudes en preguntar.';
        }
        
        appendChatMessage('IA', response);
      }, 1000);
    }
    
    // Función para añadir mensaje al chat
    function appendChatMessage(from, message) {
      const msgDiv = document.createElement('div');
      msgDiv.style.marginBottom = '10px';
      msgDiv.style.padding = '8px 12px';
      msgDiv.style.borderRadius = '10px';
      msgDiv.style.maxWidth = '80%';
      msgDiv.style.animation = 'fadeIn 0.3s ease';
      
      if (from === 'Tú') {
        msgDiv.style.marginLeft = 'auto';
        msgDiv.style.background = 'var(--primary-color)';
        msgDiv.style.color = 'var(--bg-color)';
      } else {
        msgDiv.style.marginRight = 'auto';
        msgDiv.style.background = 'var(--bg-card)';
        msgDiv.style.color = 'var(--text-color)';
        msgDiv.style.borderLeft = '3px solid var(--primary-color)';
      }
      
      msgDiv.innerHTML = `<strong>${from}:</strong> ${message}`;
      chatMessages.appendChild(msgDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  }
  
  // Función para inicializar configuración de tema
  function initThemeSettings() {
    const colorOptions = document.querySelectorAll('#color-options .color-option');
    const themeOptions = document.querySelectorAll('#theme-options .theme-option');
    const saveSettingsBtn = document.getElementById('save-settings-btn');
    
    // Cargar configuración guardada
    const savedColor = localStorage.getItem('primaryColor') || '#1db954';
    const savedTheme = localStorage.getItem('theme') || 'dark';
    
    // Aplicar configuración guardada
    document.documentElement.style.setProperty('--primary-color', savedColor);
    document.documentElement.style.setProperty('--primary-hover', adjustColor(savedColor, -20));
    
    // Marcar opciones activas
    colorOptions.forEach(option => {
      if (option.dataset.color === savedColor) {
        option.classList.add('active');
      } else {
        option.classList.remove('active');
      }
      
      option.addEventListener('click', function() {
        colorOptions.forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        
        // Aplicar color inmediatamente para previsualización
        const selectedColor = this.dataset.color;
        document.documentElement.style.setProperty('--primary-color', selectedColor);
        document.documentElement.style.setProperty('--primary-hover', adjustColor(selectedColor, -20));
      });
    });
    
    themeOptions.forEach(option => {
      if (option.dataset.theme === savedTheme) {
        option.classList.add('active');
      } else {
        option.classList.remove('active');
      }
      
      option.addEventListener('click', function() {
        themeOptions.forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        
        // Aplicar tema inmediatamente para previsualización
        const selectedTheme = this.dataset.theme;
        applyTheme(selectedTheme);
      });
    });
    
    // Aplicar tema actual
    applyTheme(savedTheme);
    
    // Guardar configuración
    if (saveSettingsBtn) {
      saveSettingsBtn.addEventListener('click', function() {
        const selectedColor = document.querySelector('#color-options .color-option.active').dataset.color;
        const selectedTheme = document.querySelector('#theme-options .theme-option.active').dataset.theme;
        
        localStorage.setItem('primaryColor', selectedColor);
        localStorage.setItem('theme', selectedTheme);
        
        showToast('Configuración guardada correctamente', 'success');
      });
    }
    
    // Función para aplicar tema
    function applyTheme(theme) {
      if (theme === 'dark') {
        document.documentElement.style.setProperty('--bg-color', '#121212');
        document.documentElement.style.setProperty('--bg-secondary', '#181818');
        document.documentElement.style.setProperty('--bg-card', '#282828');
        document.documentElement.style.setProperty('--text-color', '#eee');
        document.documentElement.style.setProperty('--text-secondary', '#bbb');
        document.documentElement.style.setProperty('--border-color', '#333');
        
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
          themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
      } else if (theme === 'light') {
        document.documentElement.style.setProperty('--bg-color', '#f5f5f5');
        document.documentElement.style.setProperty('--bg-secondary', '#ffffff');
        document.documentElement.style.setProperty('--bg-card', '#e9e9e9');
        document.documentElement.style.setProperty('--text-color', '#333');
        document.documentElement.style.setProperty('--text-secondary', '#666');
        document.documentElement.style.setProperty('--border-color', '#ddd');
        
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
          themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
      } else if (theme === 'contrast') {
        document.documentElement.style.setProperty('--bg-color', '#000000');
        document.documentElement.style.setProperty('--bg-secondary', '#0a0a0a');
        document.documentElement.style.setProperty('--bg-card', '#1a1a1a');
        document.documentElement.style.setProperty('--text-color', '#ffffff');
        document.documentElement.style.setProperty('--text-secondary', '#cccccc');
        document.documentElement.style.setProperty('--border-color', '#444');
        
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
          themeToggle.innerHTML = '<i class="fas fa-adjust"></i>';
        }
      }
    }
  }
  
  // Función para inicializar formularios
  function initForms() {
    const changePasswordForm = document.getElementById('change-password-form');
    const saveNotificationsBtn = document.getElementById('save-notifications-btn');
    const savePrivacyBtn = document.getElementById('save-privacy-btn');
    
    if (changePasswordForm) {
      changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (newPassword !== confirmPassword) {
          showToast('Las contraseñas no coinciden', 'error');
          return;
        }
        
        // Enviar solicitud para cambiar contraseña
        fetch('api/user.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=change_password&current_password=${encodeURIComponent(currentPassword)}&new_password=${encodeURIComponent(newPassword)}&confirm_password=${encodeURIComponent(confirmPassword)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Contraseña actualizada correctamente', 'success');
            this.reset();
          } else {
            showToast(data.message || 'Error al actualizar la contraseña', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error al comunicarse con el servidor', 'error');
        });
      });
    }
    
    if (saveNotificationsBtn) {
      saveNotificationsBtn.addEventListener('click', function() {
        showToast('Preferencias de notificaciones guardadas', 'success');
      });
    }
    
    if (savePrivacyBtn) {
      savePrivacyBtn.addEventListener('click', function() {
        showToast('Configuración de privacidad guardada', 'success');
      });
    }
  }
  
  // Función para inicializar botones de mostrar/ocultar contraseña
  function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        // Cambiar el icono
        const icon = this.querySelector('i');
        if (type === 'password') {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        } else {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        }
      });
    });
  }
  
  // Función para ajustar color
  function adjustColor(color, amount) {
    return '#' + color.replace(/^#/, '').replace(/../g, color => ('0' + Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2));
  }
  
  // Función para mostrar toast
  function showToast(message, type = 'info') {
    // Crear el elemento toast
    const toast = document.createElement('div');
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%) translateY(-100px)';
    toast.style.padding = '10px 20px';
    toast.style.borderRadius = '5px';
    toast.style.boxShadow = '0 3px 10px rgba(0,0,0,0.3)';
    toast.style.zIndex = '1000';
    toast.style.transition = 'all 0.3s ease';
    
    // Establecer colores según el tipo
    if (type === 'error') {
      toast.style.background = '#e74c3c';
      toast.style.color = '#fff';
      toast.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    } else if (type === 'success') {
      toast.style.background = 'var(--primary-color)';
      toast.style.color = 'var(--bg-color)';
      toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    } else {
      toast.style.background = '#3498db';
      toast.style.color = '#fff';
      toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
    }
    
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(0)';
    }, 10);
    
    // Eliminar después de 3 segundos
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(-100px)';
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }
});
</script>