<?php
    session_start();
    require_once "db-php/db.php";
    include_once "db-php/temp-config.php";
?>

<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Doctor Dashboard</title>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
        <script src="http://localhost/PRTS/dashboard.js"></script>
        <link rel="stylesheet" href="./style-sheets/dashboard-styles.css">
    </head>

    <body>
        <header>
            <section class="header-left">
                <h1>Welcome Dr. <span id="doctor-name">...</span>!</h1>
            </section>
            <section class="header-right">
                <button onclick="location.href='http://localhost/PRTS/d-my-availability.php'">My Availability Slots</button>
                <button onclick="location.href='http://localhost/PRTS/d-my-profile.php'">My Profile</button>
                <button onclick="logout()">Logout</button>
            </section>
        </header>

        <main>
        <button class="booked-appointment-button" onclick="location.href='http://localhost/PRTS/d-booked-appointments.php'">
            <p>My Booked Appointments</p>
        </button>
        <button class="my-patients-button" onclick="location.href='http://localhost/PRTS/d-my-patients.php'">
            <p>My Patients</p>
        </button>
        </main>

        <footer>
            2025 - Patient Records Tracker System [PRTS]
        </footer>

    </body>
</html>