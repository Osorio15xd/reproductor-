<?php
// Verificar si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            // Buscar usuario por email
            $stmt = $conn->prepare("SELECT id, nombre, email, password, foto, fecha_registro FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['foto'];
                
                // Redirigir a la página principal
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error al iniciar sesión: ' . $e->getMessage();
        }
    }
}
?>

<section id="page-login" class="page active" aria-label="Iniciar sesión o registrarse">
  <h1>Iniciar Sesión</h1>
  
  <?php if ($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  
  <form id="login-form" class="animate-fade-up" method="POST" action="">
    <label for="login-email">Correo electrónico</label>
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="fas fa-envelope"></i></span>
      <input type="email" id="login-email" name="email" class="form-control" required />
    </div>
    
    <label for="login-password">Contraseña</label>
    <div class="input-group mb-3 password-input-container">
      <span class="input-group-text"><i class="fas fa-lock"></i></span>
      <input type="password" id="login-password" name="password" class="form-control" required />
      <button type="button" class="toggle-password" tabindex="-1"><i class="fas fa-eye"></i></button>
    </div>
    
    <div class="form-options">
      <div class="remember-me">
        <input type="checkbox" id="remember-me" name="remember_me">
        <label for="remember-me">Recordarme</label>
      </div>
      <a href="index.php?page=recover" class="forgot-password">¿Olvidaste tu contraseña?</a>
    </div>
    
    <button type="submit" name="login" id="login-submit-btn"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
    
    <p style="margin-top: 12px; font-size: 0.9rem;">
      ¿No tienes cuenta?
      <a href="index.php?page=register">Regístrate aquí</a>
    </p>
  </form>
</section>

<style>
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.remember-me {
  display: flex;
  align-items: center;
}

.remember-me input {
  margin-right: 5px;
}

.forgot-password {
  color: var(--primary-color);
  text-decoration: none;
}

.forgot-password:hover {
  text-decoration: underline;
}

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
      const input = document.getElementById('login-password');
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
});
</script>
