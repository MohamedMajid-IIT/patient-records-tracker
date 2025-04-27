<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";


    // Ensure doctor is logged in
    if (!isset($_SESSION["doctor_id"])) {
        header("Location: a-login-page.php");
    }

    $doctor_id = $_SESSION["doctor_id"];
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
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_status"])) {
            $appointment_id = $_POST["appointment_id"];
            $new_status = $_POST["new_status"];
        
            $update_status_sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
            $update_status_stmt = $conn->prepare($update_status_sql);
            $update_status_stmt->bind_param("si", $new_status, $appointment_id);
            $update_status_stmt->execute();
            
            // Refresh page to reflect update
            header("Location: d-booked-appointments.php");
            exit();
        }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_notes"])) {
            $appointment_id = $_POST["appointment_id"];
            $appointment_notes = $_POST["appointment_notes"];
        
            $update_notes_sql = "UPDATE appointments SET appointment_notes = ? WHERE appointment_id = ?";
            $update_notes_stmt = $conn->prepare($update_notes_sql);
            $update_notes_stmt->bind_param("si", $appointment_notes, $appointment_id);
            $update_notes_stmt->execute();
        
            // Refresh page to reflect changes
            header("Location: d-booked-appointments.php");
            exit();
        }
        
        

    // Get booked appointments for this doctor
    $sql = "SELECT
                appointments.appointment_id,
                appointments.status,
                appointments.appointment_notes,
                patients.patient_id,
                patients.patient_name,
                patients.patient_sex,
                patients.dob,
                patients.nic,
                patients.emergency_contact_name,
                patients.emergency_contact_relationship,
                patients.emergency_contact_phone,
                patients.emergency_contact_email,
                users.email,
                users.user_phone,
                doctor_availability.available_date,
                doctor_availability.start_time,
                doctor_availability.end_time,
                doctors.hospital
            FROM appointments
            JOIN doctor_availability ON appointments.availability_id = doctor_availability.availability_id
            JOIN patients ON appointments.patient_id = patients.patient_id
            JOIN users ON patients.user_id = users.user_id
            JOIN doctors ON doctor_availability.doctor_id = doctors.doctor_id
            WHERE doctor_availability.doctor_id = ?
            ORDER BY doctor_availability.available_date DESC, doctor_availability.start_time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $booked_appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booked Appointments - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <script src="http://localhost/PRTS/a-booked-appointments.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>

    <body>
        
        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <header>
            <section class="header-left">
                <h1>Dr. <span id="doctor-name">...</span>'s booked appointments</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/doctor-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <br>
            <div class="results-or-list-box widest-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Hospital</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($booked_appointments->num_rows > 0): ?>
                            <?php while ($row = $booked_appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["patient_name"]) ?></td>
                                    <td><?= htmlspecialchars($row["hospital"]) ?></td>
                                    <td><?= htmlspecialchars($row["available_date"]) ?></td>
                                    <td><?= (new DateTime($row["start_time"]))->format("h:i A") ?></td>
                                    <td><?= (new DateTime($row["end_time"]))->format("h:i A") ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row["status"])) ?></td>

                                    <td>
                                        <button class="change-status-button"
                                            data-id="<?= $row['appointment_id'] ?>"
                                            data-status="<?= $row['status'] ?>">
                                            Change Status
                                        </button>
                                        
                                        <button class="notes-btn" 
                                            data-id="<?= $row['appointment_id'] ?>"
                                            data-notes="<?= htmlspecialchars($row['appointment_notes'] ?? '', ENT_QUOTES) ?>">
                                            Notes
                                        </button>

                                        <form method="POST" action="d-view-appointment-files.php" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit">View Files</button>
                                        </form>

                                        <button
                                            onclick="openFileUploadPopup(<?= $row['appointment_id'] ?>, '<?= htmlspecialchars($row['patient_name'], ENT_QUOTES) ?>')">
                                            Add File
                                        </button>

                                        <button
                                            class="view-patient-btn"
                                            data-id="<?= $row['appointment_id'] ?>"
                                            data-patient-id="<?= $row['patient_id'] ?>"
                                            data-name="<?= htmlspecialchars($row['patient_name']) ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"
                                            data-phone="<?= htmlspecialchars($row['user_phone']) ?>"
                                            data-sex="<?= htmlspecialchars($row['patient_sex']) ?>"
                                            data-dob="<?= htmlspecialchars($row['dob']) ?>"
                                            data-nic="<?= htmlspecialchars($row['nic']) ?>"
                                            data-emergency-contact-name="<?= htmlspecialchars($row['emergency_contact_name']) ?>"
                                            data-emergency-contact-relationship="<?= htmlspecialchars($row['emergency_contact_relationship']) ?>"
                                            data-emergency-contact-phone="<?= htmlspecialchars($row['emergency_contact_phone']) ?>"
                                            data-emergency-contact-email="<?= htmlspecialchars($row['emergency_contact_email']) ?>">
                                            View Patient
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No booked appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Change status popup -->
            <div id="change-status-popup" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <form method="POST" action="d-booked-appointments.php">
                        <h3>Change Status</h3>
                        <input type="hidden" name="appointment_id" id="modal-appointment-id">
                        <label class="asterisk-label" for="new_status">Status:</label>
                        <select name="new_status" id="new_status" class="drop-down-select" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <br><br>
                        <button type="submit" name="save_status">Save</button>
                        <button type="button" id="cancel-change-status">Cancel</button>
                    </form>
                </div>
            </div>

             <!-- Notes Popup -->
             <div id="notes-popup" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <h3>Appointment Notes</h3>
                    <form id="notes-form" method="POST" action="d-booked-appointments.php">
                        <input type="hidden" name="appointment_id" id="notes-appointment-id">
                        <textarea name="appointment_notes" id="appointment-notes" maxlength="400" placeholder="Type notes here..."></textarea>
                        
                        <br><br>

                        <button type="submit" name="save_notes">Save</button>
                        <button type="button" id="cancel-notes-button">Cancel</button>
                    </form>
                </div>
            </div>

            <!-- Appointment Files Popup -->
            <div id="fileUploadPopup" class="popup-modal hidden">
                <div class="popup-content wide-box">
                    <div class="popup-body">
                        <h3>Upload Appointment Files for <span id="fileUploadPatientName"></span></h3>
                        <form id="fileUploadForm" action="d-upload-appointment-files.php" method="POST" autocomplete="off" enctype="multipart/form-data">
                            <input type="hidden" name="appointment_id" id="uploadAppointmentRecordId">
                            
                            <br>

                            <div id="appointmentFileInputContainer">
                                <div class="file-upload-block">

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

                                </div>
                            </div>

                            <button type="button" onclick="addAppointmentFileInput()">+ Add Another File</button>
                            <br><br>
                            <button type="submit">Upload</button>
                            <button type="button" onclick="closeFileUploadPopup()">Close</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Patient Details Popup -->
            <div id="patient-popup" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <h2>Patient Details</h2>
                    <p style="text-align: left;" ><strong>Name:</strong> <span id="popup-patient-name"></span></p>
                    <p style="text-align: left;" ><strong>Email:</strong> <span id="popup-patient-email"></span></p>
                    <p style="text-align: left;" ><strong>Phone:</strong> <span id="popup-patient-phone"></span></p>
                    <p style="text-align: left;" ><strong>Sex:</strong> <span id="popup-patient-sex"></span></p>
                    <p style="text-align: left;" ><strong>Date of Birth:</strong> <span id="popup-patient-dob"></span></p>
                    <p style="text-align: left;" ><strong>NIC:</strong> <span id="popup-patient-nic"></span></p>

                    <h2>Emergency Contact Details</h2>
                    <p style="text-align: left;" ><strong>Name:</strong> <span id="popup-patient-emergency-contact-name"></span></p>
                    <p style="text-align: left;" ><strong>Relationship:</strong> <span id="popup-patient-emergency-contact-relationship"></span></p>
                    <p style="text-align: left;" ><strong>Phone:</strong> <span id="popup-patient-emergency-contact-phone"></span></p>
                    <p style="text-align: left;" ><strong>Email:</strong> <span id="popup-patient-emergency-contact-email"></span></p>
                    
                    <form id="medical-records-form" method="POST" action="d-medical-records.php">
                        <input type="hidden" name="patient_id" id="popup-patient-id" />
                        <button type="submit" id="medical-records-button">Medical Records</button>
                    </form>

                    <button id="close-patient-popup">Close</button>
                </div>
            </div>

            <!-- Success/Fail Popup -->
            <div id="popupModal" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <p id="popupText"></p>
                    <button id="popupOkay">Okay</button>
                </div>
            </div>

        </main>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>