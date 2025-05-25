<?php
// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
require_once 'config/db_connect.php';

// Determinar qué página mostrar
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

// Validar la página solicitada
$allowed_pages = [
    'inicio', 'biblioteca', 'playlist', 'artistas', 'carrito', 
    'configuraciones', 'subscription', 'login', 'register', 
    'recover', 'reset', 'estadisticas', 'upload', 'enhanced-player', 
    'music-store', 'perfil', 'chatbot'
];

if (!in_array($page, $allowed_pages)) {
   $page = 'inicio';
}

// Verificar si el usuario está logueado para ciertas páginas
$restricted_pages = ['biblioteca', 'perfil', 'estadisticas', 'upload'];
if (in_array($page, $restricted_pages) && !isset($_SESSION['user_id'])) {
   // Redirigir a login si intenta acceder a páginas restringidas
   header('Location: index.php?page=login');
   exit;
}

// Incluir el encabezado
include 'includes/header.php';

// Incluir la página solicitada
if ($page == 'login' || $page == 'register' || $page == 'recover' || $page == 'reset') {
   include 'auth/' . $page . '.php';
} else {
   include 'pages/' . $page . '.php';
}

// Incluir el reproductor si el usuario está logueado
if (isset($_SESSION['user_id'])) {
   include 'includes/player.php';
}

// Incluir el pie de página
include 'includes/footer.php';
?>
