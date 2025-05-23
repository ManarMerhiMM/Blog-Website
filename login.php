<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") { //form submission check
    if (isset($_POST["login"])) { //the login form is the one submitted
        $username = htmlspecialchars(trim($_POST["username"]));
        $password = htmlspecialchars(trim($_POST["password"]));

        if ($username == "" || $password == "") {
            $error = "Error: Empty field(s)!";
        } else {
            //execute query then redirect user to homepage, use statement preparation to protect from SQL injections
            $stmt = $conn->prepare("SELECT id, password, deactivated, is_admin FROM users WHERE username = ?");

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $hashedPassword = $user["password"];
                $id = $user["id"];
                $deactivated = (int) $user['deactivated'];
                $is_admin = (int) $user['is_admin'];

                // Verify password using password_verify()
                if (password_verify($password, $hashedPassword)) {
                    // Password is correct check for deactivation before routing
                    if ($deactivated) {
                        $error = "Your account has been deactivated :/";
                    } else {
                        $_SESSION["username"] = $username;
                        $_SESSION["id"] = $id;
                        $_SESSION["successful"] = true;
                        $_SESSION["is_admin"] = $is_admin;
                        header("Location: dashboard.php"); // Redirect to dashboard
                        exit;
                    }
                } else {
                    $error = "Incorrect Credentials"; //password was not verified
                }
            } else {
                $error = "Error: This account does not exist, consider signing up!"; //the username does not exist which is why the select statement returned 0 rows
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="imgs/loginRegisterIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/login.css">
</head>

<body>
    <form action="login.php" method="post" id="loginForm">
        <p id="errors"><?php if (isset($error)) {
                            echo "{$error}";
                        } ?></p>
        <div class="formContainers">
            <label for="username">Username:</label>
            <input type="text" placeholder="Username..." name="username" id="username">
        </div>
        <div class="formContainers">
            <label for="password">Password:</label>
            <input type="password" placeholder="Password..." name="password" id="password">
            <img id="hidepass" class="passControllers" src="imgs/hidepass.png" alt="password hidden">
            <img id="showpass" class="passControllers" src="imgs/showpass.png" alt="password shown">
        </div>
        <div class="formContainers">
            <a id="forgotPass" href="forgot_password.php">Forgot password? Click here</a>
        </div>
        <div class="btnControls">
            <button type="submit" name="login">Login</button>
            <button type="button"><a href="register.php">Signup Instead</a></button>
            <button type="button"><a href="index.php">Home</a></button>
        </div>
    </form>

    <script src="JS/login.js"></script>
</body>

</html>