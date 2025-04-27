<?php
session_start();
require_once "db-php/db.php";

$targetDirectory = "medical-record-file-uploads/";
$allowedTypes = ["pdf", "jpg", "jpeg", "png", "webp", "avif", "doc", "docx"];
$maxFileSize = 10 * 1024 * 1024;


// Identify logged-in user and their role
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if ($user_id === null) {
    $_SESSION["error_message"] = "You must be logged in to upload files.";
    session_destroy();
    header("Location: a-login-page.php");
    exit();
}

$failedUploadCounter = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['medical_files'])) {
    $medical_record_id = $_POST['medical_record_id'];
    $medicalFileNames = $_POST['medical_file_names'];
    $fileTypes = $_POST['file_types'];

    foreach ($_FILES['medical_files']['tmp_name'] as $index => $tmpName) {
        $originalName = basename($_FILES['medical_files']['name'][$index]);
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedTypes)) {
            $failedUploadCounter++;
            continue; // Skip unsupported files
        }

        // Check file size
        if ($_FILES['medical_files']['size'][$index] > $maxFileSize) {
            $failedUploadCounter++;
            continue;
        }

        $uniqueFileName = uniqid("record_") . "." . $fileExt;
        $targetFile = $targetDirectory . $uniqueFileName;

        if (move_uploaded_file($tmpName, $targetFile)) {
            $fileType = $fileTypes[$index];
            $medicalFileName = $medicalFileNames[$index];

            $insertSQL = "INSERT INTO medical_record_files 
                          (medical_record_id, medical_record_user_id, medical_record_file_name, medical_record_file_path, medical_record_file_type) 
                          VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($insertSQL);
            $stmt->bind_param("iisss", $medical_record_id, $user_id, $medicalFileName, $targetFile, $fileType);
            $stmt->execute();
        }
    }

    if ($failedUploadCounter > 0) {
        $_SESSION["error_message"] = "One or more files failed to upload. Ensure that supported file types are used (PDF, JPG, JPEG, PNG, WEBP, AVIF, DOC, DOCX) and each file is under 10MB.";
    } else {
        $_SESSION["success_message"] = "File(s) uploaded successfully!";
    }
} else {
    $_SESSION["error_message"] = "File upload failed. No files received.";
}

if ($role === "patient") {
    header("Location: p-my-medical-records.php");
    exit();
} else if ($role === "doctor") {
    header("Location: d-medical-records.php");
    exit();
}

?>
