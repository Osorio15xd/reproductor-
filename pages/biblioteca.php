<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Obtener la biblioteca del usuario
try {
    // Obtener canciones de álbumes en la biblioteca
    $stmt = $conn->prepare("
        SELECT 
            c.id_cancion as id, 
            c.nombre_cancion as titulo, 
            a.imagen_album_path as portada, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            'album_song' as tipo,
            b.fecha_agregado
        FROM biblioteca_usuario b
        JOIN canciones c ON b.id_cancion = c.id_cancion
        JOIN album a ON c.id_album = a.id_album
        JOIN artista art ON c.id_artista = art.id_artista
        JOIN usuario u ON art.usuario = u.id_usuario
        JOIN genero g ON a.id_genero = g.id_genero
        WHERE b.id_usuario = ? AND b.tipo = 'album_song'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $canciones_album = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener sencillos en la biblioteca
    $stmt = $conn->prepare("
        SELECT 
            s.id_sencillo as id, 
            s.nombre_sencillo as titulo, 
            s.imagen_sencillo_path as portada, 
            u.nombre_usuario as artista, 
            g.nombre_genero as genero,
            'sencillo' as tipo,
            b.fecha_agregado
        FROM biblioteca_usuario b
        JOIN sencillos s ON b.id_sencillo = s.id_sencillo
        JOIN artista a ON s.id_artista = a.id_artista
        JOIN usuario u ON a.usuario = u.id_usuario
        JOIN genero g ON s.id_genero = g.id_genero
        WHERE b.id_usuario = ? AND b.tipo = 'sencillo'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $sencillos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combinar resultados y ordenar por fecha de agregado
    $biblioteca = array_merge($canciones_album, $sencillos);
    usort($biblioteca, function($a, $b) {
        return strtotime($b['fecha_agregado']) - strtotime($a['fecha_agregado']);
    });
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar biblioteca: " . $e->getMessage() . "</div>";
    $biblioteca = [];
}
?>

<section id="page-biblioteca" class="page active" aria-label="Biblioteca">
  <h1>Biblioteca</h1>
  <p>Tu biblioteca personal de música guardada.</p>
  
  <?php if (count($biblioteca) > 0): ?>
    <div id="library-list" class="card-list">
      <?php foreach ($biblioteca as $cancion): ?>
        <div class="card" tabindex="0" role="button" aria-pressed="false" data-id="<?php echo $cancion['id']; ?>" data-type="<?php echo $cancion['tipo']; ?>">
          <img src="<?php echo $cancion['portada']; ?>" alt="Portada de <?php echo $cancion['titulo']; ?>" onerror="this.src='assets/img/default-cover.png';" />
          <div class="card-info">
            <h4><?php echo $cancion['titulo']; ?></h4>
            <p><?php echo $cancion['artista']; ?></p>
          </div>
          <div class="play-icon"><i class="fas fa-play"></i></div>
          <button class="remove-from-library" data-id="<?php echo $cancion['id']; ?>" data-type="<?php echo $cancion['tipo']; ?>" title="Eliminar de la biblioteca">
            <i class="fas fa-times"></i>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-music" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
      <h3>Tu biblioteca está vacía</h3>
      <p>Explora nuestra colección y agrega canciones a tu biblioteca.</p>
      <a href="index.php?page=inicio" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Explorar música</a>
    </div>
  <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Agregar evento para reproducir canciones
  const songCards = document.querySelectorAll('.card');
  songCards.forEach(card => {
    card.addEventListener('click', function(e) {
      // No reproducir si se hizo clic en el botón de eliminar
      if (!e.target.closest('.remove-from-library')) {
        const songId = this.dataset.id;
        const songType = this.dataset.type;
        playSong(songId, songType);
      }
    });
  });
  
  // Agregar evento para eliminar canciones de la biblioteca
  const removeButtons = document.querySelectorAll('.remove-from-library');
  removeButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.stopPropagation(); // Evitar que se reproduzca la canción
      
      const songId = this.dataset.id;
      const songType = this.dataset.type;
      
      if (confirm('¿Estás seguro de que deseas eliminar esta canción de tu biblioteca?')) {
        // Enviar solicitud para eliminar de la biblioteca
        fetch('api/user.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=remove_from_library&song_id=${songId}&type=${songType}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Eliminar la tarjeta de la interfaz
            this.closest('.card').remove();
            showToast('Canción eliminada de tu biblioteca', 'success');
            
            // Si no quedan canciones, mostrar estado vacío
            if (document.querySelectorAll('.card').length === 0) {
              document.getElementById('library-list').innerHTML = `
                <div class="empty-state">
                  <i class="fas fa-music" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                  <h3>Tu biblioteca está vacía</h3>
                  <p>Explora nuestra colección y agrega canciones a tu biblioteca.</p>
                  <a href="index.php?page=inicio" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Explorar música</a>
                </div>
              `;
            }
          } else {
            showToast(data.message || 'Error al eliminar la canción', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error al comunicarse con el servidor', 'error');
        });
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
});
</script>
