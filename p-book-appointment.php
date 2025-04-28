<?php
    session_start();
    require_once "db-php/db.php"; // Ensure you have a file that handles database connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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
        $_SESSION["error_message"] = "Access denied. Please log in as a patient.";
        header("Location: a-login-page.php");
    }

    // Get the patient's ID from the session
    $patient_id = $_SESSION["patient_id"];

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["book_availability_id"])) {
        $availability_id = $_POST["book_availability_id"];

        //Check total number of appointments booked for the slot
        $countAppointmentsSQL = "SELECT COUNT(*) as booked_appointments FROM appointments WHERE availability_id = ?";
        $stmt = $conn->prepare($countAppointmentsSQL);
        $stmt->bind_param("i", $availability_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc()["booked_appointments"];
        $stmt->close();

        // Get max slots from doctor_availability table
        $maxSlotCountSQL = "SELECT max_slots FROM doctor_availability WHERE availability_id = ?";
        $stmt = $conn->prepare($maxSlotCountSQL);
        $stmt->bind_param("i", $availability_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $max = $result->fetch_assoc()["max_slots"];
        $stmt->close();

        // Compare max slots and number of appointments
        if ($current >= $max) {
            $_SESSION["error_message"] = "This appointment slot is fully booked.";
            header("Location: p-book-appointment.php");
            exit();
        }

        // Insert appointment record
        $insertAppointment = "INSERT INTO appointments (patient_id, availability_id) VALUES (?, ?)";
    
        try{
            $stmt = $conn->prepare($insertAppointment);
            $stmt->bind_param("ii", $patient_id, $availability_id);
            $stmt->execute();

            $_SESSION["success_message"] = "Appointment booked successfully!";
            $stmt->close();
            header("Location: p-book-appointment.php");
            exit();

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $_SESSION["error_message"] = "You have already booked this slot.";
            } else {
                $_SESSION["error_message"] = "Error booking appointment: " . $e->getMessage();
            }

            header("Location: p-book-appointment.php");
            exit();
        }
        
    }

    // Fetch values for doctor name, specialization and hospitals from database
    $doctors = $conn->query("SELECT doctor_name FROM doctors");
    $specializations = $conn->query("SELECT DISTINCT specialization FROM doctors");
    $hospitals = $conn->query("SELECT DISTINCT hospital FROM doctors");

    // Filter doctor availability records
    $results = [];
    $params = [];
    $types = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["book_availability_id"])) {
        $doctor_name = $_POST["doctor_name"];
        $specialization = $_POST["specialization"];
        $hospital = $_POST["hospital"];
        $available_date = $_POST["available_date"];
    
    // Get doctor_name from doctors table by matching with doctor_id
    $sql = "SELECT
                doctors.doctor_name,
                doctors.specialization,
                doctors.hospital,
                doctor_availability.availability_id,
                doctor_availability.available_date,
                doctor_availability.start_time,
                doctor_availability.end_time,
                doctor_availability.max_slots,
                COUNT(appointments.appointment_id) AS booked_slots
            FROM doctor_availability
            INNER JOIN doctors ON doctors.doctor_id = doctor_availability.doctor_id
            LEFT JOIN appointments ON doctor_availability.availability_id = appointments.availability_id
            WHERE 1=1";

    // Add filters if set
    if (!empty($doctor_name)) {
        $sql .= " AND doctors.doctor_name LIKE ?";
        $params[] = "%$doctor_name%";
        $types .= "s";
    }
    if (!empty($specialization)) {
        $sql .= " AND doctors.specialization = ?";
        $params[] = $specialization;
        $types .= "s";
    }
    if (!empty($hospital)) {
        $sql .= " AND doctors.hospital = ?";
        $params[] = $hospital;
        $types .= "s";
    }
    if (!empty($available_date)) {
        $sql .= " AND doctor_availability.available_date = ?";
        $params[] = $available_date;
        $types .= "s";
    }

    $sql .= " GROUP BY doctor_availability.available_date DESC";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $results = $stmt->get_result();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Book an Appointment - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>
    
    <body>

        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <header>
            <section class="header-left">
                <h1>Book an appointment!</h1>
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
                <form method="POST" autocomplete="off">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="doctor_name">Doctor</label>
                            <input list="doctor_names" name="doctor_name" id="doctor_name" placeholder="Enter doctor's name">

                            <datalist id="doctor_names">
                                <?php while ($row = $doctors-> fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row["doctor_name"]) ?>"></option>
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="specialization">Special</label>
                            <input list="specializations" name="specialization" id="specialization" placeholder="Enter specialization">

                            <datalist id="specializations">
                                <?php while ($row = $specializations-> fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row["specialization"]) ?>"></option>
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="hospital">Hospital</label>
                            <input list="hospitals" name="hospital" id="hospital" placeholder="Enter hospital">

                            <datalist id="hospitals">
                                <?php while ($row = $hospitals-> fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row["hospital"]) ?>"></option>
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="available_date">Date</label>
                            <input type="date" name="available_date" id="available_date">
                        </div>
                    </div>
                    
                    <div class="centered-button">
                        <button type="submit">Search</button>
                        <button type="button" onclick="window.location.href=window.location.pathname">Reset</button>
                    </div>
                    
                </form>
            </div>
            

            <div class="results-or-list-box wider-table">
                <h2>Available Slots</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialization</th>
                            <th>Hospital</th>
                            <th>Available Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Max Slots</th>
                            <th>Remaining Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results) && $results->num_rows > 0): ?>
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <?php
                                    // Calculate remaining slots
                                    $remainingSlots = $row['max_slots'] - $row['booked_slots'];
                                ?>
                                <tr>
                                    <td>Dr. <?= htmlspecialchars($row['doctor_name']) ?></td>
                                    <td><?= htmlspecialchars($row['specialization']) ?></td>
                                    <td><?= htmlspecialchars($row['hospital']) ?></td>
                                    <td><?= htmlspecialchars($row['available_date']) ?></td>
                                    <td><?= (new DateTime($row["start_time"]))->format("h:i A") ?></td>
                                    <td><?= (new DateTime($row["end_time"]))->format("h:i A") ?></td>
                                    <td><?= htmlspecialchars($row['max_slots']) ?></td>
                                    <td><?= htmlspecialchars($remainingSlots) ?></td>
                                    <td>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="book_availability_id" value="<?= $row['availability_id'] ?>">
                                            <button type="submit">Book</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                            <tr><td colspan="9">No availability found for your search criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <br>
                
        </main>

        <?php if (!empty($popupMessage)): ?>
            <div id="popupModal" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <p id="popupText"></p>
                    <button id="popupOkay">Okay</button>
                </div>
            </div>
        <?php endif; ?>
        
        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>