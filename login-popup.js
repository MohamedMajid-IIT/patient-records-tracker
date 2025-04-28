function togglePopup(id) {
  const validIds = ['patientLogIn', 'patientSignUp', 'doctorLogIn', 'doctorSignUp'];



  if (validIds.includes(id)) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.toggle('show');
    } else {
      console.error(`No element found with id: ${id}`);
    }
  } else {
    console.error('Invalid id. Use "patientLogIn" or "doctorLogIn".');
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (typeof popupMessage !== "undefined" && popupMessage) {
      const modal = document.getElementById("popupModal");
      const popupText = document.getElementById("popupText");
      const okayButton = document.getElementById("popupOkay");

      popupText.textContent = popupMessage;

      modal.classList.remove("success", "error", "cancel");

      if (popupType === "success") {
          modal.classList.add("success");
      } else if (popupType === "error") {
          modal.classList.add("error");
      } else if (popupType === "cancel") {
          modal.classList.add("cancel");
      }

      modal.classList.remove("hidden");

      okayButton.addEventListener("click", () => {
          modal.classList.add("hidden");
          // Reload page after dismissing
          window.location.href = window.location.pathname;
      });
  }
});

document.addEventListener('DOMContentLoaded', function() {
  // Select ALL input elements with type="tel"
  const phoneInput = document.querySelectorAll('input[type="tel"]');

  // Checking if any elements with type="tel" exist
  if (phoneInput.length > 0) {
    phoneInput.forEach(function(inputElement) {
        // Event listener for the current element in the loop
        inputElement.addEventListener('keydown', function(event) {
            // This is to get the key that was pressed
            const key = event.key;

            // Allowed keys, digits, symbols, control buttons
            const allowedControlKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
            const isDigit = key >= '0' && key <= '9';
            const isAllowedControlKey = allowedControlKeys.includes(key);
            const isModifierKeyPressed = event.ctrlKey || event.metaKey; // e.g: Ctrl+C, Ctrl+V

            // If the key or key combination is NOT in the allowed list then prevent input
            if (!(isDigit || isAllowedControlKey || isModifierKeyPressed)) {
                event.preventDefault();
            }
        });
    }); 
}
});