<?php
    session_start();
    require_once "db-php/db.php";
    
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Ensures that the new password and confirm password are the same
    if ($new_password !== $confirm_password) {
        $_SESSION["popupMessage"] = "New passwords do not match.";
        $_SESSION["popupType"] = "error";
    
        if (isset($_SESSION["doctor_id"])) {
            header("Location: d-my-profile.php");
        } elseif (isset($_SESSION["patient_id"])) {
            header("Location: p-my-profile.php");
        } else {
            header("Location: a-login-page.php");
        }
        exit;
    }

    if (isset($_SESSION["doctor_id"])) {
        $doctor_id = $_SESSION["doctor_id"];
    
        $sql = "SELECT users.user_id, users.user_password FROM users 
                INNER JOIN doctors ON users.user_id = doctors.user_id
                WHERE doctors.doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    
        $redirect = "d-my-profile.php";
    
    } elseif (isset($_SESSION["patient_id"])) {
        $patient_id = $_SESSION["patient_id"];
    
        $sql = "SELECT users.user_id, users.user_password FROM users 
                INNER JOIN patients ON users.user_id = patients.user_id
                WHERE patients.patient_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    
        $redirect = "p-my-profile.php";
    
    } else {
        $_SESSION["popupMessage"] = "Unauthorized access.";
        $_SESSION["popupType"] = "error";
        header("Location: a-login-page.php");
        exit;
    }
    
    // If user is found and entered password is matching current password
    if ($user && $user["user_password"] === $current_password) {
        $stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_password, $user["user_id"]);
        $stmt->execute();
    
        $_SESSION["popupMessage"] = "Password updated successfully.";
        $_SESSION["popupType"] = "success";
    } else {
        $_SESSION["popupMessage"] = "Current password is incorrect.";
        $_SESSION["popupType"] = "error";
    }
    
    header("Location: $redirect");
    exit;
?>