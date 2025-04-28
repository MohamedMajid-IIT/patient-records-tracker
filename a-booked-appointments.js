document.addEventListener("DOMContentLoaded", function () {
        const changeStatusButtons = document.querySelectorAll(".change-status-button");
        const statusPopup = document.getElementById("change-status-popup");
        const modalAppointmentIdInput = document.getElementById("modal-appointment-id");
        const newStatus = document.getElementById("new_status");
        const cancelButton = document.getElementById("cancel-change-status");

        changeStatusButtons.forEach(button => {
            button.addEventListener("click", () => {
                const appointmentId = button.getAttribute("data-id");
                const currentStatus = button.getAttribute("data-status");
                modalAppointmentIdInput.value = appointmentId;
                newStatus.value = currentStatus; 
                statusPopup.classList.remove("hidden");
            });
        });

        if (cancelButton) {
            cancelButton.addEventListener("click", () => {
                statusPopup.classList.add("hidden");
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const notesButtons = document.querySelectorAll(".notes-btn");

        // Notes popup
        const notesPopup = document.getElementById("notes-popup");
        const notesAppointmentId = document.getElementById("notes-appointment-id");
        const notesTextarea = document.getElementById("appointment-notes");
        const cancelNotesButton = document.getElementById("cancel-notes-button");
        const okayNotesButton = document.getElementById("okay-notes-button");
        
    
        notesButtons.forEach(button => {
            button.addEventListener("click", () => {
                const appointmentId = button.getAttribute("data-id");
                const notes = button.getAttribute("data-notes") || "";
    
                // Check if doctor notes popup  exists
                if (notesPopup && notesAppointmentId && notesTextarea) {
                    notesAppointmentId.value = appointmentId;
                    notesTextarea.value = notes;
                    notesPopup.classList.remove("hidden");
                }
    
                // Check if patient notes popup exists
                if (notesPopup && notesTextarea) {
                    notesTextarea.textContent = notes;
                    notesPopup.classList.remove("hidden");
                }
            });
        });
    
        // Notes cancel button - Doctors
        if (cancelNotesButton && notesPopup) {
            cancelNotesButton.addEventListener("click", () => {
                notesPopup.classList.add("hidden");
            });
    
            notesPopup.addEventListener("click", (e) => {
                if (e.target === notesPopup) {
                    notesPopup.classList.add("hidden");
                }
            });
        }
    
        // Notes Ok button - Patients
        if (okayNotesButton && notesPopup) {
            okayNotesButton.addEventListener("click", () => {
                notesPopup.classList.add("hidden");
            });
    
            notesPopup.addEventListener("click", (e) => {
                if (e.target === notesPopup) {
                    notesPopup.classList.add("hidden");
                }
            });
        }
    });

    function openFileUploadPopup(appointmentRecordId, patientName) {
        document.getElementById('uploadAppointmentRecordId').value = appointmentRecordId;
        document.getElementById("fileUploadPatientName").textContent = patientName;
        document.getElementById('fileUploadPopup').classList.remove('hidden');
    }
    
    function closeFileUploadPopup() {
        document.getElementById('fileUploadPopup').classList.add('hidden');
        window.location.href = window.location.pathname;
    
    }

    // Add multiple new file input for appointment
    function addAppointmentFileInput() {
        const container = document.getElementById("appointmentFileInputContainer");
        const block = document.createElement("div");
        block.className = "file-upload-block";
        block.innerHTML = `
            <label class="asterisk-label">File name:</label>
            <input type="text" name="appointment_file_names[]" placeholder="e.g: Lab Results" required>

            <input class="asterisk-label" type="file" name="appointment_files[]" required>

            <select class="drop-down-select smallest-width" name="file_types[]" required>
                <option value="">Select file type</option>
                <option value="Prescription">Prescription</option>
                <option value="Lab Report">Lab Report</option>
                <option value="X-ray">X-ray</option>
                <option value="Referral Letter">Referral Letter</option>
                <option value="Other">Other</option>
            </select>
        `;
        container.appendChild(block);
    }

    // Delete File
    document.addEventListener("DOMContentLoaded", () => {
        const deleteButtons = document.querySelectorAll(".delete-trigger");
        const deleteFilePopup = document.getElementById("deleteFilePopup");
        const confirmDelete = document.getElementById("confirmDelete");
        const dismissDelete = document.getElementById("dismissDelete");
        const deleteFileId = document.getElementById("deleteFileId");
        const deleteForm = document.getElementById("deleteForm");

        let selectedFileId = null;

        deleteButtons.forEach(button => {
            button.addEventListener("click", () => {
                selectedFileId = button.getAttribute("data-file-id");
                deleteFilePopup.classList.remove("hidden");
            });
        });

        confirmDelete.addEventListener("click", () => {
            if (selectedFileId) {
                deleteFileId.value = selectedFileId;
                deleteForm.submit();
            }
        });

        dismissDelete.addEventListener("click", () => {
            deleteFilePopup.classList.add("hidden");
            selectedFileId = null;
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        const viewButtons = document.querySelectorAll(".view-patient-btn");
        const popup = document.getElementById("patient-popup");
        const closeBtn = document.getElementById("close-patient-popup");
    
        const nameEl = document.getElementById("popup-patient-name");
        const emailEl = document.getElementById("popup-patient-email");
        const phoneEl = document.getElementById("popup-patient-phone");
        const sexEl = document.getElementById("popup-patient-sex");
        const dobEl = document.getElementById("popup-patient-dob");
        const nicEl = document.getElementById("popup-patient-nic");

        const emergencyNameEl = document.getElementById("popup-patient-emergency-contact-name");
        const emergencyRelationshipEl = document.getElementById("popup-patient-emergency-contact-relationship");
        const emergencyPhoneEl = document.getElementById("popup-patient-emergency-contact-phone");
        const emergencyEmailEl = document.getElementById("popup-patient-emergency-contact-email");
        
        const patientIdEl = document.getElementById("popup-patient-id");
    
        viewButtons.forEach(button => {
            button.addEventListener("click", () => {
                nameEl.textContent = button.getAttribute("data-name");
                emailEl.textContent = button.getAttribute("data-email");
                phoneEl.textContent = button.getAttribute("data-phone");
                const sex = button.getAttribute("data-sex");
                sexEl.textContent = sex.charAt(0).toUpperCase() + sex.slice(1);
                dobEl.textContent = button.getAttribute("data-dob");
                nicEl.textContent = button.getAttribute("data-nic");
                emergencyNameEl.textContent = button.getAttribute("data-emergency-contact-name");
                emergencyRelationshipEl.textContent = button.getAttribute("data-emergency-contact-relationship");
                emergencyPhoneEl.textContent = button.getAttribute("data-emergency-contact-phone");
                emergencyEmailEl.textContent = button.getAttribute("data-emergency-contact-email");

                patientIdEl.value = button.getAttribute("data-patient-id");
    
                popup.classList.remove("hidden");
            });
        });
        
        //Click close button to close popup
        if (closeBtn && popup) {
            closeBtn.addEventListener("click", () => {
                popup.classList.add("hidden");
            });
    
            //Click outside popup to close popup
            popup.addEventListener("click", (e) => {
                if (e.target === popup) {
                    popup.classList.add("hidden");
                }
            });
        }
    });
    
    
    