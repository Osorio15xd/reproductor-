<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php?page=login');
  exit;
}

// Obtener información del usuario
$stmt = $conn->prepare("SELECT id, nombre, username, email, foto, bio FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estadísticas del usuario
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM biblioteca_usuario WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_biblioteca = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM playlists WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_playlists = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM estadisticas_reproduccion WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_reproducciones = $stmt->fetchColumn();

// Obtener suscripción del usuario
$stmt = $conn->prepare("SELECT id, plan, fecha_inicio, fecha_fin, activo FROM suscripciones WHERE usuario_id = ? AND activo = 1 ORDER BY fecha_fin DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$suscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar actualización de perfil
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
  $nombre = trim($_POST['nombre']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $bio = trim($_POST['bio']);

  if (empty($nombre) || empty($username) || empty($email)) {
    $mensaje = '<div class="alert alert-danger">Por favor, completa todos los campos obligatorios.</div>';
  } else {
    // Verificar si el email ya existe (excepto para el usuario actual)
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
      $mensaje = '<div class="alert alert-danger">Este correo electrónico ya está registrado por otro usuario.</div>';
    } else {
      // Actualizar perfil
      $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, username = ?, email = ?, bio = ? WHERE id = ?");
      if ($stmt->execute([$nombre, $username, $email, $bio, $_SESSION['user_id']])) {
        // Actualizar datos de sesión
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;

        $mensaje = '<div class="alert alert-success">Perfil actualizado correctamente.</div>';

        // Recargar información del usuario
        $stmt = $conn->prepare("SELECT id, nombre, username, email, foto, bio FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
        $mensaje = '<div class="alert alert-danger">Error al actualizar el perfil. Inténtalo de nuevo.</div>';
      }
    }
  }
}

// Obtener canciones recientes
$stmt = $conn->prepare("
    SELECT c.id, c.titulo, c.portada, a.nombre as artista, er.fecha
    FROM estadisticas_reproduccion er
    JOIN canciones c ON er.cancion_id = c.id
    JOIN artistas a ON c.artista_id = a.id
    WHERE er.usuario_id = ?
    ORDER BY er.fecha DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener playlists del usuario
$stmt = $conn->prepare("
    SELECT p.id, p.nombre, COUNT(pc.cancion_id) as canciones
    FROM playlists p
    LEFT JOIN playlist_canciones pc ON p.id = pc.playlist_id
    WHERE p.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.fecha_creacion DESC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$user_playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section id="page-perfil" class="page active" aria-label="Perfil">
  <div class="profile-header">
    <div class="profile-cover"></div>
    <div class="profile-avatar-container">
      <img id="profile-photo" src="<?php echo !empty($usuario['foto']) ? $usuario['foto'] : 'assets/img/placeholder.svg'; ?>" alt="Foto de perfil" />
      <div class="change-photo-btn" id="change-photo-trigger">
        <i class="fas fa-camera"></i>
      </div>
    </div>
    <div class="profile-info">
      <h1 id="profile-name"><?php echo $usuario['nombre']; ?></h1>
      <p id="profile-username">@<?php echo $usuario['username']; ?></p>

      <?php if ($suscripcion): ?>
        <div class="subscription-badge" id="subscription-status">
          Plan <?php echo ucfirst($suscripcion['plan']); ?>
        </div>
      <?php else: ?>
        <div class="subscription-badge" id="subscription-status" style="background: var(--bg-card); color: var(--text-color);">
          Sin suscripción
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="profile-stats">
    <div class="stat-item">
      <div class="stat-value" id="stat-songs"><?php echo $total_biblioteca; ?></div>
      <div class="stat-label">Canciones</div>
    </div>
    <div class="stat-item">
      <div class="stat-value" id="stat-playlists"><?php echo $total_playlists; ?></div>
      <div class="stat-label">Playlists</div>
    </div>
    <div class="stat-item">
      <div class="stat-value" id="stat-plays"><?php echo $total_reproducciones; ?></div>
      <div class="stat-label">Reproducciones</div>
    </div>
  </div>

  <div class="profile-actions">
    <a href="index.php?page=upload" class="profile-action-btn">
      <i class="fas fa-upload"></i> Subir Música
    </a>
    <a href="index.php?page=estadisticas" class="profile-action-btn">
      <i class="fas fa-chart-bar"></i> Ver Estadísticas
    </a>
    <a href="index.php?page=configuraciones" class="profile-action-btn">
      <i class="fas fa-cog"></i> Configuración
    </a>
  </div>

  <?php echo $mensaje; ?>

  <div class="profile-content">
    <div class="profile-tabs">
      <div class="profile-tab active" data-tab="overview">Resumen</div>
      <div class="profile-tab" data-tab="info">Información Personal</div>
      <div class="profile-tab" data-tab="subscription">Suscripción</div>
    </div>

    <div class="profile-tab-content active" id="tab-overview">
      <div class="overview-grid">
        <div class="overview-section">
          <h3>Escuchado Recientemente</h3>
          <?php if (count($recent_songs) > 0): ?>
            <div class="recent-songs">
              <?php foreach ($recent_songs as $song): ?>
                <div class="recent-song-item" data-id="<?php echo $song['id']; ?>">
                  <img src="<?php echo $song['portada']; ?>" alt="<?php echo $song['titulo']; ?>" class="recent-song-thumbnail">
                  <div class="recent-song-details">
                    <div class="recent-song-title"><?php echo $song['titulo']; ?></div>
                    <div class="recent-song-artist"><?php echo $song['artista']; ?></div>
                    <div class="recent-song-time"><?php echo date('d/m/Y H:i', strtotime($song['fecha'])); ?></div>
                  </div>
                  <button class="recent-song-play" title="Reproducir"><i class="fas fa-play"></i></button>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p>Aún no has escuchado ninguna canción.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="overview-section">
          <h3>Mis Playlists</h3>
          <?php if (count($user_playlists) > 0): ?>
            <div class="user-playlists">
              <?php foreach ($user_playlists as $playlist): ?>
                <a href="index.php?page=playlist&id=<?php echo $playlist['id']; ?>" class="user-playlist-item">
                  <div class="user-playlist-icon"><i class="fas fa-list"></i></div>
                  <div class="user-playlist-details">
                    <div class="user-playlist-name"><?php echo $playlist['nombre']; ?></div>
                    <div class="user-playlist-count"><?php echo $playlist['canciones']; ?> canciones</div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <a href="index.php?page=playlist" class="view-all-link">Ver todas mis playlists <i class="fas fa-arrow-right"></i></a>
          <?php else: ?>
            <div class="empty-state">
              <p>Aún no has creado ninguna playlist.</p>
              <a href="index.php?page=playlist" class="btn-primary">Crear Playlist</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="overview-section">
          <h3>Sobre Mí</h3>
          <?php if (!empty($usuario['bio'])): ?>
            <div class="user-bio">
              <?php echo $usuario['bio']; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <p>No has añadido una biografía.</p>
              <button class="btn-primary" id="add-bio-btn">Añadir Biografía</button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="profile-tab-content" id="tab-info">
      <form id="edit-profile-form" method="POST" action="">
        <div class="form-row">
          <div class="form-group">
            <label for="edit-name">Nombre Completo</label>
            <input type="text" id="edit-name" name="nombre" value="<?php echo $usuario['nombre']; ?>" required minlength="2" />
          </div>
          <div class="form-group">
            <label for="edit-username">Nombre de Usuario</label>
            <input type="text" id="edit-username" name="username" value="<?php echo $usuario['username']; ?>" required />
          </div>
        </div>

        <div class="form-group">
          <label for="edit-email">Correo Electrónico</label>
          <input type="email" id="edit-email" name="email" value="<?php echo $usuario['email']; ?>" required />
        </div>

        <div class="form-group">
          <label for="edit-bio">Biografía</label>
          <textarea id="edit-bio" name="bio" rows="4" style="width: 100%; background: var(--bg-card); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px;"><?php echo $usuario['bio']; ?></textarea>
        </div>

        <button type="submit" name="update_profile" class="btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
      </form>
    </div>

    <div class="profile-tab-content" id="tab-subscription">
      <?php if ($suscripcion): ?>
        <div class="subscription-card">
          <div class="subscription-header">
            <h3>Plan Actual</h3>
            <div class="subscription-status active">Activo</div>
          </div>
          <div class="subscription-details">
            <div class="subscription-plan">Plan <?php echo ucfirst($suscripcion['plan']); ?></div>
            <div class="subscription-price">
              <?php echo $suscripcion['plan'] == 'mensual' ? '$5.00 / mes' : '$50.00 / año'; ?>
            </div>
            <div class="subscription-dates">
              <div>Inicio: <?php echo date('d/m/Y', strtotime($suscripcion['fecha_inicio'])); ?></div>
              <div>Próxima renovación: <?php echo date('d/m/Y', strtotime($suscripcion['fecha_fin'])); ?></div>
            </div>
          </div>
          <div class="subscription-actions">
            <a href="index.php?page=subscription" class="btn-primary">Cambiar Plan</a>
            <button class="btn-secondary" id="cancel-subscription-btn">Cancelar Suscripción</button>
          </div>
        </div>

        <h3>Historial de Pagos</h3>
        <?php
        // Obtener historial de pagos (simulado para este ejemplo)
        $pagos = [];
        if ($suscripcion) {
          // Simular algunos pagos basados en la fecha de inicio
          $fecha_inicio = new DateTime($suscripcion['fecha_inicio']);
          $fecha_actual = new DateTime();
          $intervalo = $suscripcion['plan'] == 'mensual' ? 'P1M' : 'P1Y';
          $monto = $suscripcion['plan'] == 'mensual' ? 5.00 : 50.00;

          $fecha_pago = clone $fecha_inicio;
          while ($fecha_pago <= $fecha_actual) {
            $pagos[] = [
              'fecha' => $fecha_pago->format('d/m/Y'),
              'descripcion' => 'Plan ' . ucfirst($suscripcion['plan']) . ' - ' . $fecha_pago->format('F Y'),
              'monto' => $monto,
              'estado' => 'Pagado'
            ];
            $fecha_pago->add(new DateInterval($intervalo));
          }
        }
        ?>

        <?php if (count($pagos) > 0): ?>
          <div class="payment-history">
            <?php foreach ($pagos as $pago): ?>
              <div class="payment-item">
                <div class="payment-date"><?php echo $pago['fecha']; ?></div>
                <div class="payment-description"><?php echo $pago['descripcion']; ?></div>
                <div class="payment-amount">$<?php echo number_format($pago['monto'], 2); ?></div>
                <div class="payment-status"><?php echo $pago['estado']; ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <p>No hay historial de pagos disponible.</p>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="subscription-card">
          <div class="subscription-header">
            <h3>Sin suscripción activa</h3>
            <div class="subscription-status inactive">Inactivo</div>
          </div>
          <div class="subscription-details">
            <p>Actualmente no tienes ninguna suscripción activa. Suscríbete para disfrutar de todas las ventajas.</p>
            <ul class="subscription-benefits">
              <li><i class="fas fa-check"></i> Acceso a toda la música</li>
              <li><i class="fas fa-check"></i> Sin anuncios</li>
              <li><i class="fas fa-check"></i> Descarga de canciones</li>
              <li><i class="fas fa-check"></i> Calidad premium</li>
            </ul>
          </div>
          <div class="subscription-actions">
            <a href="index.php?page=subscription" class="btn-primary">Ver planes de suscripción</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<style>
  .profile-header {
    position: relative;
    margin-bottom: 80px;
  }

  .profile-cover {
    height: 200px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    border-radius: 12px;
    margin-bottom: 60px;
  }

  .profile-avatar-container {
    position: absolute;
    bottom: -50px;
    left: 30px;
  }

  #profile-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--bg-color);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  }

  .change-photo-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 30px;
    height: 30px;
    background: var(--primary-color);
    color: var(--bg-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
  }

  .change-photo-btn:hover {
    transform: scale(1.1);
  }

  .profile-info {
    position: absolute;
    bottom: -50px;
    left: 150px;
  }

  #profile-name {
    margin: 0;
    font-size: 1.8rem;
  }

  #profile-username {
    margin: 5px 0;
    color: var(--text-secondary);
  }

  .subscription-badge {
    display: inline-block;
    padding: 5px 10px;
    background: var(--primary-color);
    color: var(--bg-color);
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .profile-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
  }

  .stat-item {
    text-align: center;
  }

  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
  }

  .stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }

  .profile-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
  }

  .profile-action-btn {
    padding: 8px 15px;
    background: var(--bg-card);
    color: var(--text-color);
    border-radius: 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
  }

  .profile-action-btn:hover {
    background: var(--primary-color);
    color: var(--bg-color);
    transform: translateY(-3px);
    text-decoration: none;
  }

  .profile-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
  }

  .profile-tab {
    padding: 10px 0;
    cursor: pointer;
    position: relative;
    font-weight: 600;
  }

  .profile-tab::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary-color);
    transition: width 0.3s ease;
  }

  .profile-tab:hover::after {
    width: 100%;
  }

  .profile-tab.active {
    color: var(--primary-color);
  }

  .profile-tab.active::after {
    width: 100%;
  }

  .profile-tab-content {
    display: none;
  }

  .profile-tab-content.active {
    display: block;
    animation: fadeIn 0.5s ease;
  }

  .overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  .overview-section {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid var(--border-color);
  }

  .overview-section:first-child {
    grid-column: 1 / 3;
  }

  .overview-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
  }

  .recent-songs {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .recent-song-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: var(--bg-card);
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .recent-song-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  }

  .recent-song-thumbnail {
    width: 50px;
    height: 50px;
    border-radius: 5px;
    object-fit: cover;
    margin-right: 15px;
  }

  .recent-song-details {
    flex: 1;
  }

  .recent-song-title {
    font-weight: 600;
    margin-bottom: 3px;
  }

  .recent-song-artist {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 3px;
  }

  .recent-song-time {
    color: var(--text-secondary);
    font-size: 0.8rem;
  }

  .recent-song-play {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: var(--primary-color);
    color: var(--bg-color);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .recent-song-play:hover {
    transform: scale(1.1);
  }

  .user-playlists {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .user-playlist-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: var(--bg-card);
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: var(--text-color);
  }

  .user-playlist-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    text-decoration: none;
  }

  .user-playlist-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    color: var(--bg-color);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
  }

  .user-playlist-details {
    flex: 1;
  }

  .user-playlist-name {
    font-weight: 600;
    margin-bottom: 3px;
  }

  .user-playlist-count {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }

  .view-all-link {
    display: block;
    margin-top: 15px;
    text-align: right;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
  }

  .view-all-link:hover {
    text-decoration: underline;
  }

  .user-bio {
    background: var(--bg-card);
    padding: 15px;
    border-radius: 8px;
    line-height: 1.6;
  }

  .empty-state {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary);
  }

  .btn-primary {
    display: inline-block;
    background: var(--primary-color);
    color: var(--bg-color);
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    text-decoration: none;
  }

  .btn-secondary {
    display: inline-block;
    background: var(--bg-card);
    color: var(--text-color);
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-secondary:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
  }

  .subscription-card {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
  }

  .subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
  }

  .subscription-header h3 {
    margin: 0;
  }

  .subscription-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .subscription-status.active {
    background: var(--primary-color);
    color: var(--bg-color);
  }

  .subscription-status.inactive {
    background: var(--bg-card);
    color: var(--text-secondary);
  }

  .subscription-details {
    margin-bottom: 20px;
  }

  .subscription-plan {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .subscription-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 10px;
  }

  .subscription-dates {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }

  .subscription-actions {
    display: flex;
    gap: 15px;
  }

  .subscription-benefits {
    list-style: none;
    padding: 0;
    margin: 15px 0;
  }

  .subscription-benefits li {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
  }

  .subscription-benefits li i {
    color: var(--primary-color);
    margin-right: 10px;
  }

  .payment-history {
    background: var(--bg-secondary);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
  }

  .payment-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
  }

  .payment-item:last-child {
    border-bottom: none;
  }

  .payment-date {
    width: 100px;
  }

  .payment-description {
    flex: 1;
  }

  .payment-amount {
    width: 100px;
    text-align: right;
    font-weight: 600;
  }

  .payment-status {
    width: 80px;
    text-align: center;
    padding: 3px 8px;
    background: var(--primary-color);
    color: var(--bg-color);
    border-radius: 20px;
    font-size: 0.8rem;
  }

  .alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
  }

  .alert-success {
    background-color: rgba(46, 204, 113, 0.2);
    border: 1px solid #2ecc71;
    color: #2ecc71;
  }

  .alert-danger {
    background-color: rgba(231, 76, 60, 0.2);
    border: 1px solid #e74c3c;
    color: #e74c3c;
  }

  @media (max-width: 768px) {
    .profile-header {
      margin-bottom: 120px;
    }

    .profile-info {
      left: 20px;
      bottom: -100px;
    }

    .profile-avatar-container {
      left: 50%;
      transform: translateX(-50%);
    }

    .profile-stats {
      margin-top: 50px;
    }

    .overview-grid {
      grid-template-columns: 1fr;
    }

    .overview-section:first-child {
      grid-column: 1;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Change profile photo
  const changePhotoBtn = document.getElementById('change-photo-trigger');
  if (changePhotoBtn) {
    changePhotoBtn.addEventListener('click', function() {
      // Create a hidden file input
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      fileInput.style.display = 'none';
      document.body.appendChild(fileInput);

      fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
          const file = e.target.files[0];
          const formData = new FormData();
          formData.append('action', 'update_photo');
          formData.append('photo', file);

          // Send the photo to the server
          fetch('api/user.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Update the photo in the interface
                document.getElementById('profile-photo').src = data.photo_url;
                document.getElementById('user-photo').src = data.photo_url;
                showToast('Foto de perfil actualizada', 'success');
              } else {
                showToast(data.message || 'Error al actualizar la foto', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              showToast('Error al comunicarse con el servidor', 'error');
            });
        }
        document.body.removeChild(fileInput);
      });

      fileInput.click();
    });
  }

  // Switch between tabs
  const profileTabs = document.querySelectorAll('.profile-tab');
  profileTabs.forEach(tab => {
    tab.addEventListener('click', function() {
      // Deactivate all tabs
      profileTabs.forEach(t => t.classList.remove('active'));
      // Activate current tab
      this.classList.add('active');

      // Show corresponding content
      const tabId = this.dataset.tab;
      document.querySelectorAll('.profile-tab-content').forEach(content => {
        content.classList.remove('active');
      });
      document.getElementById(`tab-${tabId}`).classList.add('active');
    });
  });

  // Add biography
  const addBioBtn = document.getElementById('add-bio-btn');
  if (addBioBtn) {
    addBioBtn.addEventListener('click', function() {
      // Switch to personal info tab
      profileTabs.forEach(t => t.classList.remove('active'));
      document.querySelector('.profile-tab[data-tab="info"]').classList.add('active');

      document.querySelectorAll('.profile-tab-content').forEach(content => {
        content.classList.remove('active');
      });
      document.getElementById('tab-info').classList.add('active');

      // Focus biography field
      document.getElementById('edit-bio').focus();
    });
  }

  // Cancel subscription
  const cancelSubscriptionBtn = document.getElementById('cancel-subscription-btn');
  if (cancelSubscriptionBtn) {
    cancelSubscriptionBtn.addEventListener('click', function() {
      if (confirm('¿Estás seguro de que deseas cancelar tu suscripción? Perderás todos los beneficios al final del período actual.')) {
        // Send request to cancel subscription
        fetch('api/subscription.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=cancel_subscription'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast('Suscripción cancelada correctamente', 'success');
              // Reload page
              window.location.reload();
            } else {
              showToast(data.message || 'Error al cancelar la suscripción', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showToast('Error al comunicarse con el servidor', 'error');
          });
      }
    });
  }
  
  // Function to show toast
  function showToast(message, type = 'info') {
    // Create toast element
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
    
    // Set colors based on type
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
    
    // Animate entry
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(0)';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(-100px)';
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }
});
</script>
