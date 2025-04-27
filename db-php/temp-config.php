<?php
    // Automatically unset 'selected_patient_id' if the current page is not allowed
    $allowedPages = ['d-medical-records.php', 'a-medical-records-handler.php', 'd-view-medical-record-files.php'];
    $currentPage = basename($_SERVER['PHP_SELF']);

    if (!in_array($currentPage, $allowedPages)) {
        unset($_SESSION["selected_patient_id"]);
    }
?>
