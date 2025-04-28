document.addEventListener("DOMContentLoaded", function () {
    fetch("http://localhost/PRTS/db-php/getUserDetails.php")
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                if (data.role === "patient") {
                    document.getElementById("patient-name").innerText = data.name;
                } else if (data.role === "doctor") {
                    document.getElementById("doctor-name").innerText = data.name;
                }
            } else {
                alert("Session expired. Please log in again.");
                window.location.href = "http://localhost/PRTS/a-login-page.php";
            }
        })
        .catch(error => console.error("Error:", error));
});




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

        // Appointment cancellation popup logic
        const cancelButtons = document.querySelectorAll(".cancel-trigger");
        const cancelModal = document.getElementById("cancelModal");
        const confirmCancel = document.getElementById("confirmCancel");
        const dismissCancel = document.getElementById("dismissCancel");
        const cancelAppointmentId = document.getElementById("cancelAppointmentId");
        const cancelForm = document.getElementById("cancelForm");
    
        let selectedAppointmentId = null;
    
        cancelButtons.forEach(button => {
            button.addEventListener("click", () => {
                selectedAppointmentId = button.getAttribute("data-appointment-id");
                cancelModal.classList.remove("hidden");
            });
        });
    
        confirmCancel.addEventListener("click", () => {
            if (selectedAppointmentId) {
                cancelAppointmentId.value = selectedAppointmentId;
                cancelForm.submit();
            }
        });
    
        dismissCancel.addEventListener("click", () => {
            cancelModal.classList.add("hidden");
            selectedAppointmentId = null;
        });
    });

// Logout Function
function logout() {
    fetch("http://localhost/PRTS/db-php/logout.php")
        .then(() => {
            window.location.href = "http://localhost/PRTS/a-login-page.php";
        });
}