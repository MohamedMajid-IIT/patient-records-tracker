<?php
session_start();
require_once "db-php/db.php";

// Determine who is logged in
if (isset($_SESSION["patient_id"])) {
    $patient_id = $_SESSION["patient_id"];
} elseif (isset($_SESSION["doctor_id"]) && isset($_POST["patient_id"])) {
    $patient_id = intval($_POST["patient_id"]);
} else {
    $_SESSION["error_message"] = "Access denied. The necessary patient information is not available to process your request.";
    if (isset($_SESSION["doctor_id"])) {
        header("Location: d-medical-records.php");
    } else {
        header("Location: p-my-medical-records.php");
    }
    exit();
}

//Get title and description for medical record from POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["medical_record_title"]);
    $description = trim($_POST["medical_record_description"]);

    //Check if title and description are both available
    if (empty($title) || empty($description)) {
        $_SESSION["error_message"] = "Both title and description are required.";
        if (isset($_SESSION["doctor_id"])) {
            header("Location: d-medical-records.php");
        } else {
            header("Location: p-my-medical-records.php");
        }
        exit();
    }

    $sql = "INSERT INTO medical_records (patient_id, medical_record_title, medical_record_description) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iss", $patient_id, $title, $description);
        if ($stmt->execute()) {
            $_SESSION["success_message"] = "You have created a medical record for $title successfully!";
        } else {
            $_SESSION["error_message"] = "Medical record creation was unsuccessful!";
        }
        $stmt->close();
    } else {
        $_SESSION["error_message"] = "Medical record creation was unsuccessful!";
    }

    if (isset($_SESSION["doctor_id"])) {
        header("Location: d-medical-records.php");
    } else {
        header("Location: p-my-medical-records.php");
    }
    exit();
}
?>
