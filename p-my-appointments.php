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

    if (isset($_SESSION["cancel_message"])) {
        $popupMessage = $_SESSION["cancel_message"];
        $popupType = "cancel";
        unset($_SESSION["cancel_message"]);
    }
?>

<?php
    // Check if the patient is logged in
    if (!isset($_SESSION["patient_id"])) {
        die("Access denied. Please log in as a patient.");
    }

    // Get the patient's ID from the session
    $patient_id = $_SESSION["patient_id"];

    // Appointment cancellation
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["appointment_id"])) {
        $appointment_id = $_POST["appointment_id"];

        $deleteQuery = "DELETE FROM appointments WHERE appointment_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            $_SESSION["cancel_message"] = "Appointment has been cancelled.";
        } else {
            $_SESSION["error_message"] = "Failed to cancel the appointment.";
        }

        header("Location: p-my-appointments.php");
        exit();
    }

    // Get all appointments for the logged in patient
    $appointmentQuery = "SELECT
                            appointments.appointment_id,
                            appointments.status,
                            appointments.appointment_notes,
                            doctors.doctor_name,
                            doctors.specialization,
                            doctors.hospital,
                            doctor_availability.available_date,
                            doctor_availability.start_time,
                            doctor_availability.end_time
                        FROM appointments
                        JOIN doctor_availability ON appointments.availability_id = doctor_availability.availability_id
                        JOIN doctors ON doctor_availability.doctor_id = doctors.doctor_id
                        WHERE appointments.patient_id = ?
                        ORDER BY doctor_availability.available_date ASC, doctor_availability.start_time ASC";

    $stmt = $conn->prepare($appointmentQuery);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <script src="http://localhost/PRTS/a-booked-appointments.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>

    <script>
        const popupMessage = <?= json_encode($popupMessage) ?>;
        const popupType = <?= json_encode($popupType) ?>;
    </script>

    <body>
        <header>
            <section class="header-left">
                <h1>My appointments</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/patient-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <br>
            <div class="results-or-list-box widest-table">
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialization</th>
                            <th>Hospital</th>
                            <th>Appointment Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments) && $appointments->num_rows > 0): ?>
                            <?php while ($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td>Dr. <?= htmlspecialchars($row["doctor_name"]) ?></td>
                                    <td><?= htmlspecialchars($row["specialization"]) ?></td>
                                    <td><?= htmlspecialchars($row["hospital"]) ?></td>
                                    <td><?= htmlspecialchars($row["available_date"]) ?></td>
                                    <td><?= (new DateTime($row["start_time"]))->format("h:i A") ?></td>
                                    <td><?= (new DateTime($row["end_time"]))->format("h:i A") ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row["status"])) ?></td>
                                    <td>
                                        <button class="notes-btn"
                                            data-id="<?= $row['appointment_id'] ?>"
                                            data-notes="<?= htmlspecialchars($row['appointment_notes'] ?? 'No notes available.', ENT_QUOTES) ?>">
                                            Notes
                                        </button>

                                        <form method="POST" action="p-my-appointment-files.php" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit">View Files</button>
                                        </form>
                                        
                                        <button type="button" class="delete-button cancel-trigger" data-appointment-id="<?= $row['appointment_id'] ?>">
                                            Cancel
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Cancel Appointment Popup -->
        <div id="cancelModal" class="popup-modal cancel hidden">
            <div class="popup-content normal-box">
                <form id="cancelForm" method="POST" action="p-my-appointments.php" style="display: none;">
                    <input type="hidden" name="appointment_id" id="cancelAppointmentId">
                </form>
                <p>Are you sure you want to cancel this appointment?</p>
                    <button id="confirmCancel" class="delete-button">Confirm</button>
                    <button id="dismissCancel" >Cancel</button>
            </div>
        </div>

        <!-- Notes Popup for Patients -->
        <div id="notes-popup" class="popup-modal hidden">
            <div class="popup-content normal-box">
                <h3>Doctor's Notes</h3>
                <textarea id="appointment-notes" maxlength="400" readonly></textarea>
                <button type="button" id="okay-notes-button">Okay</button>
            </div>
        </div>

 
        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>