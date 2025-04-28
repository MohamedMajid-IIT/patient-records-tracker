<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";

    if (!isset($_SESSION["doctor_id"])) {
        header("Location: a-login-page.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["appointment_id"])) {
        $_SESSION["error_message"] = "Invalid request.";
        header("Location: d-booked-appointments.php");
        exit();
    }

    $doctor_id = $_SESSION["doctor_id"];
    $appointment_id = $_POST["appointment_id"];

    // Fetch appointment note
    $stmt = $conn->prepare("SELECT
                                patient_id,
                                appointment_notes 
                            FROM appointments 
                            WHERE appointment_id = ? AND appointment_id IN ( SELECT 
                                                                                appointment_id 
                                                                            FROM doctor_availability 
                                                                            JOIN appointments ON appointments.availability_id = doctor_availability.availability_id 
                                                                            WHERE doctor_availability.doctor_id = ?)");
    $stmt->bind_param("ii", $appointment_id, $doctor_id);
    $stmt->execute();
    $stmt->bind_result($patient_id, $appointment_notes);
    $stmt->fetch();
    $stmt->close();

    // Fetch patient name
    $stmt = $conn->prepare("SELECT patient_name
                            FROM patients
                            WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->bind_result($patient_name);
    $stmt->fetch();
    $stmt->close();

    // Fetch appointment files
    $stmt = $conn->prepare("SELECT appointment_file_id, appointment_file_name, appointment_file_path, appointment_file_type, appointment_file_created_at 
                            FROM appointment_files
                            WHERE appointment_id = ? AND appointment_file_doctor_id = ?");
    $stmt->bind_param("ii", $appointment_id, $doctor_id);
    $stmt->execute();
    $appointment_files = $stmt->get_result();
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

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Files - PRTS</title>
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
                <h1><?= htmlspecialchars($patient_name) ?>'s Appointment Files</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='d-booked-appointments.php'">Back to Appointments</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <div class="search-or-create-box small-height">
                <p><em>Welcome Dr. <span id="doctor-name">...</span>! You are now viewing <?= htmlspecialchars($patient_name) ?>'s appointment files.</em></p>
            </div>
            
            
            <div class="search-or-create-box">
                <textarea class="rounded-textarea" maxlength="400" readonly><?= 
                    htmlspecialchars($appointment_notes ?: 'No notes provided.') ?>
                </textarea>
            </div>
        
            <div class="results-or-list-box wide-table">
                    
                <h2>Appointment Files</h2>
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointment_files->num_rows > 0): ?>
                            <?php while ($file = $appointment_files->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($file["appointment_file_name"]) ?></td>
                                    <td><?= htmlspecialchars($file["appointment_file_type"]) ?></td>
                                    <td><?= (new DateTime($file['appointment_file_created_at']))->format("d-m-Y h:i A") ?></td>
                                    
                                    <td>
                                        <button onclick="window.open('<?= htmlspecialchars($file['appointment_file_path']) ?>', '_blank')">View</button>

                                        <a href="<?= htmlspecialchars($file['appointment_file_path']) ?>" download><button>Download</button></a>
                                        
                                        <button type="button" class="delete-button delete-trigger" data-file-id="<?= $file['appointment_file_id'] ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No files uploaded for this appointment.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteFilePopup" class="popup-modal cancel hidden">
                <div class="popup-content normal-box">
                    <p>Are you sure you want to delete this file?</p>
                    <form method="POST" action="d-delete-appointment-file.php" id="deleteForm">
                        <input type="hidden" name="appointment_file_id" id="deleteFileId">
                        <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">
                        <button type="submit" class="delete-button" id="confirmDelete">Delete</button>
                        <button type="button" id="dismissDelete">Cancel</button>
                    </form>
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
    </body>
</html>