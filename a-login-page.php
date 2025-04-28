<?php
    session_start();
    require_once "db-php/db.php";

    if (isset($_POST["signup_submit"])) {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $phone = trim($_POST["phone"]);
        $sex = $_POST["sex"];
        $password = $_POST["password"];
        $role = $_POST["role"];

        // Start a transaction
        $conn->begin_transaction(); 

        try {
            // Insert user into 'users' table
            $insertUser = $conn->prepare("INSERT INTO users (email, user_phone, user_password, role) VALUES (?, ?, ?, ?)");
            $insertUser->bind_param("ssss", $email, $phone, $password, $role);
            $insertUser->execute();
            $user_id = $conn->insert_id; // Get the inserted user's ID

            if ($role === "patient") {

                // Insert patient's details into the "patients" table
                $nic = trim($_POST["nic"]);
                $insertPatient = $conn->prepare("INSERT INTO patients(user_id, patient_name, patient_sex, nic) VALUES (?, ?, ?, ?)");
                $insertPatient->bind_param("isss", $user_id, $name, $sex, $nic);
                $insertPatient->execute();
                $insertPatient->close();
            }

            elseif ($role === "doctor") {

                // Insert doctor's details into the "doctors" table
                $specialization = trim($_POST["specialization"]);
                $hospital = trim($_POST["hospital"]);
                $insertDoctor = $conn->prepare("INSERT INTO doctors(user_id, doctor_name, doctor_sex, specialization, hospital) VALUES (?, ?, ?, ?, ?)");
                $insertDoctor->bind_param("issss", $user_id, $name, $sex, $specialization, $hospital);
                $insertDoctor->execute();
                $insertDoctor->close();
            }

            // Commit transaction
            $conn->commit();
            $_SESSION["success_message"] = "Account registered successfully!";
            header("Location: a-login-page.php");
            
            // Close statements and connection
            $insertUser->close();
            $conn->close();
            
            exit();
            

        } catch (Exception $e) {
            
            $_SESSION["error_message"] = "Error: " . $e->getMessage();
            header("Location: a-login-page.php");
            
            // Rollback on error
            $conn->rollback();

            // Close statements and connection
            $insertUser->close();
            $conn->close();

            exit();
        }

        
    }
    elseif (isset($_POST["login_submit"])) {
        
        
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Fetch user details from the 'users' table
        $query = $conn->prepare("SELECT
                                    user_id,
                                    user_password,
                                    role
                                FROM users
                                WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            $query->bind_result($user_id, $user_password, $role);
            $query->fetch();

            // Verify password against the database
            if ($password === $user_password) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["role"] = $role;

                // Fetch name from the respective table based on role
                if ($role === "patient") {
                    $query = $conn->prepare("SELECT patient_id, patient_name FROM patients WHERE user_id = ?");
                    $query->bind_param("i", $user_id);
                    $query->execute();
                    $query->bind_result($patient_id, $name);
                    $query->fetch();

                    $_SESSION["name"] = $name;
                    $_SESSION["patient_id"] = $patient_id;

                    // Redirect to patient dashboard
                    header("Location: http://localhost/PRTS/patient-dashboard.php");
                    exit();
                } elseif ($role === "doctor") {
                    $query = $conn->prepare("SELECT doctor_id, doctor_name FROM doctors WHERE user_id = ?");
                    $query->bind_param("i", $user_id);
                    $query->execute();
                    $query->bind_result($doctor_id, $name);
                    $query->fetch();

                    $_SESSION["name"] = $name;
                    $_SESSION["doctor_id"] = $doctor_id;

                    // Redirect to doctor dashboard
                    header("Location: http://localhost/PRTS/doctor-dashboard.php");
                    exit();
                }
            } else {
                $_SESSION["error_message"] = "Invalid email or password.";
                header("Location: a-login-page.php");
                exit();
                
            }
        } else {
            $_SESSION["error_message"] = "User not found.";
                header("Location: a-login-page.php");
                exit();
        }

        $query->close();
        $conn->close();
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

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PRTS - Home</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/login-popup.js"></script>
        
        <link rel="stylesheet" href="./style-sheets/login-styles.css">
    </head>


    <body>
        <script>
            const popupMessage = <?= json_encode($popupMessage) ?>;
            const popupType = <?= json_encode($popupType) ?>;
        </script>

        <header>
                <h1>Welcome to the Patient Records Tracker System!</h1>    
        </header>

        <div class="body-section">
            <div class="container">
                <div class="left-section">
                    <h2>Continue as patient</h2>
                    <button onclick="togglePopup('patientSignUp')">Register</button>
                    <button onclick="togglePopup('patientLogIn')">Log in</button>
                </div>
                <div class="right-section">
                    <h2>Continue as doctor</h2>
                    <button onclick="togglePopup('doctorSignUp')">Register</button>
                    <button onclick="togglePopup('doctorLogIn')">Log in</button>
                </div>
            </div>
        </div>

        <div id="patientSignUp" class="popup">
            <div class="popup-content" style="background-color: #e8ecfc">
                <div class="popup-body">  
                    <h2 style="color: #8aa1ff;">Patient Sign Up</h2>
                        <form class="form-container" action="a-login-page.php" method="POST" autocomplete="off">
                            
                        
                        <input type="hidden" name="role" value="patient">

                            <label class="form-label" for="name">
                                Name:
                            <input class="form-input" type="text" placeholder="Enter your name" id="name" name="name" required>
                            </label>

                            <label class="form-label" for="email">
                                Email:
                            <input class="form-input" type="email" placeholder="Enter your email" id="email" name="email" required>
                            </label>

                            <label class="form-label" for="phone" >
                                Phone:
                            <input class="form-input" type="tel" placeholder="Enter your phone number" id="phone" name="phone" pattern="^[0-9]+$" maxlength="10" title="Please enter only numbers from 0-9" required>
                            </label>

                            <label class="form-label" for="nic">
                                NIC / Passport:
                            <input class="form-input" type="text" placeholder="Enter your NIC or passport number" id="nic" name="nic" maxlength="12" required>
                            </label>

                            <label class="form-label">Sex:</label>
                            <div class="radio-group">
                                <input type="radio" id="male" name="sex" value="Male" required>
                                <label for="male">Male</label>
                            
                                <input type="radio" id="female" name="sex" value="Female">
                                <label for="female">Female</label>
                            </div>

                            <label class="form-label" for="password">
                                Password:
                            <input class="form-input" type="password" placeholder="Enter your password" id="password" name="password" required>
                            </label>

                            <button class="btn-submit" type="submit" name="signup_submit">
                                Create Account
                            </button>
                        </form>

                    <button onclick="togglePopup('patientSignUp')">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div id="patientLogIn" class="popup">
            <div class="popup-content" style="background-color: #e8ecfc">
                <h2 style="color: #8aa1ff;">Patient Log in</h2>
                    <form class="form-container" action="a-login-page.php" method="POST" autocomplete="off">

                        <label class="form-label" for="email">
                            Email:
                        <input class="form-input" type="email" placeholder="Enter your email" id="email" name="email" required>
                        </label>

                        <label class="form-label" for="password">
                            Password:
                        <input class="form-input" type="password" placeholder="Enter your password" id="password" name="password" required>
                        </label>

                        <button class="btn-submit" type="submit" name="login_submit">
                            Login
                        </button>
                    </form>

                <button onclick="togglePopup('patientLogIn')">
                    Close
                </button>
            </div>
        </div>

        <div id="doctorSignUp" class="popup">
            <div class="popup-content" style="background-color: #e8ecfc">
                <div class="popup-body"> 
                    <h2 style="color: #8aa1ff;">Doctor Sign Up</h2>
                        <form class="form-container" action="a-login-page.php" method="POST" autocomplete="off">
                            <input type="hidden" name="role" value="doctor">

                            <label class="form-label" for="name">
                                Name:
                            <input class="form-input" type="text" placeholder="Enter your name" id="name" name="name" required>
                            </label>

                            <label class="form-label" for="email">
                                Email:
                            <input class="form-input" type="email" placeholder="Enter your email" id="email" name="email" required>
                            </label>

                            <label class="form-label" for="phone" >
                                Phone:
                            <input class="form-input" type="tel" placeholder="Enter your phone number" id="phone" name="phone" pattern="^[0-9]+$" maxlength="10" title="Please enter only numbers from 0-9" required>
                            </label>

                            <label class="form-label">Sex:</label>
                            <div class="radio-group">
                                <input type="radio" id="male" name="sex" value="Male" required>
                                <label for="male">Male</label>
                            
                                <input type="radio" id="female" name="sex" value="Female">
                                <label for="female">Female</label>
                            </div>

                            <label class="form-label" for="specialization"> 
                                Specialization:
                                <input type="text" class="form-input" placeholder="Enter your specialization" id="specialization" name="specialization" required> 
                            </label>
                        
                            <label class="form-label" for="hospital"> 
                                Hospital:
                                <input type="text" class="form-input" placeholder="Enter the hospital name" id="hospital" name="hospital" required> 
                            </label>

                            <label class="form-label" for="password">
                                Password:
                            <input class="form-input" type="password" placeholder="Enter your password" id="password" name="password" required>
                            </label>

                            <button class="btn-submit" type="submit" name="signup_submit">
                                Create Account
                            </button>
                        </form>

                    <button onclick="togglePopup('doctorSignUp')">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div id="doctorLogIn" class="popup">
            <div class="popup-content" style="background-color: #e8ecfc">
                <h2 style="color: #8aa1ff;">Doctor Log in</h2>
                <form class="form-container" action="a-login-page.php" method="POST" autocomplete="off">
                    <label class="form-label" for="email">
                        Email:
                    <input class="form-input" type="email" placeholder="Enter your email" id="email" name="email" required>
                    </label>

                    <label class="form-label" for="password">
                        Password:
                    <input class="form-input" type="password" placeholder="Enter your password" id="password" name="password" required>
                    </label>

                    <button class="btn-submit" type="submit" name="login_submit">
                        Login
                    </button>
                </form>

                <button onclick="togglePopup('doctorLogIn')">
                    Close
                </button>
            </div>
        </div>

        <!-- Success/Fail Popup -->
        <div id="popupModal" class="popup-modal hidden">
            <div class="popup-content normal-box">
                <p id="popupText"></p>
                <button id="popupOkay">Okay</button>
            </div>
        </div>


        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>

    </body>
</html>