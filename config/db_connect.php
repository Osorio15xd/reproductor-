<?php
// Configuración de la base de datos
$host = "localhost";
$db_name = "bassculture"; 
$username = "root";
$password = ""; 

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    
    // Configurar el modo de error de PDO para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar el modo de recuperación de datos por defecto
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Verificar si la base de datos existe, si no, crearla
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE `$db_name`");
    
    // Crear tablas si no existen
    $tables = [
        "CREATE TABLE IF NOT EXISTS `generos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS `artistas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `biografia` text,
            `foto` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS `canciones` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `titulo` varchar(100) NOT NULL,
            `artista_id` int(11) NOT NULL,
            `genero_id` int(11) NOT NULL,
            `portada` varchar(255) DEFAULT NULL,
            `archivo_audio` varchar(255) NOT NULL,
            `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
            `fecha_subida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `artista_id` (`artista_id`),
            KEY `genero_id` (`genero_id`),
            CONSTRAINT `canciones_ibfk_1` FOREIGN KEY (`artista_id`) REFERENCES `artistas` (`id`) ON DELETE CASCADE,
            CONSTRAINT `canciones_ibfk_2` FOREIGN KEY (`genero_id`) REFERENCES `generos` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "CREATE TABLE IF NOT EXISTS `biblioteca_usuario` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) NOT NULL,
            `cancion_id` int(11) NOT NULL,
            `fecha_agregado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `usuario_cancion` (`usuario_id`,`cancion_id`),
            KEY `cancion_id` (`cancion_id`),
            CONSTRAINT `biblioteca_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
            CONSTRAINT `biblioteca_usuario_ibfk_2` FOREIGN KEY (`cancion_id`) REFERENCES `canciones` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    // Ejecutar las consultas para crear las tablas
    foreach ($tables as $sql) {
        $conn->exec($sql);
    }
    
    // Verificar si hay datos de ejemplo en las tablas
    $stmt = $conn->query("SELECT COUNT(*) as count FROM generos");
    $generos_count = $stmt->fetch()['count'];
    
    if ($generos_count == 0) {
        // Insertar géneros de ejemplo
        $generos = [
            "Rock", "Pop", "Hip Hop", "Electrónica", "Jazz", "Blues", "Reggae", 
            "R&B", "Country", "Folk", "Clásica", "Metal", "Punk", "Indie", "Latino"
        ];
        
        $stmt = $conn->prepare("INSERT INTO generos (nombre) VALUES (?)");
        foreach ($generos as $genero) {
            $stmt->execute([$genero]);
        }
        
        // Insertar artistas de ejemplo
        $artistas = [
            "The Beatles", "Queen", "Michael Jackson", "Madonna", "Bob Marley", 
            "David Bowie", "Beyoncé", "Adele", "Ed Sheeran", "Taylor Swift"
        ];
        
        $stmt = $conn->prepare("INSERT INTO artistas (nombre, biografia, foto) VALUES (?, ?, ?)");
        foreach ($artistas as $artista) {
            $stmt->execute([$artista, "Biografía de " . $artista, "assets/img/placeholder.svg"]);
        }
    }
    
} catch(PDOException $e) {
    // En caso de error, mostrar mensaje y terminar el script
    die("Error de conexión: " . $e->getMessage());
}
?>
