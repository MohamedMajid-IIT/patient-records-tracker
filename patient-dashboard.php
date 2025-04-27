<?php
    session_start();
    require_once "db-php/db.php";
?>

<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Patient Dashboard</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <link rel="stylesheet" href="./style-sheets/dashboard-styles.css">
    </head>

    <body>
        <header>
            <section class="header-left">
                <h1>Welcome <span id="patient-name">...</span>!</h1>
            </section>

            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/p-my-appointments.php'">My Appointments</button>
                <button onclick="location.href='http://localhost/PRTS/p-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
        <button class="appointment-button" onclick="location.href='http://localhost/PRTS/p-book-appointment.php'">
            <p>Book an Appointment</p>
        </button>
        <button class="medical-rec-button" onclick="location.href='http://localhost/PRTS/p-my-medical-records.php'">
            <p>My Medical Records</p>
        </button>
        </main>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>

    </body>
</html>