<?php
// Verificar si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Este correo electrónico ya está registrado.';
        } else {
            // Crear el usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $hashed_password])) {
                // Iniciar sesión automáticamente
                $user_id = $conn->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $nombre;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_photo'] = '';
                
                // Redirigir a la página principal
                header('Location: index.php');
                exit;
            } else {
                $error = 'Error al registrar el usuario. Inténtalo de nuevo.';
            }
        }
    }
}
?>

<section id="page-register" class="page active" aria-label="Registrarse">
  <h1>Registrarse</h1>
  
  <?php if ($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  
  <form id="register-form" class="animate-fade-up" method="POST" action="">
    <label for="register-name">Nombre completo</label>
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="fas fa-user"></i></span>
      <input type="text" id="register-name" name="nombre" class="form-control" required />
    </div>
    
    <label for="register-email">Correo electrónico</label>
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="fas fa-envelope"></i></span>
      <input type="email" id="register-email" name="email" class="form-control" required />
    </div>
    
    <label for="register-password">Contraseña</label>
    <div class="input-group mb-3 password-input-container">
      <span class="input-group-text"><i class="fas fa-lock"></i></span>
      <input type="password" id="register-password" name="password" class="form-control" required minlength="6" />
      <button type="button" class="toggle-password" tabindex="-1"><i class="fas fa-eye"></i></button>
    </div>
    
    <div class="password-strength">
      <div class="strength-meter">
        <div class="strength-meter-fill" data-strength="0"></div>
      </div>
      <div class="strength-text">Fuerza de la contraseña: <span>Débil</span></div>
    </div>
    
    <button type="submit" name="register" id="register-submit-btn"><i class="fas fa-user-plus"></i> Registrarse</button>
    
    <p style="margin-top: 12px; font-size: 0.9rem;">
      ¿Ya tienes cuenta?
      <a href="index.php?page=login">Inicia sesión aquí</a>
    </p>
  </form>
</section>

<style>
.alert {
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 8px;
  text-align: center;
}

.alert-danger {
  background-color: rgba(231, 76, 60, 0.2);
  border: 1px solid #e74c3c;
  color: #e74c3c;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar botón para mostrar/ocultar contraseña
  const toggleButton = document.querySelector('.toggle-password');
  
  if (toggleButton) {
    toggleButton.addEventListener('click', function() {
      const input = document.getElementById('register-password');
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
  }
  
  // Medidor de fuerza de contraseña
  const passwordInput = document.getElementById('register-password');
  if (passwordInput) {
    passwordInput.addEventListener('input', function() {
      const password = this.value;
      const strengthMeter = document.querySelector('.strength-meter-fill');
      const strengthText = document.querySelector('.strength-text span');
      
      // Calcular fuerza de la contraseña
      let strength = 0;
      
      // Longitud
      if (password.length >= 8) {
        strength += 1;
      }
      
      // Letras mayúsculas y minúsculas
      if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
        strength += 1;
      }
      
      // Números
      if (password.match(/\d/)) {
        strength += 1;
      }
      
      // Caracteres especiales
      if (password.match(/[^a-zA-Z0-9]/)) {
        strength += 1;
      }
      
      // Actualizar UI
      strengthMeter.setAttribute('data-strength', strength);
      
      // Actualizar texto
      switch (strength) {
        case 0:
          strengthText.textContent = 'Débil';
          strengthText.style.color = '#e74c3c';
          break;
        case 1:
          strengthText.textContent = 'Débil';
          strengthText.style.color = '#e74c3c';
          break;
        case 2:
          strengthText.textContent = 'Moderada';
          strengthText.style.color = '#f39c12';
          break;
        case 3:
          strengthText.textContent = 'Buena';
          strengthText.style.color = '#3498db';
          break;
        case 4:
          strengthText.textContent = 'Fuerte';
          strengthText.style.color = '#2ecc71';
          break;
      }
    });
  }
});
</script>
