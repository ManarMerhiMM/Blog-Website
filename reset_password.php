<?php
session_start();
include "connect.php";

// Get token from query string
$token = $_GET['token'] ?? '';

if (!$token) {
    header("Location: login.php");
    exit;
}

// Verify token is valid and not expired
$stmt = $conn->prepare("
    SELECT pr.user_id, pr.id
    FROM password_resets pr
    WHERE pr.token = ? AND pr.expires_at > NOW()
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // invalid or expired
    header("Location: login.php");
    exit;
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];
$stmt->close();

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass1 = trim($_POST['password']);
    $pass2 = trim($_POST['confirm_password']);

    if ($pass1 == "" || $pass2 == "") {
        $error = "Error: Empty field(s)!";
    } else if ($pass1 != $pass2) {
        $error = "Error: Passwords do not match!";
    } else if (strlen($pass1) < 10) {
        $error = "Error: New password must be at least 10 characters long!";
    } else if (!preg_match('/\d/', $pass1)) {
        $error = "Error: Password must contain numbers!";
    } else {
        // Update user password
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $upd  = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $hash, $user_id);
        $upd->execute();
        $upd->close();

        // Delete reset token
        $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $del->bind_param("s", $token);
        $del->execute();
        $del->close();

        // Redirect to login
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="icon" href="imgs/resetPassIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/reset_password.css">
</head>

<body>
    <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="post" id="resetForm">
        <p id="errors" style="<?php echo $error ? 'display:block' : 'display:none'; ?>">
            <?php echo htmlspecialchars($error); ?>
        </p>

        <div class="formContainers">
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" placeholder="New password...">
        </div>

        <div class="formContainers">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat password...">
        </div>

        <div class="btnControls">
            <button type="submit" name="reset">Reset Password</button>
            <button type="button"><a href="login.php">Back to Login</a></button>
        </div>
    </form>

    <script src="JS/reset_password.js"></script>
</body>

</html>