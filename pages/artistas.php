<?php
// Obtener todos los artistas
try {
    $stmt = $conn->query("
        SELECT 
            a.id_artista, 
            u.nombre_usuario as nombre, 
            u.foto_perfil as foto, 
            a.biografia as bio,
            (SELECT COUNT(*) FROM canciones c WHERE c.id_artista = a.id_artista) + 
            (SELECT COUNT(*) FROM sencillos s WHERE s.id_artista = a.id_artista) as canciones
        FROM artista a
        JOIN usuario u ON a.usuario = u.id_usuario
        ORDER BY u.nombre_usuario
    ");
    $artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar artistas: " . $e->getMessage() . "</div>";
    $artistas = [];
}

// Obtener artista específico si se solicita
$artista_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$artista_actual = null;
$canciones_artista = [];
$albums_artista = [];

if ($artista_id > 0) {
    try {
        // Obtener información del artista
        $stmt = $conn->prepare("
            SELECT 
                a.id_artista, 
                u.nombre_usuario as nombre, 
                u.foto_perfil as foto, 
                a.biografia as bio
            FROM artista a
            JOIN usuario u ON a.usuario = u.id_usuario
            WHERE a.id_artista = ?
        ");
        $stmt->execute([$artista_id]);
        $artista_actual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($artista_actual) {
            // Obtener canciones del artista (de álbumes)
            $stmt = $conn->prepare("
                SELECT 
                    c.id_cancion as id, 
                    c.nombre_cancion as titulo, 
                    a.imagen_album_path as portada, 
                    c.cancion_path as archivo_audio, 
                    g.nombre_genero as genero,
                    a.nombre_album as album,
                    'album_song' as tipo
                FROM canciones c
                JOIN album a ON c.id_album = a.id_album
                JOIN genero g ON a.id_genero = g.id_genero
                WHERE c.id_artista = ?
                ORDER BY a.nombre_album, c.nombre_cancion
            ");
            $stmt->execute([$artista_id]);
            $canciones_album = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener sencillos del artista
            $stmt = $conn->prepare("
                SELECT 
                    s.id_sencillo as id, 
                    s.nombre_sencillo as titulo, 
                    s.imagen_sencillo_path as portada, 
                    s.cancion_path as archivo_audio, 
                    g.nombre_genero as genero,
                    NULL as album,
                    'sencillo' as tipo
                FROM sencillos s
                JOIN genero g ON s.id_genero = g.id_genero
                WHERE s.id_artista = ?
                ORDER BY s.nombre_sencillo
            ");
            $stmt->execute([$artista_id]);
            $sencillos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar canciones de álbumes y sencillos
            $canciones_artista = array_merge($canciones_album, $sencillos);
            
            // Obtener álbumes del artista
            $stmt = $conn->prepare("
                SELECT 
                    id_album, 
                    nombre_album, 
                    imagen_album_path, 
                    fecha_lanzamiento
                FROM album
                WHERE id_artista = ?
                ORDER BY fecha_lanzamiento DESC
            ");
            $stmt->execute([$artista_id]);
            $albums_artista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al cargar información del artista: " . $e->getMessage() . "</div>";
    }
}
?>

<section id="page-artistas" class="page active" aria-label="Artistas">
  <?php if ($artista_actual): ?>
    <!-- Vista detallada del artista -->
    <div class="artist-detail">
      <div class="artist-header">
        <a href="index.php?page=artistas" class="back-button"><i class="fas fa-arrow-left"></i> Volver a artistas</a>
        <div class="artist-profile">
          <img src="<?php echo $artista_actual['foto']; ?>" alt="<?php echo $artista_actual['nombre']; ?>" class="artist-photo" onerror="this.src='assets/img/default-user.png';">
          <div class="artist-info">
            <h1><?php echo $artista_actual['nombre']; ?></h1>
            <p class="artist-songs-count"><?php echo count($canciones_artista); ?> canciones</p>
            <?php if (!empty($artista_actual['bio'])): ?>
              <p class="artist-bio"><?php echo $artista_actual['bio']; ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <?php if (count($albums_artista) > 0): ?>
        <h2>Álbumes de <?php echo $artista_actual['nombre']; ?></h2>
        <div class="albums-list card-list">
          <?php foreach ($albums_artista as $album): ?>
            <a href="index.php?page=album&id=<?php echo $album['id_album']; ?>" class="card" tabindex="0">
              <img src="<?php echo $album['imagen_album_path']; ?>" alt="Portada de <?php echo $album['nombre_album']; ?>" onerror="this.src='assets/img/default-cover.png';" />
              <div class="card-info">
                <h4><?php echo $album['nombre_album']; ?></h4>
                <p><?php echo date('Y', strtotime($album['fecha_lanzamiento'])); ?></p>
              </div>
              <div class="play-icon"><i class="fas fa-compact-disc"></i></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <h2>Canciones de <?php echo $artista_actual['nombre']; ?></h2>
      <?php if (count($canciones_artista) > 0): ?>
        <div class="songs-list">
          <table class="songs-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Título</th>
                <th>Álbum</th>
                <th>Género</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($canciones_artista as $index => $cancion): ?>
                <tr data-id="<?php echo $cancion['id']; ?>" data-type="<?php echo $cancion['tipo']; ?>">
                  <td><?php echo $index + 1; ?></td>
                  <td>
                    <div class="song-info-cell">
                      <img src="<?php echo $cancion['portada']; ?>" alt="Portada" class="song-thumbnail-small" onerror="this.src='assets/img/default-cover.png';">
                      <span><?php echo $cancion['titulo']; ?></span>
                    </div>
                  </td>
                  <td><?php echo $cancion['album'] ? $cancion['album'] : 'Sencillo'; ?></td>
                  <td><?php echo $cancion['genero']; ?></td>
                  <td>
                    <div class="song-actions">
                      <button class="play-song-btn" title="Reproducir"><i class="fas fa-play"></i></button>
                      <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="add-to-library-btn" title="Añadir a biblioteca"><i class="fas fa-heart"></i></button>
                        <button class="add-to-playlist-btn" title="Añadir a playlist"><i class="fas fa-plus"></i></button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>No hay canciones disponibles para este artista.</p>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <!-- Lista de artistas -->
    <h1>Artistas</h1>
    <div id="artist-list" class="card-list">
      <?php foreach ($artistas as $artista): ?>
        <a href="index.php?page=artistas&id=<?php echo $artista['id_artista']; ?>" class="card" tabindex="0" role="button" aria-pressed="false">
          <img src="<?php echo $artista['foto']; ?>" alt="Foto de <?php echo $artista['nombre']; ?>" onerror="this.src='assets/img/default-user.png';" />
          <div class="card-info">
            <h4><?php echo $artista['nombre']; ?></h4>
            <p><?php echo $artista['canciones']; ?> canciones</p>
          </div>
          <div class="play-icon"><i class="fas fa-user"></i></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Eventos para reproducir canciones
  const playButtons = document.querySelectorAll('.play-song-btn');
  playButtons.forEach(button => {
    button.addEventListener('click', function() {
      const songId = this.closest('tr').dataset.id;
      const songType = this.closest('tr').dataset.type;
      playSong(songId, songType);
    });
  });
  
  // Eventos para añadir a biblioteca
  const addToLibraryButtons = document.querySelectorAll('.add-to-library-btn');
  addToLibraryButtons.forEach(button => {
    button.addEventListener('click', function() {
      const songId = this.closest('tr').dataset.id;
      const songType = this.closest('tr').dataset.type;
      
      // Enviar solicitud para añadir a biblioteca
      fetch('api/user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_library&song_id=${songId}&type=${songType}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Canción añadida a tu biblioteca', 'success');
        } else {
          showToast(data.message || 'Error al añadir la canción', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al comunicarse con el servidor', 'error');
      });
    });
  });
  
  // Eventos para añadir a playlist
  const addToPlaylistButtons = document.querySelectorAll('.add-to-playlist-btn');
  addToPlaylistButtons.forEach(button => {
    button.addEventListener('click', function() {
      const songId = this.closest('tr').dataset.id;
      const songType = this.closest('tr').dataset.type;
      
      // Mostrar modal para seleccionar playlist
      showAddToPlaylistModal(songId, songType);
    });
  });
  
  // Hacer que las filas de la tabla sean clickeables para reproducir
  const songRows = document.querySelectorAll('.songs-table tbody tr');
  songRows.forEach(row => {
    row.addEventListener('click', function(e) {
      // Solo reproducir si no se hizo clic en un botón
      if (!e.target.closest('button')) {
        const songId = this.dataset.id;
        const songType = this.dataset.type;
        playSong(songId, songType);
      }
    });
  });
  
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
  
  // Función para mostrar modal de añadir a playlist
  function showAddToPlaylistModal(songId, songType) {
    // Obtener playlists del usuario
    fetch('api/playlist.php?action=get_playlists')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Crear modal
          const modal = document.createElement('div');
          modal.className = 'modal';
          modal.innerHTML = `
            <div class="modal-content">
              <div class="modal-header">
                <h2>Añadir a playlist</h2>
                <button class="close-modal">&times;</button>
              </div>
              <div class="modal-body">
                ${data.playlists.length > 0 ? `
                  <ul class="playlist-list">
                    ${data.playlists.map(playlist => `
                      <li data-id="${playlist.id_playlist}">
                        <img src="${playlist.imagen_playlist || 'assets/img/default-playlist.png'}" alt="${playlist.nombre_playlist}">
                        <span>${playlist.nombre_playlist}</span>
                      </li>
                    `).join('')}
                  </ul>
                ` : `
                  <div class="empty-state">
                    <p>No tienes playlists creadas.</p>
                    <a href="index.php?page=playlist" class="btn-primary">Crear playlist</a>
                  </div>
                `}
              </div>
            </div>
          `;
          
          document.body.appendChild(modal);
          
          // Mostrar modal
          setTimeout(() => {
            modal.classList.add('active');
          }, 10);
          
          // Cerrar modal
          const closeBtn = modal.querySelector('.close-modal');
          closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            setTimeout(() => {
              modal.remove();
            }, 300);
          });
          
          // Añadir a playlist
          const playlistItems = modal.querySelectorAll('.playlist-list li');
          playlistItems.forEach(item => {
            item.addEventListener('click', () => {
              const playlistId = item.dataset.id;
              
              fetch('api/playlist.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_song&playlist_id=${playlistId}&song_id=${songId}&type=${songType}`
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  showToast('Canción añadida a la playlist', 'success');
                } else {
                  showToast(data.message || 'Error al añadir la canción', 'error');
                }
                
                // Cerrar modal
                modal.classList.remove('active');
                setTimeout(() => {
                  modal.remove();
                }, 300);
              })
              .catch(error => {
                console.error('Error:', error);
                showToast('Error al comunicarse con el servidor', 'error');
              });
            });
          });
        } else {
          showToast(data.message || 'Error al obtener playlists', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al comunicarse con el servidor', 'error');
      });
  }
});
</script>
