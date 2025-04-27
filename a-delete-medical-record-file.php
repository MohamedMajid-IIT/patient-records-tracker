<?php
session_start();
require_once "db-php/db.php";

// Identify logged-in user and their role
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if ($user_id === null) {
    $_SESSION["error_message"] = "You must be logged in access this page.";
    header("Location: a-login-page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION["error_message"] = "Invalid request.";
    if ($role == "patient") {
        header("Location: p-my-medical-records.php");
        exit;
    } elseif ($role == "doctor") {
        header("Location: d-medical-records.php");
        exit;
    } else {
        header("Location: a-login-page.php");
        exit;
    }
}

$medical_record_file_id = intval($_POST["medical_record_file_id"] ?? 0);
$medical_record_id = intval($_POST["medical_record_id"] ?? 0);

if (!$medical_record_file_id || !$medical_record_id) {
    $_SESSION["error_message"] = "Missing data to delete file.";
    if ($role == "patient") {
        header("Location: p-my-medical-records.php");
        exit;
    } elseif ($role == "doctor") {
        header("Location: d-medical-records.php");
        exit;
    } else {
        header("Location: a-login-page.php");
        exit;
    }
}

// Step 1: Get file path
$sql = "SELECT medical_record_file_path FROM medical_record_files WHERE medical_record_file_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $medical_record_file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION["error_message"] = "File not found.";
    if ($role == "patient") {
        header("Location: p-my-medical-records.php");
        exit;
    } elseif ($role == "doctor") {
        header("Location: d-medical-records.php");
        exit;
    } else {
        header("Location: a-login-page.php");
        exit;
    }
}

$row = $result->fetch_assoc();
$file_path = $row["medical_record_file_path"];

// Step 2: Delete from DB
$delete_sql = "DELETE FROM medical_record_files WHERE medical_record_file_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $medical_record_file_id);
$delete_stmt->execute();

// Step 3: Delete file from filesystem
if (file_exists($file_path)) {
    unlink($file_path);
}

$_SESSION["success_message"] = "File deleted successfully.";
if ($role == "patient") {
    header("Location: p-my-medical-records.php");
    exit;
} elseif ($role == "doctor") {
    header("Location: d-medical-records.php");
    exit;
}
?>