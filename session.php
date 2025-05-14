<?php
session_start(); // Start session at the beginning of the script

// Check if the user is logged in
if (isset($_SESSION["successful"])) {
    if ($_SESSION["successful"]) {
        // Redirect to the previous page
        header("Location: {$_SESSION['prev_page']}");
        exit;
    } else {
        // Redirect to login page if not logged in
        header("Location: login.php");
        exit;
    }
} else {
    // If no session is found, redirect to login page
    $_SESSION["successful"] = false; // Mark session as unsuccessful
    header("Location: login.php");

    exit;
}
