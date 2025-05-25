// Variables globales
let currentSong = null
let isPlaying = false
const playQueue = []
const audio = new Audio()

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Aplicar tema y color guardados
  applyThemeAndColor()

  // Inicializar el reproductor de música
  initPlayer()

  // Inicializar el menú desplegable de usuario
  initUserDropdown()

  // Inicializar el cambio de tema
  initThemeToggle()

  // Inicializar los eventos de las tarjetas de música
  initMusicCards()

  // Inicializar modales
  initModals()

  // Inicializar botones de mostrar/ocultar contraseña
  initPasswordToggles()
})

// Aplicar tema y color guardados
function applyThemeAndColor() {
  const savedTheme = localStorage.getItem("theme") || "dark"
  const savedColor = localStorage.getItem("primaryColor") || "#1db954"

  // Aplicar color primario
  document.documentElement.style.setProperty("--primary-color", savedColor)
  document.documentElement.style.setProperty("--primary-hover", adjustColor(savedColor, -20))

  // Aplicar tema
  if (savedTheme === "dark") {
    document.documentElement.style.setProperty("--bg-color", "#121212")
    document.documentElement.style.setProperty("--bg-secondary", "#181818")
    document.documentElement.style.setProperty("--bg-card", "#282828")
    document.documentElement.style.setProperty("--text-color", "#eee")
    document.documentElement.style.setProperty("--text-secondary", "#bbb")
    document.documentElement.style.setProperty("--border-color", "#333")

    const themeToggle = document.getElementById("theme-toggle")
    if (themeToggle) {
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>'
    }
  } else if (savedTheme === "light") {
    document.documentElement.style.setProperty("--bg-color", "#f5f5f5")
    document.documentElement.style.setProperty("--bg-secondary", "#ffffff")
    document.documentElement.style.setProperty("--bg-card", "#e9e9e9")
    document.documentElement.style.setProperty("--text-color", "#333")
    document.documentElement.style.setProperty("--text-secondary", "#666")
    document.documentElement.style.setProperty("--border-color", "#ddd")

    const themeToggle = document.getElementById("theme-toggle")
    if (themeToggle) {
      themeToggle.innerHTML = '<i class="fas fa-moon"></i>'
    }
  } else if (savedTheme === "contrast") {
    document.documentElement.style.setProperty("--bg-color", "#000000")
    document.documentElement.style.setProperty("--bg-secondary", "#0a0a0a")
    document.documentElement.style.setProperty("--bg-card", "#1a1a1a")
    document.documentElement.style.setProperty("--text-color", "#ffffff")
    document.documentElement.style.setProperty("--text-secondary", "#cccccc")
    document.documentElement.style.setProperty("--border-color", "#444")

    const themeToggle = document.getElementById("theme-toggle")
    if (themeToggle) {
      themeToggle.innerHTML = '<i class="fas fa-adjust"></i>'
    }
  }
}

// Inicializar el reproductor de música
function initPlayer() {
  const playPauseBtn = document.getElementById("play-pause-btn")
  const prevBtn = document.getElementById("prev-btn")
  const nextBtn = document.getElementById("next-btn")

  if (playPauseBtn) {
    playPauseBtn.addEventListener("click", togglePlay)
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", playPrevious)
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", playNext)
  }

  // Configurar eventos de audio
  audio.addEventListener("ended", playNext)
  audio.addEventListener("play", updatePlayButton)
  audio.addEventListener("pause", updatePlayButton)

  // Cargar canción actual si existe
  const audioPlayer = document.getElementById("audio-player")
  if (audioPlayer) {
    audio.src = audioPlayer.src
    currentSong = {
      id: audioPlayer.dataset.id,
      title: document.getElementById("track-name").textContent,
    }
  }

  // Mostrar u ocultar el reproductor flotante según si hay una canción reproduciéndose
  updateFloatingPlayer()
}

// Actualizar el reproductor flotante
function updateFloatingPlayer() {
  const floatingPlayer = document.getElementById("floating-player")
  if (floatingPlayer) {
    if (audio.src) {
      floatingPlayer.classList.add("active")
    } else {
      floatingPlayer.classList.remove("active")
    }
  }
}

// Reproducir/pausar la canción actual
function togglePlay() {
  if (!audio.src) return

  if (audio.paused) {
    audio.play()
  } else {
    audio.pause()
  }
}

// Actualizar el botón de reproducción/pausa
function updatePlayButton() {
  const playPauseBtn = document.getElementById("play-pause-btn")
  if (!playPauseBtn) return

  if (audio.paused) {
    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>'
    isPlaying = false
  } else {
    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>'
    isPlaying = true
  }

  // Actualizar el reproductor flotante
  updateFloatingPlayer()
}

// Reproducir la canción anterior
function playPrevious() {
  if (playQueue.length === 0) {
    showToast("No hay canciones anteriores en la cola", "info")
    return
  }

  // Obtener la canción anterior de la cola
  const previousSong = playQueue.pop()
  if (previousSong) {
    playSong(previousSong)
  } else {
    showToast("No hay canciones anteriores en la cola", "info")
  }
}

// Reproducir la siguiente canción
function playNext() {
  if (playQueue.length === 0) {
    showToast("No hay más canciones en la cola", "info")
    return
  }

  // Obtener la siguiente canción de la cola
  const nextSong = playQueue.shift()
  if (nextSong) {
    playSong(nextSong)
  } else {
    showToast("No hay más canciones en la cola", "info")
  }
}

// Reproducir una canción específica
function playSong(songId) {
  // Enviar solicitud AJAX para reproducir la canción
  fetch(`api/music.php?action=get_song&id=${songId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Si hay una canción reproduciéndose, añadirla a la cola
        if (currentSong && currentSong.id) {
          playQueue.push(currentSong.id)
        }

        const song = data.song
        currentSong = {
          id: song.id,
          title: song.titulo,
          artist: song.artista,
        }

        // Verificar si el archivo de audio existe y es accesible
        const audioSrc = song.archivo_audio

        // Configurar el audio
        audio.src = audioSrc

        // Intentar reproducir
        audio
          .play()
          .then(() => {
            console.log("Reproducción iniciada con éxito")
          })
          .catch((error) => {
            console.error("Error al reproducir:", error)
            showToast(
              "No se pudo reproducir el archivo de audio. Puede que el formato no sea compatible o el archivo no exista.",
              "error",
            )
          })

        // Actualizar la interfaz del reproductor
        const playerCover = document.getElementById("player-cover")
        const trackName = document.getElementById("track-name")

        if (playerCover) {
          playerCover.src = song.portada
        }

        if (trackName) {
          trackName.textContent = `${song.titulo} - ${song.artista}`
        }

        // Actualizar el reproductor flotante
        const floatingPlayerCover = document.getElementById("floating-player-cover")
        const floatingTrackName = document.getElementById("floating-track-name")

        if (floatingPlayerCover) {
          floatingPlayerCover.src = song.portada
        }

        if (floatingTrackName) {
          floatingTrackName.textContent = `${song.titulo} - ${song.artista}`
        }

        // Mostrar el reproductor flotante
        const floatingPlayer = document.getElementById("floating-player")
        if (floatingPlayer) {
          floatingPlayer.classList.add("active")
        }

        // Registrar la reproducción
        fetch(`api/music.php?action=record_play&id=${songId}`).catch((error) => {
          console.error("Error al registrar reproducción:", error)
        })
      } else {
        showToast(data.message || "Error al reproducir la canción", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showToast("Error al comunicarse con el servidor", "error")
    })
}

// Añadir canción a la cola de reproducción
function addToQueue(songId) {
  playQueue.push(songId)
  showToast("Canción añadida a la cola de reproducción", "success")
}

// Añadir canción al carrito
function addToCart(songId) {
  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=add_to_cart&song_id=${songId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Canción añadida al carrito", "success")
      } else {
        showToast(data.message || "Error al añadir la canción al carrito", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showToast("Error al comunicarse con el servidor", "error")
    })
}

// Mostrar modal para añadir a playlist
function showAddToPlaylistModal(songId) {
  // Obtener las playlists del usuario
  fetch("api/playlist.php?action=get_playlists")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const playlists = data.playlists

        // Crear el contenido del modal
        let modalContent = `
          <div class="modal-header">
            <h3 class="modal-title">Añadir a Playlist</h3>
            <button class="modal-close" id="close-playlist-modal"><i class="fas fa-times"></i></button>
          </div>
          <div class="modal-body">
        `

        if (playlists && playlists.length > 0) {
          modalContent += `<p>Selecciona una playlist:</p><ul class="playlist-select-list">`

          playlists.forEach((playlist) => {
            modalContent += `
              <li data-id="${playlist.id_playlist}" data-song-id="${songId}">
                <i class="fas fa-list"></i> ${playlist.nombre_playlist}
              </li>
            `
          })

          modalContent += `</ul>`
        } else {
          modalContent += `
            <div class="empty-state">
              <p>No tienes playlists. Crea una nueva playlist primero.</p>
              <button id="create-new-playlist-btn" class="btn-primary">Crear Playlist</button>
            </div>
          `
        }

        modalContent += `
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" id="cancel-playlist-modal">Cancelar</button>
          </div>
        `

        // Mostrar el modal
        const playlistModal = document.getElementById("playlist-modal")
        if (playlistModal) {
          playlistModal.querySelector(".modal-container").innerHTML = modalContent
          playlistModal.classList.add("active")

          // Eventos para los elementos del modal
          const closeBtn = playlistModal.querySelector("#close-playlist-modal")
          const cancelBtn = playlistModal.querySelector("#cancel-playlist-modal")
          const createNewPlaylistBtn = playlistModal.querySelector("#create-new-playlist-btn")
          const playlistItems = playlistModal.querySelectorAll(".playlist-select-list li")

          if (closeBtn) {
            closeBtn.addEventListener("click", () => {
              playlistModal.classList.remove("active")
            })
          }

          if (cancelBtn) {
            cancelBtn.addEventListener("click", () => {
              playlistModal.classList.remove("active")
            })
          }

          if (createNewPlaylistBtn) {
            createNewPlaylistBtn.addEventListener("click", () => {
              playlistModal.classList.remove("active")
              // Mostrar modal para crear playlist
              showCreatePlaylistModal()
            })
          }

          playlistItems.forEach((item) => {
            item.addEventListener("click", () => {
              const playlistId = item.dataset.id
              const songId = item.dataset.songId

              // Añadir canción a la playlist
              addSongToPlaylist(playlistId, songId)

              // Cerrar el modal
              playlistModal.classList.remove("active")
            })
          })
        }
      } else {
        showToast(data.message || "Error al obtener las playlists", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showToast("Error al comunicarse con el servidor", "error")
    })
}

// Añadir canción a una playlist
function addSongToPlaylist(playlistId, songId) {
  fetch("api/playlist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=add_song&playlist_id=${playlistId}&song_id=${songId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Canción añadida a la playlist", "success")
      } else {
        showToast(data.message || "Error al añadir la canción a la playlist", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showToast("Error al comunicarse con el servidor", "error")
    })
}

// Mostrar modal para crear playlist
function showCreatePlaylistModal() {
  const playlistModal = document.getElementById("playlist-modal")
  if (playlistModal) {
    const modalContent = `
      <div class="modal-header">
        <h3 class="modal-title">Nueva Playlist</h3>
        <button class="modal-close" id="close-create-playlist-modal"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <form id="create-playlist-form">
          <div class="form-group">
            <label for="playlist-name">Nombre de la playlist</label>
            <input type="text" id="playlist-name" required minlength="3" maxlength="100">
          </div>
          <div class="form-group">
            <label for="playlist-description">Descripción (opcional)</label>
            <textarea id="playlist-description" rows="3" maxlength="500"></textarea>
          </div>
          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" id="playlist-public" class="form-check-input">
              <label for="playlist-public" class="form-check-label">Playlist pública</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" id="cancel-create-playlist-modal">Cancelar</button>
        <button class="btn-primary" id="save-playlist">Guardar</button>
      </div>
    `

    playlistModal.querySelector(".modal-container").innerHTML = modalContent
    playlistModal.classList.add("active")

    // Eventos para los elementos del modal
    const closeBtn = playlistModal.querySelector("#close-create-playlist-modal")
    const cancelBtn = playlistModal.querySelector("#cancel-create-playlist-modal")
    const saveBtn = playlistModal.querySelector("#save-playlist")
    const form = playlistModal.querySelector("#create-playlist-form")

    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        playlistModal.classList.remove("active")
      })
    }

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
        playlistModal.classList.remove("active")
      })
    }

    if (saveBtn && form) {
      saveBtn.addEventListener("click", () => {
        const playlistName = document.getElementById("playlist-name").value.trim()
        const playlistDescription = document.getElementById("playlist-description").value.trim()
        const isPublic = document.getElementById("playlist-public").checked ? 1 : 0

        if (playlistName.length < 3) {
          showToast("El nombre de la playlist debe tener al menos 3 caracteres", "error")
          return
        }

        // Crear playlist
        fetch("api/playlist.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `action=create_playlist&name=${encodeURIComponent(playlistName)}&description=${encodeURIComponent(playlistDescription)}&public=${isPublic}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              showToast("Playlist creada correctamente", "success")
              playlistModal.classList.remove("active")

              // Redirigir a la página de playlist
              window.location.href = `index.php?page=playlist&id=${data.playlist_id}`
            } else {
              showToast(data.message || "Error al crear la playlist", "error")
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            showToast("Error al comunicarse con el servidor", "error")
          })
      })
    }
  }
}

// Inicializar el menú desplegable de usuario
function initUserDropdown() {
  const userProfileContainer = document.querySelector(".user-profile-container")
  const dropdown = document.getElementById("user-dropdown")

  if (userProfileContainer && dropdown) {
    userProfileContainer.addEventListener("click", (e) => {
      e.preventDefault()
      e.stopPropagation()
      dropdown.classList.toggle("show")
    })
  }

  // Cerrar el dropdown al hacer clic fuera
  document.addEventListener("click", (event) => {
    if (dropdown && dropdown.classList.contains("show") && !event.target.closest(".user-profile-container")) {
      dropdown.classList.remove("show")
    }
  })
}

// Inicializar el cambio de tema
function initThemeToggle() {
  const themeToggle = document.getElementById("theme-toggle")

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const currentTheme = localStorage.getItem("theme") || "dark"
      let newTheme

      if (currentTheme === "dark") {
        newTheme = "light"
      } else if (currentTheme === "light") {
        newTheme = "contrast"
      } else {
        newTheme = "dark"
      }

      localStorage.setItem("theme", newTheme)
      applyThemeAndColor()
    })
  }
}

// Inicializar los eventos de las tarjetas de música
function initMusicCards() {
  const cards = document.querySelectorAll(".card[data-id]")

  cards.forEach((card) => {
    card.addEventListener("click", function (e) {
      // Solo reproducir si no se hizo clic en un botón de acción
      if (!e.target.closest(".song-action-btn")) {
        const songId = this.dataset.id
        playSong(songId)
      }
    })

    card.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        const songId = this.dataset.id
        playSong(songId)
      }
    })

    // Añadir botones de acción a las tarjetas
    if (!card.querySelector(".card-actions")) {
      const songId = card.dataset.id
      const actionsDiv = document.createElement("div")
      actionsDiv.className = "card-actions"
      actionsDiv.innerHTML = `
        <button class="song-action-btn add-to-queue-btn" data-id="${songId}" title="Añadir a la cola">
          <i class="fas fa-list"></i>
        </button>
        <button class="song-action-btn add-to-playlist-btn" data-id="${songId}" title="Añadir a playlist">
          <i class="fas fa-plus"></i>
        </button>
        <button class="song-action-btn add-to-cart-btn" data-id="${songId}" title="Añadir al carrito">
          <i class="fas fa-shopping-cart"></i>
        </button>
      `
      card.appendChild(actionsDiv)

      // Añadir eventos a los botones
      const addToQueueBtn = actionsDiv.querySelector(".add-to-queue-btn")
      const addToPlaylistBtn = actionsDiv.querySelector(".add-to-playlist-btn")
      const addToCartBtn = actionsDiv.querySelector(".add-to-cart-btn")

      if (addToQueueBtn) {
        addToQueueBtn.addEventListener("click", function (e) {
          e.stopPropagation() // Evitar que se reproduzca la canción
          const songId = this.dataset.id
          addToQueue(songId)
        })
      }

      if (addToPlaylistBtn) {
        addToPlaylistBtn.addEventListener("click", function (e) {
          e.stopPropagation() // Evitar que se reproduzca la canción
          const songId = this.dataset.id
          showAddToPlaylistModal(songId)
        })
      }

      if (addToCartBtn) {
        addToCartBtn.addEventListener("click", function (e) {
          e.stopPropagation() // Evitar que se reproduzca la canción
          const songId = this.dataset.id
          addToCart(songId)
        })
      }
    }
  })
}

// Inicializar modales
function initModals() {
  const modalOverlays = document.querySelectorAll(".modal-overlay")
  const modalCloseButtons = document.querySelectorAll('.modal-close, .btn-secondary[id*="cancel"]')

  modalCloseButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const modalId = this.closest(".modal-overlay").id
      document.getElementById(modalId).classList.remove("active")
    })
  })

  // Botón para dar de baja la cuenta
  const deactivateBtn = document.getElementById("deactivate-account-btn")
  if (deactivateBtn) {
    deactivateBtn.addEventListener("click", () => {
      document.getElementById("deactivate-modal").classList.add("active")
    })
  }

  // Confirmar dar de baja la cuenta
  const confirmDeactivateBtn = document.getElementById("confirm-deactivate")
  if (confirmDeactivateBtn) {
    confirmDeactivateBtn.addEventListener("click", () => {
      const reason = document.getElementById("deactivate-reason").value
      const password = document.getElementById("deactivate-password").value

      if (!reason) {
        showToast("Por favor, selecciona una razón para dar de baja tu cuenta.", "error")
        return
      }

      if (!password) {
        showToast("Por favor, ingresa tu contraseña para confirmar.", "error")
        return
      }

      // Enviar solicitud para dar de baja la cuenta
      fetch("api/user.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=deactivate&reason=${reason}&password=${password}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.href = "index.php"
          } else {
            showToast(data.message || "Error al dar de baja la cuenta", "error")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          showToast("Error al comunicarse con el servidor", "error")
        })
    })
  }
}

// Inicializar botones para mostrar/ocultar contraseña
function initPasswordToggles() {
  const toggleButtons = document.querySelectorAll(".toggle-password")

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.previousElementSibling
      const type = input.getAttribute("type") === "password" ? "text" : "password"
      input.setAttribute("type", type)

      // Cambiar el icono
      const icon = this.querySelector("i")
      if (type === "password") {
        icon.classList.remove("fa-eye-slash")
        icon.classList.add("fa-eye")
      } else {
        icon.classList.remove("fa-eye")
        icon.classList.add("fa-eye-slash")
      }
    })
  })
}

// Función para ajustar color
function adjustColor(color, amount) {
  return (
    "#" +
    color
      .replace(/^#/, "")
      .replace(/../g, (color) =>
        ("0" + Math.min(255, Math.max(0, Number.parseInt(color, 16) + amount)).toString(16)).substr(-2),
      )
  )
}

// Mostrar un mensaje toast
function showToast(message, type = "info") {
  // Crear el elemento toast
  const toast = document.createElement("div")
  toast.style.position = "fixed"
  toast.style.top = "20px"
  toast.style.left = "50%"
  toast.style.transform = "translateX(-50%) translateY(-100px)"
  toast.style.padding = "10px 20px"
  toast.style.borderRadius = "5px"
  toast.style.boxShadow = "0 3px 10px rgba(0,0,0,0.3)"
  toast.style.zIndex = "1000"
  toast.style.transition = "all 0.3s ease"

  // Establecer colores según el tipo
  if (type === "error") {
    toast.style.background = "#e74c3c"
    toast.style.color = "#fff"
    toast.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`
  } else if (type === "success") {
    toast.style.background = "var(--primary-color)"
    toast.style.color = "var(--bg-color)"
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`
  } else {
    toast.style.background = "#3498db"
    toast.style.color = "#fff"
    toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`
  }

  document.body.appendChild(toast)

  // Animar entrada
  setTimeout(() => {
    toast.style.transform = "translateX(-50%) translateY(0)"
  }, 10)

  // Eliminar después de 3 segundos
  setTimeout(() => {
    toast.style.transform = "translateX(-50%) translateY(-100px)"
    setTimeout(() => {
      document.body.removeChild(toast)
    }, 300)
  }, 3000)
}