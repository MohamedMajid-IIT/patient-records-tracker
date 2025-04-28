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
    $sql = "SELECT DISTINCT 
            patients.patient_id,
            patients.patient_name, 
            users.email,
            users.user_phone,
            patients.patient_sex, 
            patients.dob, 
            patients.nic,
            patients.emergency_contact_name,
            patients.emergency_contact_relationship,
            patients.emergency_contact_phone,
            patients.emergency_contact_email
        FROM patients
        INNER JOIN appointments ON patients.patient_id = appointments.patient_id
        INNER JOIN doctor_availability ON appointments.availability_id = doctor_availability.availability_id
        INNER JOIN users ON patients.user_id = users.user_id
        WHERE doctor_availability.doctor_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $my_patients = $stmt->get_result();
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Patients - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <script src="http://localhost/PRTS/d-my-patients.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>

    <body>

        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <header>
            <section class="header-left">
                <h1>Dr. <span id="doctor-name">...</span>'s patients</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/doctor-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-availability.php'">My Availability Slots</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <br>
            <div class="results-or-list-box wide-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Sex</th>
                            <th>DoB</th>
                            <th>NIC</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($my_patients->num_rows > 0): ?>
                            <?php while ($row = $my_patients->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["patient_name"]) ?></td>
                                    <td><?= htmlspecialchars($row["email"]) ?></td>
                                    <td><?= htmlspecialchars($row["user_phone"]) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row["patient_sex"])) ?></td>
                                    <td><?= htmlspecialchars($row["dob"]) ?></td>
                                    <td><?= htmlspecialchars($row["nic"]) ?></td>
                                    <td>
                                        <form action="d-medical-records.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="patient_id" value="<?= $row['patient_id'] ?>">
                                            <button type="submit" class="medical-records-button">Medical Records</button>
                                        </form>
                                        <button
                                            class="view-patient-btn"
                                            data-patient-id="<?= $row['patient_id'] ?>"
                                            data-emergency-contact-name="<?= htmlspecialchars($row['emergency_contact_name']) ?>"
                                            data-emergency-contact-relationship="<?= htmlspecialchars($row['emergency_contact_relationship']) ?>"
                                            data-emergency-contact-phone="<?= htmlspecialchars($row['emergency_contact_phone']) ?>"
                                            data-emergency-contact-email="<?= htmlspecialchars($row['emergency_contact_email']) ?>">
                                            Emergency Contact
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No patients found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Patient Details Popup -->
            <div id="patient-popup" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <h2>Emergency Contact Details</h2>
                    <p style="text-align: left;" ><strong>Name:</strong> <span id="popup-patient-emergency-contact-name"></span></p>
                    <p style="text-align: left;" ><strong>Relationship:</strong> <span id="popup-patient-emergency-contact-relationship"></span></p>
                    <p style="text-align: left;" ><strong>Phone:</strong> <span id="popup-patient-emergency-contact-phone"></span></p>
                    <p style="text-align: left;" ><strong>Email:</strong> <span id="popup-patient-emergency-contact-email"></span></p>

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