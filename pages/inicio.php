<?php
// Obtener géneros musicales
$stmt = $conn->query("SELECT id_genero, nombre_genero FROM genero ORDER BY nombre_genero");
$generos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtrar por género si se especifica
$genero_filtro = isset($_GET['genero']) ? intval($_GET['genero']) : null;
$busqueda = isset($_GET['search']) ? $_GET['search'] : null;

// Obtener canciones destacadas (combinando canciones de álbumes y sencillos)
$canciones = [];

try {
    // Consulta para canciones de álbumes
    $sql_album = "
        SELECT 
            c.id_cancion as id, 
            c.nombre_cancion as titulo, 
            a.imagen_album_path as portada, 
            c.cancion_path as archivo_audio, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            'album_song' as tipo
        FROM canciones c
        JOIN album a ON c.id_album = a.id_album
        JOIN artista art ON c.id_artista = art.id_artista
        JOIN usuario u ON art.usuario = u.id_usuario
        JOIN genero g ON a.id_genero = g.id_genero
    ";
    
    // Consulta para sencillos
    $sql_sencillo = "
        SELECT 
            s.id_sencillo as id, 
            s.nombre_sencillo as titulo, 
            s.imagen_sencillo_path as portada, 
            s.cancion_path as archivo_audio, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            'sencillo' as tipo
        FROM sencillos s
        JOIN artista a ON s.id_artista = a.id_artista
        JOIN usuario u ON a.usuario = u.id_usuario
        JOIN genero g ON s.id_genero = g.id_genero
    ";
    
    // Añadir filtros si existen
    $params = [];
    
    if ($genero_filtro) {
        $sql_album .= " WHERE a.id_genero = ?";
        $sql_sencillo .= " WHERE s.id_genero = ?";
        $params[] = $genero_filtro;
    } elseif ($busqueda) {
        $busqueda_param = "%$busqueda%";
        $sql_album .= " WHERE c.nombre_cancion LIKE ? OR u.nombre_usuario LIKE ?";
        $sql_sencillo .= " WHERE s.nombre_sencillo LIKE ? OR u.nombre_usuario LIKE ?";
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
    }
    
    // Limitar resultados
    $sql_album .= " ORDER BY c.id_cancion DESC LIMIT 10";
    $sql_sencillo .= " ORDER BY s.id_sencillo DESC LIMIT 10";
    
    // Ejecutar consulta para canciones de álbumes
    $stmt = $conn->prepare($sql_album);
    if (!empty($params)) {
        if ($busqueda) {
            $stmt->execute([$params[0], $params[1]]);
        } else {
            $stmt->execute([$params[0]]);
        }
    } else {
        $stmt->execute();
    }
    $canciones_album = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ejecutar consulta para sencillos
    $stmt = $conn->prepare($sql_sencillo);
    if (!empty($params)) {
        if ($busqueda) {
            $stmt->execute([$params[0], $params[1]]);
        } else {
            $stmt->execute([$params[0]]);
        }
    } else {
        $stmt->execute();
    }
    $sencillos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combinar resultados
    $canciones = array_merge($canciones_album, $sencillos);
    
    // Ordenar por ID de forma aleatoria para mezclar canciones y sencillos
    shuffle($canciones);
    
} catch (PDOException $e) {
    // Manejar error
    echo "<div class='alert alert-danger'>Error al cargar la música: " . $e->getMessage() . "</div>";
}

// Obtener canciones más populares (basado en reproducciones)
try {
    // Consulta para canciones populares (combinando álbumes y sencillos)
    $sql_populares = "
        (SELECT 
            c.id_cancion as id, 
            c.nombre_cancion as titulo, 
            a.imagen_album_path as portada, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            COUNT(r.id_reproduccion) as reproducciones,
            'album_song' as tipo
        FROM canciones c
        JOIN album a ON c.id_album = a.id_album
        JOIN artista art ON c.id_artista = art.id_artista
        JOIN usuario u ON art.usuario = u.id_usuario
        JOIN genero g ON a.id_genero = g.id_genero
        LEFT JOIN reproducciones r ON r.id_cancion = c.id_cancion AND r.tipo = 'album_song'
        GROUP BY c.id_cancion
        ORDER BY reproducciones DESC
        LIMIT 4)
        
        UNION
        
        (SELECT 
            s.id_sencillo as id, 
            s.nombre_sencillo as titulo, 
            s.imagen_sencillo_path as portada, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            COUNT(r.id_reproduccion) as reproducciones,
            'sencillo' as tipo
        FROM sencillos s
        JOIN artista a ON s.id_artista = a.id_artista
        JOIN usuario u ON a.usuario = u.id_usuario
        JOIN genero g ON s.id_genero = g.id_genero
        LEFT JOIN reproducciones r ON r.id_sencillo = s.id_sencillo AND r.tipo = 'sencillo'
        GROUP BY s.id_sencillo
        ORDER BY reproducciones DESC
        LIMIT 4)
        
        ORDER BY reproducciones DESC
        LIMIT 8
    ";
    
    $stmt = $conn->query($sql_populares);
    $canciones_populares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Manejar error
    $canciones_populares = [];
    echo "<div class='alert alert-danger'>Error al cargar canciones populares: " . $e->getMessage() . "</div>";
}
require_once 'includes/header.php';
?>

<section id="page-inicio" class="page active" aria-label="Inicio">
  <h1 class="animate-fade-up">Inicio</h1>
  
  <div id="genre-buttons" class="animate-slide-left" aria-label="Géneros musicales">
    <a href="index.php?page=inicio" class="genre-btn <?php echo !$genero_filtro ? 'active' : ''; ?>">Todos</a>
    <?php foreach ($generos as $genero): ?>
    <a href="index.php?page=inicio&genero=<?php echo $genero['id_genero']; ?>" class="genre-btn <?php echo $genero_filtro == $genero['id_genero'] ? 'active' : ''; ?>">
      <?php echo $genero['nombre_genero']; ?>
    </a>
    <?php endforeach; ?>
  </div>
  
  <h2 class="animate-fade-up">Música destacada</h2>
  <div id="featured-music" class="card-list animate-slide-right" aria-live="polite" aria-relevant="additions">
    <?php if (count($canciones) > 0): ?>
      <?php foreach ($canciones as $cancion): ?>
      <div class="card" tabindex="0" role="button" aria-pressed="false" data-id="<?php echo $cancion['id']; ?>" data-type="<?php echo $cancion['tipo']; ?>">
        <img src="<?php echo $cancion['portada']; ?>" alt="Portada de <?php echo $cancion['titulo']; ?>" onerror="this.src='assets/img/default-cover.png';" />
        <div class="card-info">
          <h4><?php echo $cancion['titulo']; ?></h4>
          <p><?php echo $cancion['artista']; ?></p>
        </div>
        <div class="play-icon"><i class="fas fa-play"></i></div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-music" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
        <h3>No se encontraron canciones</h3>
        <p>Intenta con otro género o término de búsqueda.</p>
      </div>
    <?php endif; ?>
  </div>
  
  <h2 class="animate-fade-up">Canciones Populares</h2>
  <div id="top-songs" class="card-list animate-slide-right" aria-live="polite" aria-relevant="additions">
    <?php if (count($canciones_populares) > 0): ?>
      <?php foreach ($canciones_populares as $cancion): ?>
      <div class="card" tabindex="0" role="button" aria-pressed="false" data-id="<?php echo $cancion['id']; ?>" data-type="<?php echo $cancion['tipo']; ?>">
        <img src="<?php echo $cancion['portada']; ?>" alt="Portada de <?php echo $cancion['titulo']; ?>" onerror="this.src='assets/img/default-cover.png';" />
        <div class="card-info">
          <h4><?php echo $cancion['titulo']; ?></h4>
          <p><?php echo $cancion['artista']; ?></p>
        </div>
        <div class="play-icon"><i class="fas fa-play"></i></div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-chart-line" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
        <h3>Aún no hay datos de popularidad</h3>
        <p>Las canciones más reproducidas aparecerán aquí.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Función para reproducir una canción
  function playSong(songId, songType) {
    // Registrar reproducción
    fetch(`api/music.php?action=record_play&id=${songId}&type=${songType}`, {
      method: 'POST'
    });
    
    // Obtener información de la canción
    fetch(`api/music.php?action=get_song&id=${songId}&type=${songType}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Actualizar el reproductor con la información de la canción
          const player = document.getElementById('music-player');
          if (player) {
            const audioElement = player.querySelector('audio');
            const songTitle = player.querySelector('.song-info h3');
            const artistName = player.querySelector('.song-info p');
            const coverImage = player.querySelector('.song-cover img');
            
            audioElement.src = data.song.archivo_audio;
            songTitle.textContent = data.song.titulo;
            artistName.textContent = data.song.artista;
            coverImage.src = data.song.portada;
            
            // Reproducir la canción
            audioElement.play();
            
            // Mostrar el reproductor si está oculto
            player.classList.add('active');
          }
        } else {
          showToast('Error al cargar la canción', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al comunicarse con el servidor', 'error');
      });
  }
  
  // Agregar eventos a las tarjetas de canciones
  const songCards = document.querySelectorAll('.card');
  songCards.forEach(card => {
    card.addEventListener('click', function() {
      const songId = this.dataset.id;
      const songType = this.dataset.type;
      playSong(songId, songType);
    });
    
    // También agregar soporte para teclado (accesibilidad)
    card.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const songId = this.dataset.id;
        const songType = this.dataset.type;
        playSong(songId, songType);
      }
    });
  });
});
</script>
