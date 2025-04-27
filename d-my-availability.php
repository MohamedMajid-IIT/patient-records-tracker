<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";

    // Check if the doctor is logged in
    if (!isset($_SESSION["doctor_id"])) {
        $_SESSION["error_message"] = "Access denied. Please log in as a doctor.";
        header("Location: a-login-page.php");
    }

    // Get the doctor's ID from the session
    $doctor_id = $_SESSION["doctor_id"];

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Delete doctor availability records
        if (isset($_POST["delete_availability"])) {
            $delete_availability = $_POST["delete_availability"];

            $deleteDocAvailability = $conn->prepare("DELETE FROM doctor_availability WHERE availability_id = ?");
            $deleteDocAvailability->bind_param("i", $delete_availability);
            $deleteDocAvailability->execute();
            $deleteDocAvailability->close();
        }

        elseif (
            isset($_POST["available_date"], $_POST["max_slots"], $_POST["start_time"], $_POST["end_time"])
        ) {
            // Get form inputs
            $available_date = $_POST["available_date"];
            $max_slots = $_POST["max_slots"];
            $start_time = $_POST["start_time"];
            $end_time = $_POST["end_time"];    

            // Validate inputs (basic validation)
            if (empty($available_date) || empty($max_slots) || empty($start_time) || empty($end_time)) {
                $_SESSION["error_message"] = "All fields are required.";
                header("Location: d-my-availability.php");
                exit();
            } elseif ($max_slots < 0) {
                $_SESSION["error_message"] = "Max slots must be 1 or more.";
                header("Location: d-my-availability.php");
                exit();
            }

            // Prepare and execute the query
            $insertDocAvailability = $conn->prepare("INSERT INTO doctor_availability (doctor_id, available_date, max_slots, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            $insertDocAvailability->bind_param("isiss", $doctor_id, $available_date, $max_slots, $start_time, $end_time);

            if (!$insertDocAvailability->execute()) {
                echo "Error: " . $insertDocAvailability->error;
            }
            
            $insertDocAvailability->close();
        }
    }
    // Fetch availability records for display
    $selectDocAvailability = $conn->prepare("SELECT 
                                                availability_id,
                                                available_date,
                                                start_time,
                                                max_slots,
                                                end_time
                                            FROM doctor_availability
                                            WHERE doctor_id = ?
                                            ORDER BY available_date DESC, start_time ASC");
    $selectDocAvailability->bind_param("i", $doctor_id);
    $selectDocAvailability->execute();
    $result = $selectDocAvailability->get_result();
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
                <h1>Dr. <span id="doctor-name">...</span>'s availability</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/doctor-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <div class="search-or-create-box">
                <form action="d-my-availability.php" method="POST">
                    <div class="form-grid">

                        <div class="form-group">
                            <label class="asterisk-label" for="date">Date:</label>
                            <input type="date" id="date" name="available_date" required>
                        </div>

                        <div class="form-group">
                            <label class="asterisk-label" for="max-slots">Max slots:</label>
                            <input type="number" id="max-slots" name="max_slots" placeholder="E.g., 25" required>
                        </div>

                        <div class="form-group">
                            <label class="asterisk-label" for="start-time">Start time:</label>
                            <input type="time" id="start-time" name="start_time" required>
                        </div>

                        <div class="form-group">
                            <label class="asterisk-label" for="end-time">End time:</label>
                            <input type="time" id="end-time" name="end_time" required>
                        </div>

                    </div>
                    <div class="centered-button">
                        <button type="submit">Add appointment slot</button>
                    </div>
                    
                </form>
            </div>

            <div class="results-or-list-box">
                <h2>Your availability</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Max Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($result)&& $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["available_date"]) ?></td>
                                    <td><?= (new DateTime($row["start_time"]))->format("h:i A") ?></td>
                                    <td><?= (new DateTime($row["end_time"]))->format("h:i A") ?></td>
                                    <td><?= htmlspecialchars($row["max_slots"]) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_availability" value="<?= $row["availability_id"] ?>">
                                            <button type="submit" class="delete-button" onclick="return confirm('Are you sure?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No availability provided.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        
        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>

        <?php
            $selectDocAvailability->close();
            $conn->close();
        ?>

        <!-- Success/Fail Popup -->
        <div id="popupModal" class="popup-modal hidden">
            <div class="popup-content normal-box">
                <p id="popupText"></p>
                <button id="popupOkay">Okay</button>
            </div>
        </div>

    </body>
</html>