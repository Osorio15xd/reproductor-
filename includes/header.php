<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title>Bass Culture - Reproductor de Música</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Animate.css para animaciones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<!-- Estilos personalizados -->
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
  <!-- Logo -->
  <img id="logo" src="C:\xampp\htdocs\reproductor\assets\img\logo.jpg" alt="Bass Culture Logo" />
  <!-- Navigation links -->
  <nav id="nav-links">
    <a href="index.php?page=inicio" class="nav-link <?php echo $page == 'inicio' ? 'active' : ''; ?>">Inicio</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="index.php?page=biblioteca" class="nav-link <?php echo $page == 'biblioteca' ? 'active' : ''; ?>">Biblioteca</a>
    <a href="index.php?page=playlist" class="nav-link <?php echo $page == 'playlist' ? 'active' : ''; ?>">Playlist</a>
    <?php endif; ?>
    <a href="index.php?page=artistas" class="nav-link <?php echo $page == 'artistas' ? 'active' : ''; ?>">Artistas</a>
    <a href="index.php?page=carrito" class="nav-link <?php echo $page == 'carrito' ? 'active' : ''; ?>">Carrito</a>
    <a href="pages/music-store.php" class="nav-link">Tienda</a>
    <a href="index.php?page=enhanced-player" class="nav-link <?php echo $page == 'enhanced-player' ? 'active' : ''; ?>">Reproductor</a>
    <a href="index.php?page=chatbot" class="nav-link <?php echo $page == 'chatbot' ? 'active' : ''; ?>">Asistente</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="index.php?page=configuraciones" class="nav-link <?php echo $page == 'configuraciones' ? 'active' : ''; ?>">Configuraciones</a>
    <a href="index.php?page=subscription" class="nav-link <?php echo $page == 'subscription' ? 'active' : ''; ?>" id="nav-subscription-link">Suscripción</a>
    <a href="index.php?page=estadisticas" class="nav-link <?php echo $page == 'estadisticas' ? 'active' : ''; ?>">Estadísticas</a>
    <?php endif; ?>
  </nav>
  <!-- Search form -->
  <form id="search-form" action="index.php" method="GET">
    <input type="hidden" name="page" value="inicio">
    <input type="search" id="search-input" name="search" placeholder="Buscar música..." autocomplete="off" />
  </form>
  <!-- User section with login/register or profile info -->
  <div id="user-section">
    <?php if(isset($_SESSION['user_id'])): ?>
      <div class="user-profile-container">
        <img id="user-photo" src="<?php echo !empty($_SESSION['user_photo']) ? $_SESSION['user_photo'] : 'assets/img/default-user.png'; ?>" alt="Foto de usuario" />
        <span id="username-display"><?php echo $_SESSION['username']; ?></span>
        
        <!-- User dropdown menu -->
        <div id="user-dropdown">
          <a href="index.php?page=perfil" class="dropdown-item" id="dropdown-profile">
            <i class="fas fa-user-circle"></i> Mi Perfil
          </a>
          <a href="index.php?page=upload" class="dropdown-item" id="dropdown-upload">
            <i class="fas fa-upload"></i> Subir Música
          </a>
          <a href="index.php?page=configuraciones" class="dropdown-item" id="dropdown-settings">
            <i class="fas fa-cog"></i> Configuraciones
          </a>
          <a href="index.php?page=subscription" class="dropdown-item" id="dropdown-subscription">
            <i class="fas fa-star"></i> Mi Suscripción
          </a>
          <a href="index.php?page=estadisticas" class="dropdown-item" id="dropdown-stats">
            <i class="fas fa-chart-bar"></i> Estadísticas
          </a>
          <a href="index.php?page=chatbot" class="dropdown-item" id="dropdown-chatbot">
            <i class="fas fa-robot"></i> Asistente Virtual
          </a>
          <a href="auth/logout.php" class="dropdown-item danger" id="dropdown-logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
        </div>
      </div>
    <?php else: ?>
      <button id="login-btn" onclick="window.location.href='index.php?page=login'"><i class="fas fa-user"></i> Iniciar Sesión / Registrarse</button>
    <?php endif; ?>
  </div>
</header>
<main>
