document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const creditCardBtn = document.getElementById("creditCardBtn")
    const paypalBtn = document.getElementById("paypalBtn")
    const creditCardForm = document.getElementById("creditCardForm")
    const paypalForm = document.getElementById("paypalForm")
    const paypalRedirectBtn = document.getElementById("paypalRedirectBtn")
    const successModal = document.getElementById("successModal")
    const closeBtn = document.querySelector(".close-btn")
    const modalBtn = document.querySelector(".modal-btn")
  
    // Campos del formulario de tarjeta
    const cardNumber = document.getElementById("cardNumber")
    const expiryDate = document.getElementById("expiryDate")
    const cvv = document.getElementById("cvv")
    const cardName = document.getElementById("cardName")
  
    // Mensajes de error
    const cardNumberError = document.getElementById("cardNumberError")
    const expiryDateError = document.getElementById("expiryDateError")
    const cvvError = document.getElementById("cvvError")
    const cardNameError = document.getElementById("cardNameError")
  
    // Cambiar entre métodos de pago
    creditCardBtn.addEventListener("click", () => {
      creditCardBtn.classList.add("active")
      paypalBtn.classList.remove("active")
      creditCardForm.classList.remove("hidden")
      paypalForm.classList.add("hidden")
    })
  
    paypalBtn.addEventListener("click", () => {
      paypalBtn.classList.add("active")
      creditCardBtn.classList.remove("active")
      paypalForm.classList.remove("hidden")
      creditCardForm.classList.add("hidden")
    })
  
    // Formatear número de tarjeta con espacios cada 4 dígitos
    cardNumber.addEventListener("input", (e) => {
      const value = e.target.value.replace(/\s+/g, "").replace(/[^0-9]/gi, "")
      let formattedValue = ""
  
      for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
          formattedValue += " "
        }
        formattedValue += value[i]
      }
  
      e.target.value = formattedValue
    })
  
    // Formatear fecha de expiración (MM/AA)
    expiryDate.addEventListener("input", (e) => {
      const value = e.target.value.replace(/\D/g, "")
  
      if (value.length > 0) {
        if (value.length <= 2) {
          e.target.value = value
        } else {
          e.target.value = value.slice(0, 2) + "/" + value.slice(2, 4)
        }
      }
    })
  
    // Solo permitir números en CVV
    cvv.addEventListener("input", (e) => {
      e.target.value = e.target.value.replace(/\D/g, "")
    })
  
    // Validación del formulario de tarjeta
    creditCardForm.addEventListener("submit", (e) => {
      e.preventDefault()
      let isValid = true
  
      // Validar número de tarjeta (16 dígitos sin espacios)
      const cardNumberValue = cardNumber.value.replace(/\s+/g, "")
      if (!/^\d{16}$/.test(cardNumberValue)) {
        cardNumberError.textContent = "Número de tarjeta inválido. Debe tener 16 dígitos."
        isValid = false
      } else {
        cardNumberError.textContent = ""
      }
  
      // Validar fecha de expiración (MM/AA)
      if (!/^(0[1-9]|1[0-2])\/([0-9]{2})$/.test(expiryDate.value)) {
        expiryDateError.textContent = "Formato inválido. Use MM/AA."
        isValid = false
      } else {
        const [month, year] = expiryDate.value.split("/")
        const currentDate = new Date()
        const currentYear = currentDate.getFullYear() % 100
        const currentMonth = currentDate.getMonth() + 1
  
        if (
          Number.parseInt(year) < currentYear ||
          (Number.parseInt(year) === currentYear && Number.parseInt(month) < currentMonth)
        ) {
          expiryDateError.textContent = "La tarjeta ha expirado."
          isValid = false
        } else {
          expiryDateError.textContent = ""
        }
      }
  
      // Validar CVV (3 dígitos)
      if (!/^\d{3}$/.test(cvv.value)) {
        cvvError.textContent = "CVV inválido. Debe tener 3 dígitos."
        isValid = false
      } else {
        cvvError.textContent = ""
      }
  
      // Validar nombre en la tarjeta
      if (cardName.value.trim().length < 3) {
        cardNameError.textContent = "Por favor, ingrese el nombre completo."
        isValid = false
      } else {
        cardNameError.textContent = ""
      }
  
      // Si todo es válido, mostrar modal de éxito
      if (isValid) {
        showSuccessModal()
      }
    })
  
    // Botón de redirección a PayPal
    paypalRedirectBtn.addEventListener("click", () => {
      // Aquí normalmente redirigirías a PayPal
      // Para este ejemplo, solo mostraremos el modal de éxito
      showSuccessModal()
    })
  
    // Funciones para el modal
    function showSuccessModal() {
      successModal.style.display = "flex"
    }
  
    function closeModal() {
      successModal.style.display = "none"
    }
  
    closeBtn.addEventListener("click", closeModal)
    modalBtn.addEventListener("click", closeModal)
  
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener("click", (e) => {
      if (e.target === successModal) {
        closeModal()
      }
    })
  })
  