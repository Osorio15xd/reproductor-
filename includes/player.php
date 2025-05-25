<?php
// Obtener la canción actual si existe
$current_song = null;
if (isset($_SESSION['current_song_id'])) {
    $stmt = $conn->prepare("SELECT c.id, c.titulo, c.portada, c.archivo_audio, a.nombre as artista 
                           FROM canciones c 
                           JOIN artistas a ON c.artista_id = a.id 
                           WHERE c.id = ?");
    $stmt->execute([$_SESSION['current_song_id']]);
    $current_song = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- Reproductor flotante -->
<div id="floating-player" class="<?php echo $current_song ? 'active' : ''; ?>">
  <div id="floating-player-info">
    <img src="<?php echo $current_song ? $current_song['portada'] : 'assets/img/placeholder.svg'; ?>" alt="Portada canción" id="floating-player-cover" />
    <div id="floating-track-name"><?php echo $current_song ? $current_song['titulo'] . ' - ' . $current_song['artista'] : 'No hay canción seleccionada'; ?></div>
  </div>
  <div id="floating-player-controls">
    <button id="floating-prev-btn" aria-label="Canción anterior" class="tooltip" data-tooltip="Anterior"><i class="fas fa-step-backward"></i></button>
    <button id="floating-play-pause-btn" aria-label="Reproducir / Pausar" class="tooltip" data-tooltip="Reproducir"><i class="fas fa-play"></i></button>
    <button id="floating-next-btn" aria-label="Siguiente canción" class="tooltip" data-tooltip="Siguiente"><i class="fas fa-step-forward"></i></button>
  </div>
  
  <div id="floating-player-progress">
    <div id="floating-player-progress-bar"></div>
  </div>
  
  <?php if ($current_song): ?>
  <audio id="audio-player" src="<?php echo $current_song['archivo_audio']; ?>" preload="metadata" data-id="<?php echo $current_song['id']; ?>"></audio>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar controles del reproductor flotante
  const floatingPlayPauseBtn = document.getElementById('floating-play-pause-btn');
  const floatingPrevBtn = document.getElementById('floating-prev-btn');
  const floatingNextBtn = document.getElementById('floating-next-btn');
  
  if (floatingPlayPauseBtn) {
    floatingPlayPauseBtn.addEventListener('click', function() {
      togglePlay();
    });
  }
  
  if (floatingPrevBtn) {
    floatingPrevBtn.addEventListener('click', function() {
      playPrevious();
    });
  }
  
  if (floatingNextBtn) {
    floatingNextBtn.addEventListener('click', function() {
      playNext();
    });
  }
  
  // Actualizar el botón de reproducción/pausa según el estado del audio
  const audioPlayer = document.getElementById('audio-player');
  if (audioPlayer) {
    if (audioPlayer.paused) {
      if (floatingPlayPauseBtn) {
        floatingPlayPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
      }
    } else {
      if (floatingPlayPauseBtn) {
        floatingPlayPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
      }
    }
  }
});
</script>