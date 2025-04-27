function openFileUploadPopup(medicalRecordId) {
    document.getElementById('uploadMedicalRecordId').value = medicalRecordId;
    document.getElementById('fileUploadPopup').classList.remove('hidden');
}

function closeFileUploadPopup() {
    document.getElementById('fileUploadPopup').classList.add('hidden');
    window.location.href = window.location.pathname;

}

// Add new file + dropdown input
function addFileInput() {
    const container = document.getElementById('fileInputContainer');
    const block = document.createElement('div');
    block.className = 'file-upload-block';
    block.innerHTML = `
        <label class="asterisk-label" for="file_name_field">File name: </label>
        <input type="text" id="file_name_field" name="medical_file_names[]" placeholder="Enter file name" required>
                                    
        <input class="asterisk-label" type="file" name="medical_files[]" required>
                                    
        <select class="drop-down-select smallest-width" name="file_types[]" required>
            <option value="">Select file type</option>
            <option value="X-ray">X-ray</option>
            <option value="Prescription">Prescription</option>
            <option value="Lab Report">Lab Report</option>
            <option value="CT scan">CT scan</option>
            <option value="MRI scan">MRI scan</option>
            <option value="Ultrasound">Ultrasound</option>
            <option value="ECG/EKG">ECG/EKG</option>
            <option value="Pathology report">Pathology report</option>
            <option value="Discharge summary">Discharge summary</option>
            <option value="Referral letter">Referral letter</option>
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



