document.addEventListener("DOMContentLoaded", function () {
    const viewButtons = document.querySelectorAll(".view-patient-btn");
    const popup = document.getElementById("patient-popup");
    const closeBtn = document.getElementById("close-patient-popup");

    // Emergency contact details 
    const emergencyNameEl = document.getElementById("popup-patient-emergency-contact-name");
    const emergencyRelationshipEl = document.getElementById("popup-patient-emergency-contact-relationship");
    const emergencyPhoneEl = document.getElementById("popup-patient-emergency-contact-phone");
    const emergencyEmailEl = document.getElementById("popup-patient-emergency-contact-email");

    viewButtons.forEach(button => {
        button.addEventListener("click", () => {
            // Get values from the button
            emergencyNameEl.textContent = button.getAttribute("data-emergency-contact-name");
            emergencyRelationshipEl.textContent = button.getAttribute("data-emergency-contact-relationship");
            emergencyPhoneEl.textContent = button.getAttribute("data-emergency-contact-phone");
            emergencyEmailEl.textContent = button.getAttribute("data-emergency-contact-email");

            // Show the popup
            popup.classList.remove("hidden");
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            popup.classList.add("hidden");
        });

        popup.addEventListener("click", (e) => {
            if (e.target === popup) {
                popup.classList.add("hidden");
            }
        });
    }
});
