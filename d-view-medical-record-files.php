<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: d-medical-records.php");
        die("The medical record is invalid or unavailable.");
    }
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
    // Check if the doctor is logged in
    if (!isset($_SESSION["doctor_id"])) {
        die("Access denied. Please log in as a doctor.");
    }

    // Get the doctor's ID from the session
    $doctor_id = $_SESSION["doctor_id"];

    $temp_patient_name = $_SESSION['selected_patient_name'] ?? '—no patient selected—';
    
?>

<?php
    // Get medical_record_id from URL
    if (!isset($_POST["medical_record_id"])) {
        die("Medical Record ID not provided.");
    }
    $medical_record_id = intval($_POST["medical_record_id"]);

    // SQL to fetch medical record files with uploader name logic
    $sql = "SELECT 
                medical_record_files.medical_record_file_id,
                medical_record_files.medical_record_file_name,
                medical_record_files.medical_record_file_path,
                medical_record_files.medical_record_file_type,
                medical_record_files.medical_record_created_at,
                users.user_id,
                users.role,
                patients.patient_name,
                doctors.doctor_name
            FROM medical_record_files
            JOIN users ON medical_record_files.medical_record_user_id = users.user_id
            LEFT JOIN patients ON users.role = 'patient' AND patients.user_id = users.user_id
            LEFT JOIN doctors ON users.role = 'doctor' AND doctors.user_id = users.user_id
            WHERE medical_record_files.medical_record_id = ?
            ORDER BY medical_record_files.medical_record_created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $medical_record_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>



<!DOCTYPE html> 
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Medical Record Files - PRTS</title>
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
                <h1><?= htmlspecialchars($temp_patient_name, ENT_QUOTES) ?>'s medical record files</h1> 
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/d-medical-records.php'">Return to Medical Records</button> 
                <button onclick="location.href='http://localhost/PRTS/d-my-availability.php'">My Availability Slots</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            
            <!-- Success/Fail Popup -->
            <div id="popupModal" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <p id="popupText"></p>
                    <button id="popupOkay">Okay</button>
                </div>
            </div>

            <div class="search-or-create-box small-height">
                <p><em>Welcome Dr. <span id="doctor-name">...</span>! You are now viewing <?= htmlspecialchars($temp_patient_name, ENT_QUOTES) ?>'s medical record files.</em></p>
            </div>
            <br>
            <div class="results-or-list-box wider-table">
              
                <input type="hidden" id="medical_record_id" value="<?= htmlspecialchars($medical_record_id) ?>">
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>File Type</th>
                            <th>Uploaded By</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['medical_record_file_name']) ?></td>
                                    <td><?= htmlspecialchars($row['medical_record_file_type']) ?></td>
                                    <td>
                                        <?= $row['role'] === 'patient' ? htmlspecialchars($row['patient_name']) : "Dr. " . htmlspecialchars($row['doctor_name']) ?>
                                    </td>
                                    <td>
                                        <?= (new DateTime($row['medical_record_created_at']))->format("d-m-Y h:i A") ?>
                                    </td>
                                    <td>
                                        <button onclick="window.open('<?= htmlspecialchars($row['medical_record_file_path']) ?>', '_blank')">View File</button>
                                        
                                        <a href="<?= htmlspecialchars($row['medical_record_file_path']) ?>" download><button>Download File</button></a>

                                        <button type="button" class="delete-button delete-trigger" data-file-id="<?= $row['medical_record_file_id'] ?>">Delete File</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No files uploaded for this medical record.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <br>

            <!-- Delete Confirmation Modal -->
            <div id="deleteFilePopup" class="popup-modal cancel hidden">
                <div class="popup-content normal-box">
                    <p>Are you sure you want to delete this file?</p>
                    <form method="POST" action="a-delete-medical-record-file.php" id="deleteForm">
                        <input type="hidden" name="medical_record_file_id" id="deleteFileId">
                        <input type="hidden" name="medical_record_id" value="<?= $medical_record_id ?>">
                        <button type="submit" class="delete-button" id="confirmDelete">Delete</button>
                        <button type="button" id="dismissDelete">Cancel</button>
                    </form>
                </div>
            </div>

        </main>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>