<?php
session_start();
session_destroy();
header("Location: http://localhost/PRTS/a-login-page.php");
exit();
?>