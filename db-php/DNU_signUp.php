<?php

include_once "db.php";

// Process form data if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $sex = $_POST["sex"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    

    // Start a transaction
    $conn->begin_transaction(); 

    try {
        // Insert user into 'users' table
        $insertUser = $conn->prepare("INSERT INTO users (email, user_password, role) VALUES (?, ?, ?)");
        $insertUser->bind_param("sss", $email, $password, $role);
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

        echo "Account registered successfully!";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Close statements and connection
    $insertUser->close();
    $conn->close();
}
?>