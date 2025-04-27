<?php
session_start();

if (isset($_SESSION["user_id"])) {
    echo json_encode([
        "status" => "success",
        "name" => $_SESSION["name"],
        "role" => $_SESSION["role"]
    ]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
