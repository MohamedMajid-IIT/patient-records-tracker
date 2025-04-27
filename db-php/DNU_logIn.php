<?php
session_start();

include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Fetch user details from the 'users' table
    $query = $conn->prepare("SELECT user_id, user_password, role FROM users WHERE email = ?");
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
                $query = $conn->prepare("SELECT patient_name FROM patients WHERE user_id = ?");
                $query->bind_param("i", $user_id);
                $query->execute();
                $query->bind_result($name);
                $query->fetch();
                $_SESSION["name"] = $name;

                // Redirect to patient dashboard
                header("Location: http://localhost/PRTS/patient-dashboard.html");
                exit();
            } elseif ($role === "doctor") {
                $query = $conn->prepare("SELECT doctor_name FROM doctors WHERE user_id = ?");
                $query->bind_param("i", $user_id);
                $query->execute();
                $query->bind_result($name);
                $query->fetch();
                $_SESSION["name"] = $name;

                // Redirect to doctor dashboard
                header("Location: http://localhost/PRTS/doctor-dashboard.html");
                exit();
            }
        } else {
            echo "<script>alert('Invalid email or password'); window.location.href='http://localhost/PRTS/login-page.html';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='http://localhost/PRTS/login-page.html';</script>";
    }

    $query->close();
    $conn->close();
}
?>
