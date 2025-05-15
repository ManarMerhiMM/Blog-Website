<?php
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") { //form submission check
    try {
        if (isset($_POST["signup"])) { //the signup form is the one submitted
            $username = htmlspecialchars(trim($_POST["username"]));
            $password = htmlspecialchars(trim($_POST["password"]));

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //hash password

            //execute query then redirect user to login, use statement preparation to protect from SQL injections
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

            $stmt->bind_param("ss", $username, $hashedPassword);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            }
        }
    } catch (mysqli_sql_exception $e) { //Username already exists error
        if ($e->getCode() === 1062) {
            $error = "Username already exists.";
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