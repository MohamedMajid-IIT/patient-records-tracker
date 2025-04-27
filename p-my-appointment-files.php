<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";

    if (!isset($_SESSION["patient_id"])) {
        header("Location: a-login-page.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["appointment_id"])) {
        $_SESSION["error_message"] = "Invalid request.";
        header("Location: p-my-appointments.php");
        exit();
    }

    $patient_id = $_SESSION["patient_id"];
    $appointment_id = $_POST["appointment_id"];

    // Select appointment_notes that belongs to the appointment and loged in patient
    $stmt = $conn->prepare("SELECT appointment_notes FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $stmt->bind_result($appointment_notes);
    if (!$stmt->fetch()) {
        $stmt->close();
        $_SESSION["error_message"] = "Appointment not found or access denied.";
        header("Location: p-my-appointments.php");
        exit();
    }
    $stmt->close();

    // Fetch the name of doctor for the appointment
    $stmt = $conn->prepare("SELECT doctors.doctor_name
                            FROM doctors
                            JOIN doctor_availability ON doctors.doctor_id = doctor_availability.doctor_id
                            JOIN appointments ON appointments.availability_id = doctor_availability.availability_id
                            WHERE appointments.appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->bind_result($doctor_name);
    $stmt->fetch();
    $stmt->close();

    // Fetch the appointment_files for this appointment
    $stmt = $conn->prepare("SELECT appointment_file_name, appointment_file_path, appointment_file_type, appointment_file_created_at 
                            FROM appointment_files
                            WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointment_files = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Files - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>

        <script src="http://localhost/PRTS/dashboard.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>
    <body>

        <header>
            <section class="header-left">
                <h1>Files from Dr. <?= htmlspecialchars($doctor_name) ?></h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='p-my-appointments.php'">Back to Appointments</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
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

                                        <a href="<?= htmlspecialchars($file['appointment_file_path']) ?>" download>
                                            <button>Download</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No files uploaded for this appointment yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

    </body>
</html>
