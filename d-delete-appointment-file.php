<?php
session_start();
require_once "db-php/db.php";
include_once "db-php/temp-config.php";

$doctor_id = $_SESSION['doctor_id'] ?? null;

if ($doctor_id === null) {
    $_SESSION["error_message"] = "You must be logged in as a doctor to delete files.";
    session_destroy();
    header("Location: a-login-page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION["error_message"] = "Invalid request.";
    header("Location: d-booked-appointments.php");
    exit();
}

$appointment_file_id = intval($_POST["appointment_file_id"] ?? 0);
$appointment_id = intval($_POST["appointment_id"] ?? 0);

if (!$appointment_file_id || !$appointment_id) {
    $_SESSION["error_message"] = "Missing data to delete file.";
    header("Location: d-booked-appointments.php");
    exit;
}

// Step 1: Get file path
$sql = "SELECT appointment_file_path FROM appointment_files WHERE appointment_file_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION["error_message"] = "File not found.";
    header("Location: d-view-appointment-files.php");
    exit;
}

$row = $result->fetch_assoc();
$file_path = $row["appointment_file_path"];

// Step 2: Delete from DB
$delete_sql = "DELETE FROM appointment_files WHERE appointment_file_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $appointment_file_id);
$delete_stmt->execute();

// Step 3: Delete file from filesystem
if (file_exists($file_path)) {
    unlink($file_path);
}

$_SESSION["success_message"] = "File deleted successfully.";
header("Location: d-booked-appointments.php");
exit; 
?>