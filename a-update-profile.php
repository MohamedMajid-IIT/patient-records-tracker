<?php
    session_start();
    require_once "db-php/db.php";

    if (isset($_SESSION["doctor_id"])){
        $doctor_id = $_SESSION["doctor_id"];

        // Get doctor details from POST
        $doctor_name = $_POST['doctor_name'];
        $email = $_POST['email'];
        $phone = $_POST['user_phone'];
        $doctor_sex = $_POST['doctor_sex'];
        $specialization = $_POST['specialization'];
        $hospital = $_POST['hospital'];
        
        // Get the user_id of the doctor
        $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
            $user_id = $user['user_id'];

            // Update doctor
            $stmt1 = $conn->prepare("UPDATE doctors SET doctor_name = ?, doctor_sex = ?, specialization = ?, hospital = ? WHERE doctor_id = ?");
            $stmt1->bind_param("ssssi", $doctor_name, $doctor_sex, $specialization, $hospital, $doctor_id);
            $stmt1->execute();

            // Update doctor name in current session after successful update
            $_SESSION["name"] = $doctor_name;

            // Update user email
            $stmt2 = $conn->prepare("UPDATE users SET email = ?, user_phone = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi", $email, $phone, $user_id);
            $stmt2->execute();

            $_SESSION["popupMessage"] = "Doctor profile updated successfully.";
            $_SESSION["popupType"] = "success";
            header("Location: d-my-profile.php");
            exit;
        }

    } elseif (isset($_SESSION["patient_id"])) {
        $patient_id = $_SESSION["patient_id"];
    
        $patient_name = $_POST['patient_name'];
        $email = $_POST['email'];
        $phone = $_POST['user_phone'];
        $patient_sex = $_POST['patient_sex'];
        $dob = $_POST['dob'];
        $nic = $_POST['nic'];
        $emergency_contact_name = $_POST['emergency_contact_name'];
        $emergency_contact_email = $_POST['emergency_contact_email'];
        $emergency_contact_relationship = $_POST['emergency_contact_relationship'];
        $emergency_contact_phone = $_POST['emergency_contact_phone'];

        $stmt = $conn->prepare("SELECT user_id FROM patients WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
            $user_id = $user['user_id'];

            $stmt1 = $conn->prepare("UPDATE patients SET patient_name = ?, patient_sex = ?, dob = ?, nic = ?, emergency_contact_name = ?, emergency_contact_email = ?, emergency_contact_relationship = ?, emergency_contact_phone = ? WHERE patient_id = ?");
            $stmt1->bind_param("ssssssssi", $patient_name, $patient_sex, $dob, $nic, $emergency_contact_name, $emergency_contact_email, $emergency_contact_relationship, $emergency_contact_phone, $patient_id);
            $stmt1->execute();

            $_SESSION["name"] = $patient_name;

            $stmt2 = $conn->prepare("UPDATE users SET email = ? , user_phone = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi", $email, $phone, $user_id);
            $stmt2->execute();

            $_SESSION["popupMessage"] = "Patient profile updated successfully.";
            $_SESSION["popupType"] = "success";
            header("Location: p-my-profile.php");
            exit;
        }

    }

// If neither doctor nor patient is logged in
$_SESSION["popupMessage"] = "Error updating profile.";
$_SESSION["popupType"] = "error";
header("Location: a-login-page.php");
exit;
    
?>
