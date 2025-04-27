<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";

    // Ensure doctor is logged in
    if (!isset($_SESSION["doctor_id"])) {
        header("Location: a-login-page.php");
        exit;
    }

    // Store popup message and popup type if available
    $popupMessage = $_SESSION["popupMessage"] ?? "";
    $popupType = $_SESSION["popupType"] ?? "";

    $doctor_id = $_SESSION["doctor_id"];
?>

<?php
    // Get doctor details from "doctors" table and "users" table
    $sql = "SELECT 
                doctors.doctor_name,
                users.email,
                users.user_phone,
                doctors.doctor_sex,
                doctors.specialization,
                doctors.hospital 
            FROM doctors
            INNER JOIN users ON doctors.user_id = users.user_id
            WHERE doctors.doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Doctor Profile - PRTS</title>
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
                <h1>Dr. <span id="doctor-name">...</span>'s profile</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/doctor-dashboard.php'">Return to Dashboard</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-availability.php'">My Availability Slots</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
            <?php if ($doctor): ?>
                <div class="results-or-list-box box-padding narrow-table">
                    <h2>My Profile</h2>
                    <form method="POST" action="a-update-profile.php" autocomplete="off">
                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="doctors_name_field">Doctor Name:</label>
                                <input type="text" id="doctors_name_field" name="doctor_name" value="<?= htmlspecialchars($doctor['doctor_name']) ?>" required>
                            </div>
                            <div class="block">
                                <label class="asterisk-label" for="email_field" >Email:</label>
                                <input type="email" id="email_field" name="email" value="<?= htmlspecialchars($doctor['email']) ?>" required>
                            </div>    
                        </div>

                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="specialization_field">Specialization:</label>
                                <input type="text" id="specialization_field" name="specialization" value="<?= htmlspecialchars($doctor['specialization']) ?>" required>
                            </div>

                            <div class="block">
                                <label class="asterisk-label" for="hospital_field">Hospital:</label>
                                <input type="text" id="hospital_field" name="hospital" value="<?= htmlspecialchars($doctor['hospital']) ?>" required>
                            </div>
                        </div>

                        <div class="big-block">
                            <div class="block">
                                <label class="asterisk-label" for="phone_field">Phone:</label>
                                <input type="text" id="phone_field" name="user_phone" maxlength="10" value="<?= htmlspecialchars($doctor['user_phone']) ?>" required>
                            </div>

                            <div class="block">
                                <label  class="asterisk-label">Sex:</label>
                                <select name="doctor_sex" class="drop-down-select" required>
                                    <option value="male" <?= $doctor['doctor_sex'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $doctor['doctor_sex'] === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="user_type" value="doctor">
                        
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
                            <label class="asterisk-label" for="confirm_new_password_field">Confirm New Password:</label>
                            <input type="password" id="confirm_new_password_field" name="confirm_password" required><br><br>
                        </div>

                        <input type="hidden" name="user_type" value="doctor">
                        
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

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>
    </body>
</html>