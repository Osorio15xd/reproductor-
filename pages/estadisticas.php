<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];

// Obtener estadísticas del usuario
try {
    // Obtener canciones más escuchadas (simulado con me_gusta)
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN mg.tipo_item = 'cancion' THEN c.nombre_cancion
                WHEN mg.tipo_item = 'sencillo' THEN s.nombre_sencillo
            END as nombre,
            CASE 
                WHEN mg.tipo_item = 'cancion' THEN u_c.nombre_usuario
                WHEN mg.tipo_item = 'sencillo' THEN u_s.nombre_usuario
            END as artista,
            COUNT(mg.id_me_gusta) as reproducciones
        FROM me_gusta mg
        LEFT JOIN canciones c ON mg.tipo_item = 'cancion' AND mg.id_item = c.id_cancion
        LEFT JOIN artista art_c ON c.id_artista = art_c.id_artista
        LEFT JOIN usuario u_c ON art_c.usuario = u_c.id_usuario
        LEFT JOIN sencillos s ON mg.tipo_item = 'sencillo' AND mg.id_item = s.id_sencillo
        LEFT JOIN artista art_s ON s.id_artista = art_s.id_artista
        LEFT JOIN usuario u_s ON art_s.usuario = u_s.id_usuario
        WHERE mg.id_usuario = :user_id AND (mg.tipo_item = 'cancion' OR mg.tipo_item = 'sencillo')
        GROUP BY mg.id_item, mg.tipo_item
        ORDER BY reproducciones DESC
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $topSongs = $stmt->fetchAll();
    
    // Obtener artistas más escuchados
    $stmt = $conn->prepare("
        SELECT 
            u.nombre_usuario as artista,
            COUNT(mg.id_me_gusta) as reproducciones
        FROM me_gusta mg
        LEFT JOIN canciones c ON mg.tipo_item = 'cancion' AND mg.id_item = c.id_cancion
        LEFT JOIN sencillos s ON mg.tipo_item = 'sencillo' AND mg.id_item = s.id_sencillo
        LEFT JOIN artista a ON 
            (mg.tipo_item = 'cancion' AND c.id_artista = a.id_artista) OR 
            (mg.tipo_item = 'sencillo' AND s.id_artista = a.id_artista) OR
            (mg.tipo_item = 'artista' AND mg.id_item = a.id_artista)
        LEFT JOIN usuario u ON a.usuario = u.id_usuario
        WHERE mg.id_usuario = :user_id AND u.nombre_usuario IS NOT NULL
        GROUP BY a.id_artista
        ORDER BY reproducciones DESC
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $topArtists = $stmt->fetchAll();
    
    // Obtener géneros preferidos
    $stmt = $conn->prepare("
        SELECT 
            g.nombre_genero as genero,
            pm.nivel_interes as interes
        FROM preferencias_musicales pm
        JOIN genero g ON pm.id_genero = g.id_genero
        WHERE pm.id_usuario = :user_id
        ORDER BY pm.nivel_interes DESC
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $genres = $stmt->fetchAll();
    
    // Obtener compras recientes
    $stmt = $conn->prepare("
        SELECT 
            c.tipo_producto,
            CASE 
                WHEN c.tipo_producto = 'album' THEN a.nombre_album
                WHEN c.tipo_producto = 'cancion' THEN can.nombre_cancion
                WHEN c.tipo_producto = 'sencillo' THEN s.nombre_sencillo
            END as nombre,
            c.precio,
            c.fecha_compra
        FROM compras c
        LEFT JOIN album a ON c.tipo_producto = 'album' AND c.id_producto = a.id_album
        LEFT JOIN canciones can ON c.tipo_producto = 'cancion' AND c.id_producto = can.id_cancion
        LEFT JOIN sencillos s ON c.tipo_producto = 'sencillo' AND c.id_producto = s.id_sencillo
        WHERE c.id_usuario = :user_id
        ORDER BY c.fecha_compra DESC
        LIMIT 5
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $recentPurchases = $stmt->fetchAll();
    
    // Obtener estadísticas del chatbot
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_chats
        FROM ai_chat_history
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $chatStats = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener estadísticas: " . $e->getMessage();
}
?>

<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="row">
            <!-- Canciones más escuchadas -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-music me-2"></i>Canciones más escuchadas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topSongs)): ?>
                            <p class="text-muted">Aún no tienes canciones escuchadas.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($topSongs as $song): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($song['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($song['artista']); ?></small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo $song['reproducciones']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Artistas más escuchados -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Artistas favoritos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topArtists)): ?>
                            <p class="text-muted">Aún no tienes artistas favoritos.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($topArtists as $artist): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($artist['artista']); ?></strong>
                                        <span class="badge bg-primary rounded-pill"><?php echo $artist['reproducciones']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Géneros preferidos -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-guitar me-2"></i>Géneros preferidos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($genres)): ?>
                            <p class="text-muted">Aún no has establecido preferencias de géneros.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($genres as $genre): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong><?php echo htmlspecialchars($genre['genero']); ?></strong>
                                            <span class="badge bg-primary rounded-pill"><?php echo $genre['interes']; ?>/10</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $genre['interes'] * 10; ?>%;" 
                                                 aria-valuenow="<?php echo $genre['interes']; ?>" aria-valuemin="0" aria-valuemax="10"></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Compras recientes -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Compras recientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentPurchases)): ?>
                            <p class="text-muted">Aún no has realizado compras.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recentPurchases as $purchase): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($purchase['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo ucfirst($purchase['tipo_producto']); ?> • 
                                                <?php echo date('d/m/Y', strtotime($purchase['fecha_compra'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-success rounded-pill">$<?php echo number_format($purchase['precio'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas del chatbot -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-robot me-2"></i>Interacciones con el Asistente</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h2 class="display-4"><?php echo $chatStats['total_chats']; ?></h2>
                            <p class="text-muted">Total de mensajes enviados al asistente</p>
                            <a href="index.php?page=chatbot" class="btn btn-primary mt-2">
                                <i class="fas fa-comments me-2"></i>Ir al Asistente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
