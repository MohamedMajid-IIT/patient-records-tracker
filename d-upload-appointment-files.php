<?php
    session_start();
    require_once "db-php/db.php";

    $doctor_id = $_SESSION["doctor_id"];

    $targetDirectory = "appointment-file-uploads/";
    $allowedTypes = ["pdf", "jpg", "jpeg", "png", "webp", "avif", "doc", "docx"];
    $maxFileSize = 10 * 1024 * 1024;
    
    $failedUploadCounter = 0;
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['appointment_files'])) {
        $appointment_id = $_POST['appointment_id'];
        $appointmentFileNames = $_POST['appointment_file_names'];
        $fileTypes = $_POST['file_types'];
    
        foreach ($_FILES['appointment_files']['tmp_name'] as $index => $tmpName) {
            $originalName = basename($_FILES['appointment_files']['name'][$index]);
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
            if (!in_array($fileExt, $allowedTypes)) {
                $failedUploadCounter++;
                continue; // Skip unsupported files
            }

            // Check file size
            if ($_FILES['appointment_files']['size'][$index] > $maxFileSize) {
                $failedUploadCounter++;
                continue;
            }
    
            $uniqueFileName = uniqid("record_") . "." . $fileExt;
            $targetFile = $targetDirectory . $uniqueFileName;
    
            if (move_uploaded_file($tmpName, $targetFile)) {
                $fileType = $fileTypes[$index];
                $appointmentFileName = $appointmentFileNames[$index];
    
                $insertSQL = "INSERT INTO appointment_files 
                              (appointment_id, appointment_file_doctor_id, appointment_file_name, appointment_file_path, appointment_file_type) 
                              VALUES (?, ?, ?, ?, ?)";
    
                $stmt = $conn->prepare($insertSQL);
                $stmt->bind_param("iisss", $appointment_id, $doctor_id, $appointmentFileName, $targetFile, $fileType);
                $stmt->execute();
            }
        }
    
        if ($failedUploadCounter > 0) {
            $_SESSION["error_message"] = "One or more files failed to upload. Ensure that supported file types are used (PDF, JPG, JPEG, PNG, WEBP, AVIF, DOC, DOCX) and each file is under 10MB.";
            header("Location: d-booked-appointments.php");
            exit();
        } else {
            $_SESSION["success_message"] = "File(s) uploaded successfully!";
            header("Location: d-booked-appointments.php");
            exit();
        }
    } else {
        $_SESSION["error_message"] = "File upload failed. No files received.";
        header("Location: d-booked-appointments.php");
        exit();
    }

?>