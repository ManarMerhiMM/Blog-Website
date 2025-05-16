<?php
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") { //form submission check
    try {
        if (isset($_POST["signup"])) { //the signup form is the one submitted
            $username = htmlspecialchars(trim($_POST["username"]));
            $password = htmlspecialchars(trim($_POST["password"]));
            $email = htmlspecialchars(trim($_POST["email"]));
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //hash password

            if ($username == "" || $email == "" || $password == "") {
                $error = "Error: Empty field(s)!";
            } else if (strlen($password) < 10) {
                $error = "Error: Password must contain at least 10 characters!";
            } else if (!preg_match('/\d/', $password)) {
                $error = "Error: Password must contain numbers!";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Error: Invalid email format!";
            } else {
                //execute query then redirect user to login, use statement preparation to protect from SQL injections
                $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");

                $stmt->bind_param("sss", $username, $hashedPassword, $email);
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit;
                }
            }
        }
    } catch (mysqli_sql_exception $e) { //Username already exists error
        if ($e->getCode() === 1062) {
            $error = "Username/Email already exists.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" href="imgs/loginRegisterIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/register.css">
</head>

<body>
    <form action="register.php" method="post" id="registerForm">
        <p id="errors"><?php if (isset($error)) {
                            echo "{$error}";
                        } ?></p>
        <div class="formContainers">
            <label for="username">Username:</label>
            <input type="text" placeholder="Username..." name="username" id="username">
        </div>
        <div class="formContainers">
            <label for="email">Email:</label>
            <input type="email" placeholder="Email..." name="email" id="email">
        </div>
        <div class="formContainers">
            <label for="password">Password:</label>
            <input type="password" placeholder="Password..." name="password" id="password">
            <img id="hidepass" class="passControllers" src="imgs/hidepass.png" alt="password hidden">
            <img id="showpass" class="passControllers" src="imgs/showpass.png" alt="password shown">
        </div>
        <div class="btnControls">
            <button type="submit" name="signup">Sign up</button>
            <button type="button"><a href="login.php">Login Instead</a></button>
            <button type="button"><a href="index.php">Home</a></button>
        </div>
    </form>

    <script src="JS/register.js"></script>
</body>

</html>