<?php
    session_start();
    require_once "db-php/db.php";
?>

<?php
    $popupMessage = "";
    $popupType = "";

    if (isset($_SESSION["success_message"])) {
        $popupMessage = $_SESSION["success_message"];
        $popupType = "success";
        unset($_SESSION["success_message"]);
    }

    if (isset($_SESSION["error_message"])) {
        $popupMessage = $_SESSION["error_message"];
        $popupType = "error";
        unset($_SESSION["error_message"]);
    }
?>
    
<?php
    // Check if the patient is logged in
    if (!isset($_SESSION["patient_id"])) {
        die("Access denied. Please log in as a patient.");
    }

    // Get the patient's ID from the session
    $patient_id = $_SESSION["patient_id"];
?>

<?php
    // Fetch medical records for the logged-in patient
    $medicalRecordsQuery = "SELECT
                                medical_record_id,
                                medical_record_title,
                                medical_record_description
                            FROM medical_records
                            WHERE patient_id = ?
                            ORDER BY medical_record_id DESC";

    $stmt = $conn->prepare($medicalRecordsQuery);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $medicalRecords = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your Medical Records - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <script src="http://localhost/PRTS/medical-and-appointment-files.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>
    
    <body>

        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <header>
            <section class="header-left">
                <h1><span id="patient-name">...</span>'s medical records</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/patient-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-appointments.php'">My Appointments</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <div class="search-or-create-box">
                <form action="a-medical-records-handler.php" method="POST" class="medical-record-form" autocomplete="off">
                    <div class="form-grid">
                        <div class="left-form-group">
                            <label class="asterisk-label" for="title">Title:</label>
                            <input class="max-width" type="text" id="title" name="medical_record_title" placeholder="e.g: Spine X-ray Report" required>
                        </div>
                        <br>
                        <br>
                        <div class="left-form-group">
                            <label class="asterisk-label" for="description">Description:</label>
                            <textarea class="max-width fixed-height" id="description" name="medical_record_description" rows="4" placeholder="e.g: X-ray report from 12/04/2025" required></textarea>
                        </div>
                    </div>
                    <div class="centered-button">
                        <button type="submit">Create Medical Record Collection</button>
                    </div>
                </form>
            </div>

            
            <div class="results-or-list-box">
                <h2>Your Medical Record Collections</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Collection Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($medicalRecords)&& $medicalRecords->num_rows > 0): ?>
                            <?php while ($row = $medicalRecords->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['medical_record_title']) ?></td>
                                    <td><?= htmlspecialchars($row['medical_record_description']) ?></td>
                                    <td>
                                        <form method="POST" action="p-view-medical-record-files.php" style="display: inline;">
                                            <input type="hidden" name="medical_record_id" value="<?= $row['medical_record_id'] ?>">
                                            <button type="submit">View Files</button>
                                        </form>
                                        <button onclick="openFileUploadPopup(<?= $row['medical_record_id'] ?>)">Add File</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No medical records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <br>
            

            <!-- Success/Fail Popup -->
            <div id="popupModal" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <p id="popupText"></p>
                    <button id="popupOkay">Okay</button>
                </div>
            </div>

            <!-- File Upload Popup -->
            <div id="fileUploadPopup" class="popup-modal hidden">
                <div class="popup-content wide-box">
                    <div class="popup-body" >
                        <h3>Upload Files for Medical Record</h3>
                        <form id="fileUploadForm" action="a-upload-medical-record-files.php" method="POST" autocomplete="off" enctype="multipart/form-data">
                            <input type="hidden" name="medical_record_id" id="uploadMedicalRecordId">
                            <div id="fileInputContainer">
                                <div class="file-upload-block">

                                    <label class="asterisk-label" for="file_name_field">File name: </label>
                                    <input type="text" id="file_name_field" name="medical_file_names[]" placeholder="e.g: X-ray (page 2)" required>
                                    
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
                                    
                                </div>
                            </div>
                            <br>
                            <button type="button" onclick="addFileInput()">+ Add Another File</button>
                            <br><br>
                            <button type="submit">Upload</button>
                            <button type="button" onclick="closeFileUploadPopup()">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>

        </main>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>