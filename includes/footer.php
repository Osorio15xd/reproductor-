</main>

<!-- Theme toggle button -->
<div id="theme-toggle" aria-label="Cambiar tema">
  <i class="fas fa-moon"></i>
</div>

<!-- Modal para dar de baja la cuenta -->
<div class="modal-overlay" id="deactivate-modal">
  <div class="modal-container">
    <div class="modal-header">
      <h3 class="modal-title">Dar de Baja la Cuenta</h3>
      <button class="modal-close" id="close-deactivate-modal"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p>¿Estás seguro de que deseas dar de baja tu cuenta? Esta acción no se puede deshacer y perderás:</p>
      <ul>
        <li>Tu biblioteca de música</li>
        <li>Tus playlists</li>
        <li>Tu historial de reproducciones</li>
        <li>Tu suscripción activa</li>
      </ul>
      <div class="form-group mt-3">
        <label for="deactivate-reason">¿Por qué te vas?</label>
        <select id="deactivate-reason" style="width: 100%; padding: 8px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 5px;">
          <option value="">Selecciona una razón</option>
          <option value="price">El precio es muy alto</option>
          <option value="content">No encuentro la música que me gusta</option>
          <option value="service">Problemas con el servicio</option>
          <option value="competitor">Me cambio a otro servicio</option>
          <option value="other">Otra razón</option>
        </select>
      </div>
      <div class="form-group mt-3">
        <label for="deactivate-password">Confirma tu contraseña</label>
        <input type="password" id="deactivate-password" placeholder="Contraseña" style="width: 100%; padding: 8px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 5px;">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="cancel-deactivate">Cancelar</button>
      <button class="btn btn-danger" id="confirm-deactivate">Dar de Baja</button>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Script principal -->
<script src="assets/js/script.js"></script>
</body>
</html>