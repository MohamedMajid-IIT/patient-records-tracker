<?php
    session_start();
    require_once "db-php/db.php";

    // Ensure patient is logged in
    if (!isset($_SESSION["patient_id"])) {
        header("Location: a-login-page.php");
        exit;
    }

    // Store popup message and popup type if available
    $popupMessage = $_SESSION["popupMessage"] ?? "";
    $popupType = $_SESSION["popupType"] ?? "";

    $patient_id = $_SESSION["patient_id"];
?>

<?php
    // Get patient details from "patients" table and "users" table
    $sql = "SELECT 
                patients.patient_name,
                users.email,
                users.user_phone,
                patients.patient_sex,
                patients.dob,
                patients.nic,
                patients.emergency_contact_name,
                patients.emergency_contact_email,
                patients.emergency_contact_phone,
                patients.emergency_contact_relationship
            FROM patients
            INNER JOIN users ON patients.user_id = users.user_id
            WHERE patients.patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Patient Profile - PRTS</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <link rel="stylesheet" href="./style-sheets/book-appointments.css">
    </head>

    <body>

        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <?php
            unset($_SESSION["popupMessage"]);
            unset($_SESSION["popupType"]);
        ?>

        <header>
            <section class="header-left">
                <h1><span id="patient-name">...</span>'s profile</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/patient-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-appointments.php'">My Appointments</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <?php if ($patient): ?>
                <div class="results-or-list-box box-padding narrow-table">
                    <h2>My Profile</h2>
                    <form method="POST" action="a-update-profile.php" autocomplete="off">
                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="patient_name_field">Patient Name:</label>
                                <input type="text" id="patient_name_field" name="patient_name" value="<?= htmlspecialchars($patient['patient_name']) ?>" required>
                            </div>
                            <div class="block">
                                <label class="asterisk-label" for="email_field">Email:</label>
                                <input type="email" id="email_field" name="email" value="<?= htmlspecialchars($patient['email']) ?>" required>
                            </div>    
                        </div>

                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="phone_field">Phone:</label>
                                <input type="tel" id="phone_field" name="user_phone" maxlength="10" value="<?= htmlspecialchars($patient['user_phone']) ?>" required>
                            </div>

                            <div class="block">
                                <label class="asterisk-label" for="nic_field">NIC:</label>
                                <input type="text" id="nic_field" name="nic" maxlength="12" value="<?= htmlspecialchars($patient['nic']) ?>" required>
                            </div>
                        </div>

                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="dob_field">DoB:</label>
                                <input type="date" id="dob_field" name="dob" value="<?= htmlspecialchars($patient['dob']) ?>" required>
                            </div>

                            <div class="block">
                                <label class="asterisk-label">Sex:</label>
                                <select name="patient_sex" class="drop-down-select" required>
                                    <option value="male" <?= $patient['patient_sex'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $patient['patient_sex'] === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>

                        <h2>Emergency Contact Information</h2>

                        <div class="big-block">
                            <div class="block">
                                <label for="emergency_contact_name_field">Name:</label>
                                <input type="text" id="emergency_contact_name_field" name="emergency_contact_name" value="<?= htmlspecialchars($patient['emergency_contact_name']) ?>">
                            </div>

                            <div class="block">
                                <label for="emergency_contact_email_field">Email:</label>
                                <input type="email" id="emergency_contact_email_field" name="emergency_contact_email" value="<?= htmlspecialchars($patient['emergency_contact_email']) ?>">
                            </div>   
                        </div>

                        <div class="big-block">
                            <div class="block">
                                <label for="emergency_contact_relationship_field">Relationship:</label>
                                <select name="emergency_contact_relationship" class="drop-down-select">
                                <option value="" <?= !isset($patient['emergency_contact_relationship']) || $patient['emergency_contact_relationship'] === null || $patient['emergency_contact_relationship'] === '' ? 'selected' : '' ?>>
                                    Select Relationship
                                </option>

                                    <option value="Father" <?= $patient['emergency_contact_relationship'] === 'Father' ? 'selected' : '' ?>>Father</option>
                                    <option value="Mother" <?= $patient['emergency_contact_relationship'] === 'Mother' ? 'selected' : '' ?>>Mother</option>
                                    <option value="Husband" <?= $patient['emergency_contact_relationship'] === 'Husband' ? 'selected' : '' ?>>Husband</option>
                                    <option value="Wife" <?= $patient['emergency_contact_relationship'] === 'Wife' ? 'selected' : '' ?>>Wife</option>
                                    <option value="Partner" <?= $patient['emergency_contact_relationship'] === 'Partner' ? 'selected' : '' ?>>Partner</option>
                                    <option value="Uncle" <?= $patient['emergency_contact_relationship'] === 'Uncle' ? 'selected' : '' ?>>Uncle</option>
                                    <option value="Aunt" <?= $patient['emergency_contact_relationship'] === 'Aunt' ? 'selected' : '' ?>>Aunt</option>
                                    <option value="Grandfather" <?= $patient['emergency_contact_relationship'] === 'Grandfather' ? 'selected' : '' ?>>Grandfather</option>
                                    <option value="Grandmother" <?= $patient['emergency_contact_relationship'] === 'Grandmother' ? 'selected' : '' ?>>Grandmother</option>
                                    <option value="Brother" <?= $patient['emergency_contact_relationship'] === 'Brother' ? 'selected' : '' ?>>Brother</option>
                                    <option value="Sister" <?= $patient['emergency_contact_relationship'] === 'Sister' ? 'selected' : '' ?>>Sister</option>
                                    <option value="Cousin" <?= $patient['emergency_contact_relationship'] === 'Cousin' ? 'selected' : '' ?>>Cousin</option>
                                    <option value="Friend" <?= $patient['emergency_contact_relationship'] === 'Friend' ? 'selected' : '' ?>>Friend</option>
                                    <option value="Neighbour" <?= $patient['emergency_contact_relationship'] === 'Neighbour' ? 'selected' : '' ?>>Neighbour</option>
                                    <option value="Other" <?= $patient['emergency_contact_relationship'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>  
                               
                            <div class="block">
                                <label for="emergency_contact_phone_field">Phone:</label>
                                <input type="tel" id="emergency_contact_phone_field" name="emergency_contact_phone" maxlength="10" value="<?= htmlspecialchars($patient['emergency_contact_phone']) ?>">
                            </div>   
                        </div>

                        <input type="hidden" name="user_type" value="patient">
                        
                        <div class="centered">
                        <button type="submit">Update Profile</button>
                        </div>
                    </form>
                </div>

                <div class="results-or-list-box box-padding narrow-table">
                    <h2>Change Password</h2>
                    <form method="POST" action="a-change-password.php" autocomplete="off">
                        
                            <div class="block">
                                <label class="asterisk-label" for="current_password_field">Current Password:</label>
                                <input type="password" id="current_password_field" name="current_password" required><br><br>
                            </div>
                            <div class="block">
                                <label class="asterisk-label" for="new_password_field">New Password:</label>
                                <input type="password" id="new_password_field" name="new_password" required><br><br>
                            </div>
                            <div class="block">
                                <label class="asterisk-label" for="confirm_password_field">Confirm New Password:</label>
                                <input type="password" id="confirm_password_field" name="confirm_password" required><br><br>
                            </div>
                        
                        <input type="hidden" name="user_type" value="patient">

                        <div class="centered">
                            <button type="submit">Change Password</button>
                        </div>
                    </form>
                </div>
                <br>
            <?php else: ?>
                <p>Unable to load profile details.</p>
            <?php endif; ?>

            <!-- Success or Fail Popup -->
            <div id="popupModal" class="popup-modal hidden">
                <div class="popup-content normal-box">
                    <p id="popupText"></p>
                    <button id="popupOkay">Okay</button>
                </div>
            </div>

        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
            // Select ALL input elements with type="tel"
            const phoneInput = document.querySelectorAll('input[type="tel"]');

            // Checking if any elements with type="tel" exist
            if (phoneInput.length > 0) {
                phoneInput.forEach(function(inputElement) {
                    // Event listener for the current element in the loop
                    inputElement.addEventListener('keydown', function(event) {
                        // This is to get the key that was pressed
                        const key = event.key;

                        // Allowed keys, digits, symbols, control buttons
                        const allowedControlKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                        const isDigit = key >= '0' && key <= '9';
                        const isAllowedControlKey = allowedControlKeys.includes(key);
                        const isModifierKeyPressed = event.ctrlKey || event.metaKey; // e.g: Ctrl+C, Ctrl+V

                        // If the key or key combination is NOT in the allowed list then prevent input
                        if (!(isDigit || isAllowedControlKey || isModifierKeyPressed)) {
                            event.preventDefault();
                        }
                    });
                }); 
            }
        });
        </script>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>