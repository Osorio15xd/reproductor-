<?php
// Verificar si el usuario está logueado
$is_logged_in = isset($_SESSION['user_id']);

// Obtener géneros musicales para filtrar
$stmt = $conn->query("SELECT id, nombre FROM generos ORDER BY nombre");
$generos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener artistas para filtrar
$stmt = $conn->query("SELECT id, nombre FROM artistas ORDER BY nombre");
$artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener canciones destacadas
$stmt = $conn->query("
    SELECT c.id, c.titulo, c.portada, c.precio, a.nombre as artista, g.nombre as genero
    FROM canciones c
    JOIN artistas a ON c.artista_id = a.id
    JOIN generos g ON c.genero_id = g.id
    ORDER BY RAND()
    LIMIT 6
");
$featured_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener nuevos lanzamientos
$stmt = $conn->query("
    SELECT c.id, c.titulo, c.portada, c.precio, a.nombre as artista, g.nombre as genero
    FROM canciones c
    JOIN artistas a ON c.artista_id = a.id
    JOIN generos g ON c.genero_id = g.id
    ORDER BY c.fecha_subida DESC
    LIMIT 8
");
$new_releases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar búsqueda y filtros
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? intval($_GET['genre']) : 0;
$artist_filter = isset($_GET['artist']) ? intval($_GET['artist']) : 0;
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$filtered_songs = [];
if (!empty($search_query) || $genre_filter > 0 || $artist_filter > 0 || !empty($price_filter) || isset($_GET['filter'])) {
    // Construir consulta SQL con filtros
    $sql = "
        SELECT c.id, c.titulo, c.portada, c.precio, a.nombre as artista, g.nombre as genero
        FROM canciones c
        JOIN artistas a ON c.artista_id = a.id
        JOIN generos g ON c.genero_id = g.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($search_query)) {
        $sql .= " AND (c.titulo LIKE ? OR a.nombre LIKE ?)";
        $params[] = "%{$search_query}%";
        $params[] = "%{$search_query}%";
    }
    
    if ($genre_filter > 0) {
        $sql .= " AND c.genero_id = ?";
        $params[] = $genre_filter;
    }
    
    if ($artist_filter > 0) {
        $sql .= " AND c.artista_id = ?";
        $params[] = $artist_filter;
    }
    
    if (!empty($price_filter)) {
        if ($price_filter === 'free') {
            $sql .= " AND c.precio = 0";
        } else if ($price_filter === 'under5') {
            $sql .= " AND c.precio < 5";
        } else if ($price_filter === 'under10') {
            $sql .= " AND c.precio < 10";
        } else if ($price_filter === 'over10') {
            $sql .= " AND c.precio >= 10";
        }
    }
    
    // Ordenar resultados
    if ($sort_by === 'price_asc') {
        $sql .= " ORDER BY c.precio ASC";
    } else if ($sort_by === 'price_desc') {
        $sql .= " ORDER BY c.precio DESC";
    } else if ($sort_by === 'name_asc') {
        $sql .= " ORDER BY c.titulo ASC";
    } else if ($sort_by === 'name_desc') {
        $sql .= " ORDER BY c.titulo DESC";
    } else {
        $sql .= " ORDER BY c.fecha_subida DESC";
    }
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $i => $param) {
        $stmt->bindValue($i + 1, $param);
    }
    $stmt->execute();
    $filtered_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<section id="page-music-store" class="page active" aria-label="Tienda de Música">
  <h1>Tienda de Música</h1>
  <p>Descubre y compra música de tus artistas favoritos</p>
  
  <div class="store-container">
    <div class="store-sidebar">
      <div class="search-filters">
        <h3>Filtros</h3>
        <form id="filter-form" action="" method="GET">
          <input type="hidden" name="page" value="music-store">
          <input type="hidden" name="filter" value="1">
          
          <div class="filter-group">
            <label for="search-input">Buscar</label>
            <input type="text" id="search-input" name="search" placeholder="Título o artista..." value="<?php echo htmlspecialchars($search_query); ?>">
          </div>
          
          <div class="filter-group">
            <label for="genre-filter">Género</label>
            <select id="genre-filter" name="genre">
              <option value="0">Todos los géneros</option>
              <?php foreach ($generos as $genero): ?>
                <option value="<?php echo $genero['id']; ?>" <?php echo $genre_filter == $genero['id'] ? 'selected' : ''; ?>><?php echo $genero['nombre']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="artist-filter">Artista</label>
            <select id="artist-filter" name="artist">
              <option value="0">Todos los artistas</option>
              <?php foreach ($artistas as $artista): ?>
                <option value="<?php echo $artista['id']; ?>" <?php echo $artist_filter == $artista['id'] ? 'selected' : ''; ?>><?php echo $artista['nombre']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="price-filter">Precio</label>
            <select id="price-filter" name="price">
              <option value="" <?php echo $price_filter === '' ? 'selected' : ''; ?>>Cualquier precio</option>
              <option value="free" <?php echo $price_filter === 'free' ? 'selected' : ''; ?>>Gratis</option>
              <option value="under5" <?php echo $price_filter === 'under5' ? 'selected' : ''; ?>>Menos de $5</option>
              <option value="under10" <?php echo $price_filter === 'under10' ? 'selected' : ''; ?>>Menos de $10</option>
              <option value="over10" <?php echo $price_filter === 'over10' ? 'selected' : ''; ?>>$10 o más</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="sort-by">Ordenar por</label>
            <select id="sort-by" name="sort">
              <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Más recientes</option>
              <option value="price_asc" <?php echo $sort_by === 'price_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
              <option value="price_desc" <?php echo $sort_by === 'price_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
              <option value="name_asc" <?php echo $sort_by === 'name_asc' ? 'selected' : ''; ?>>Nombre: A-Z</option>
              <option value="name_desc" <?php echo $sort_by === 'name_desc' ? 'selected' : ''; ?>>Nombre: Z-A</option>
            </select>
          </div>
          
          <button type="submit" class="btn-primary filter-btn">
            <i class="fas fa-filter"></i> Aplicar Filtros
          </button>
          
          <button type="button" id="clear-filters" class="btn-secondary">
            <i class="fas fa-times"></i> Limpiar Filtros
          </button>
        </form>
      </div>
      
      <div class="store-categories">
        <h3>Categorías</h3>
        <ul>
          <li><a href="#featured-songs">Canciones Destacadas</a></li>
          <li><a href="#new-releases">Nuevos Lanzamientos</a></li>
        </ul>
      </div>
      
      <div class="store-cart-summary">
        <h3>Carrito</h3>
        <div id="cart-summary">
          <p>0 items en el carrito</p>
          <p>Total: $0.00</p>
        </div>
        <a href="index.php?page=carrito" class="btn-primary view-cart-btn">
          <i class="fas fa-shopping-cart"></i> Ver Carrito
        </a>
      </div>
    </div>
    
    <div class="store-content">
      <?php if (!empty($search_query) || $genre_filter > 0 || $artist_filter > 0 || !empty($price_filter) || isset($_GET['filter'])): ?>
        <div class="search-results">
          <h2>Resultados de búsqueda</h2>
          <?php if (count($filtered_songs) > 0): ?>
            <p><?php echo count($filtered_songs); ?> resultados encontrados</p>
            <div class="song-grid">
              <?php foreach ($filtered_songs as $song): ?>
                <div class="song-card">
                  <div class="song-cover">
                    <img src="<?php echo file_exists($song['portada']) ? $song['portada'] : 'assets/img/placeholder.svg'; ?>" alt="<?php echo $song['titulo']; ?>">
                    <div class="song-actions">
                      <button class="preview-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-play"></i></button>
                      <button class="add-to-cart-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-shopping-cart"></i></button>
                    </div>
                  </div>
                  <div class="song-info">
                    <h3 class="song-title"><?php echo $song['titulo']; ?></h3>
                    <p class="song-artist"><?php echo $song['artista']; ?></p>
                    <p class="song-genre"><?php echo $song['genero']; ?></p>
                    <div class="song-price">
                      <span class="regular-price">$<?php echo number_format($song['precio'], 2); ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p>No se encontraron resultados para tu búsqueda.</p>
              <button id="clear-search" class="btn-primary">Limpiar búsqueda</button>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div id="featured-songs" class="store-section">
          <h2>Canciones Destacadas</h2>
          <div class="song-grid">
            <?php foreach ($featured_songs as $song): ?>
              <div class="song-card">
                <div class="song-cover">
                  <img src="<?php echo file_exists($song['portada']) ? $song['portada'] : 'assets/img/placeholder.svg'; ?>" alt="<?php echo $song['titulo']; ?>">
                  <div class="song-actions">
                    <button class="preview-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-play"></i></button>
                    <button class="add-to-cart-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-shopping-cart"></i></button>
                  </div>
                </div>
                <div class="song-info">
                  <h3 class="song-title"><?php echo $song['titulo']; ?></h3>
                  <p class="song-artist"><?php echo $song['artista']; ?></p>
                  <p class="song-genre"><?php echo $song['genero']; ?></p>
                  <div class="song-price">
                    <span class="regular-price">$<?php echo number_format($song['precio'], 2); ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        
        <div id="new-releases" class="store-section">
          <h2>Nuevos Lanzamientos</h2>
          <div class="song-grid">
            <?php foreach ($new_releases as $song): ?>
              <div class="song-card">
                <div class="song-cover">
                  <img src="<?php echo file_exists($song['portada']) ? $song['portada'] : 'assets/img/placeholder.svg'; ?>" alt="<?php echo $song['titulo']; ?>">
                  <div class="song-actions">
                    <button class="preview-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-play"></i></button>
                    <button class="add-to-cart-btn" data-id="<?php echo $song['id']; ?>"><i class="fas fa-shopping-cart"></i></button>
                  </div>
                </div>
                <div class="song-info">
                  <h3 class="song-title"><?php echo $song['titulo']; ?></h3>
                  <p class="song-artist"><?php echo $song['artista']; ?></p>
                  <p class="song-genre"><?php echo $song['genero']; ?></p>
                  <div class="song-price">
                    <span class="regular-price">$<?php echo number_format($song['precio'], 2); ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Preview Modal -->
  <div id="preview-modal" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">Previsualización</h3>
        <button class="modal-close"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <div class="preview-content">
          <div class="preview-cover">
            <img id="preview-cover-img" src="assets/img/placeholder.svg" alt="Portada">
          </div>
          <div class="preview-info">
            <h3 id="preview-title">Título de la canción</h3>
            <p id="preview-artist">Artista</p>
            <p id="preview-genre">Género</p>
            <div class="preview-player">
              <audio id="preview-audio" controls></audio>
            </div>
            <div class="preview-price">
              <span id="preview-price-display">$0.00</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" id="close-preview">Cerrar</button>
        <button class="btn-primary" id="add-to-cart-preview"><i class="fas fa-shopping-cart"></i> Añadir al Carrito</button>
      </div>
    </div>
  </div>
</section>

<style>
.store-container {
  display: flex;
  gap: 30px;
  margin-top: 20px;
}

.store-sidebar {
  width: 300px;
  flex-shrink: 0;
}

.store-content {
  flex: 1;
}

.search-filters, .store-categories, .store-cart-summary {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  border: 1px solid var(--border-color);
}

.search-filters h3, .store-categories h3, .store-cart-summary h3 {
  margin-top: 0;
  margin-bottom: 15px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
}

.filter-group {
  margin-bottom: 15px;
}

.filter-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}

.filter-group input, .filter-group select {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-color);
}

.filter-btn {
  width: 100%;
  margin-bottom: 10px;
}

.store-categories ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.store-categories li {
  margin-bottom: 10px;
}

.store-categories a {
  display: block;
  padding: 10px;
  border-radius: 8px;
  background: var(--bg-card);
  color: var(--text-color);
  text-decoration: none;
  transition: all 0.3s ease;
}

.store-categories a:hover {
  background: var(--primary-color);
  color: var(--bg-color);
  transform: translateY(-3px);
}

.store-cart-summary {
  text-align: center;
}

#cart-summary {
  margin-bottom: 15px;
}

.view-cart-btn {
  width: 100%;
}

.store-section {
  margin-bottom: 40px;
}

.store-section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
}

.song-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.song-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.song-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

.song-cover {
  position: relative;
  width: 100%;
  padding-top: 100%; /* 1:1 Aspect Ratio */
  overflow: hidden;
}

.song-cover img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.song-actions {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.song-cover:hover .song-actions {
  opacity: 1;
}

.preview-btn, .add-to-cart-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-color);
  color: var(--bg-color);
  border: none;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.preview-btn:hover, .add-to-cart-btn:hover {
  transform: scale(1.1);
  background: var(--primary-hover);
}

.song-info {
  padding: 15px;
}

.song-title {
  margin: 0 0 5px 0;
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.song-artist, .song-genre {
  margin: 0 0 5px 0;
  font-size: 0.9rem;
  color: var(--text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.song-price {
  display: flex;
  align-items: center;
  gap: 10px;
}

.regular-price {
  font-weight: 700;
  color: var(--primary-color);
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.search-results {
  margin-bottom: 40px;
}

.search-results h2 {
  margin-top: 0;
  margin-bottom: 10px;
}

.search-results p {
  margin-top: 0;
  margin-bottom: 20px;
  color: var(--text-secondary);
}

/* Preview Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.modal-container {
  background: var(--bg-color);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid var(--border-color);
}

.modal-title {
  margin: 0;
  font-size: 1.2rem;
}

.modal-close {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-close:hover {
  color: var(--primary-color);
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.preview-content {
  display: flex;
  gap: 20px;
}

.preview-cover {
  width: 200px;
  height: 200px;
  border-radius: 10px;
  overflow: hidden;
}

.preview-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-info {
  flex: 1;
}

.preview-info h3 {
  margin-top: 0;
  margin-bottom: 10px;
}

.preview-info p {
  margin: 0 0 10px 0;
  color: var(--text-secondary);
}

.preview-player {
  margin: 20px 0;
}

.preview-player audio {
  width: 100%;
}

.preview-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
  margin-top: 20px;
}

@media (max-width: 768px) {
  .store-container {
    flex-direction: column;
  }
  
  .store-sidebar {
    width: 100%;
  }
  
  .preview-content {
    flex-direction: column;
    align-items: center;
  }
  
  .preview-info {
    width: 100%;
    text-align: center;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Variables globales
  let cartItems = [];
  let cartTotal = 0;
  let previewSongId = null;
  
  // Elementos DOM
  const clearFiltersBtn = document.getElementById('clear-filters');
  const clearSearchBtn = document.getElementById('clear-search');
  const previewBtns = document.querySelectorAll('.preview-btn');
  const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
  const previewModal = document.getElementById('preview-modal');
  const closePreviewBtn = document.getElementById('close-preview');
  const addToCartPreviewBtn = document.getElementById('add-to-cart-preview');
  const previewAudio = document.getElementById('preview-audio');
  const cartSummary = document.getElementById('cart-summary');
  
  // Inicializar
  initStore();
  
  // Función para inicializar la tienda
  function initStore() {
    // Cargar carrito desde localStorage
    loadCart();
    
    // Actualizar resumen del carrito
    updateCartSummary();
    
    // Configurar eventos
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', clearFilters);
    }
    
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', clearSearch);
    }
    
    previewBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const songId = this.dataset.id;
        previewSong(songId);
      });
    });
    
    addToCartBtns.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const songId = this.dataset.id;
        addToCart(songId);
      });
    });
    
    if (closePreviewBtn) {
      closePreviewBtn.addEventListener('click', closePreview);
    }
    
    if (previewModal) {
      previewModal.querySelector('.modal-close').addEventListener('click', closePreview);
    }
    
    if (addToCartPreviewBtn) {
      addToCartPreviewBtn.addEventListener('click', function() {
        if (previewSongId) {
          addToCart(previewSongId);
          closePreview();
        }
      });
    }
    
    // Detener reproducción al cerrar el modal
    if (previewModal) {
      previewModal.addEventListener('click', function(e) {
        if (e.target === previewModal) {
          closePreview();
        }
      });
    }
  }
  
  // Función para limpiar filtros
  function clearFilters() {
    window.location.href = 'index.php?page=music-store';
  }
  
  // Función para limpiar búsqueda
  function clearSearch() {
    window.location.href = 'index.php?page=music-store';
  }
  
  // Función para previsualizar una canción
  function previewSong(songId) {
    // Obtener información de la canción mediante AJAX
    fetch(`api/music.php?action=get_song&id=${songId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const song = data.song;
          
          // Actualizar modal con información de la canción
          document.getElementById('preview-title').textContent = song.titulo;
          document.getElementById('preview-artist').textContent = song.artista;
          document.getElementById('preview-genre').textContent = song.genero;
          document.getElementById('preview-cover-img').src = song.portada;
          
          // Actualizar precio
          document.getElementById('preview-price-display').innerHTML = `
            <span class="regular-price">$${parseFloat(song.precio).toFixed(2)}</span>
          `;
          
          // Configurar audio
          previewAudio.src = song.archivo_audio;
          previewAudio.play().catch(error => {
            console.error('Error al reproducir:', error);
          });
          
          // Guardar ID de la canción para añadir al carrito
          previewSongId = songId;
          
          // Mostrar modal
          previewModal.classList.add('active');
        } else {
          showToast(data.message || 'Error al obtener información de la canción', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al comunicarse con el servidor', 'error');
      });
  }
  
  // Función para cerrar la previsualización
  function closePreview() {
    previewModal.classList.remove('active');
    previewAudio.pause();
    previewAudio.src = '';
    previewSongId = null;
  }
  
  // Función para añadir al carrito
  function addToCart(songId) {
    // Enviar solicitud AJAX para añadir al carrito
    fetch('api/cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `action=add_to_cart&cancion_id=${songId}`,
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Canción añadida al carrito', 'success');
          
          // Actualizar carrito local
          loadCart();
        } else {
          showToast(data.message || 'Error al añadir la canción al carrito', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al comunicarse con el servidor', 'error');
      });
  }
  
  // Función para cargar el carrito
  function loadCart() {
    fetch('api/cart.php?action=get_cart')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          cartItems = data.items || [];
          cartTotal = data.total || 0;
          updateCartSummary();
        }
      })
      .catch(error => {
        console.error('Error al cargar el carrito:', error);
      });
  }
  
  // Función para actualizar el resumen del carrito
  function updateCartSummary() {
    if (cartSummary) {
      cartSummary.innerHTML = `
        <p>${cartItems.length} item${cartItems.length !== 1 ? 's' : ''} en el carrito</p>
        <p>Total: $${parseFloat(cartTotal).toFixed(2)}</p>
      `;
    }
  }
  
  // Función para mostrar un mensaje toast
  function showToast(message, type = 'info') {
    // Crear el elemento toast
    const toast = document.createElement('div');
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%) translateY(-100px)';
    toast.style.padding = '10px 20px';
    toast.style.borderRadius = '5px';
    toast.style.boxShadow = '0 3px 10px rgba(0,0,0,0.3)';
    toast.style.zIndex = '1000';
    toast.style.transition = 'all 0.3s ease';
    
    // Establecer colores según el tipo
    if (type === 'error') {
      toast.style.background = '#e74c3c';
      toast.style.color = '#fff';
      toast.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    } else if (type === 'success') {
      toast.style.background = 'var(--primary-color)';
      toast.style.color = 'var(--bg-color)';
      toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    } else {
      toast.style.background = '#3498db';
      toast.style.color = '#fff';
      toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
    }
    
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(0)';
    }, 10);
    
    // Eliminar después de 3 segundos
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(-100px)';
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }
});
</script>
