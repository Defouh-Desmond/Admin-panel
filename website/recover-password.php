<?php
session_start();
require_once "../classes/connection.php"; // Adjust path if needed

// Get token from URL
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("<h3>Invalid or missing token.</h3>");
}

// Validate token and fetch expiry
$stmt = $mysqli->prepare("SELECT pr.user_id, u.email, pr.expires_at 
                          FROM password_resets pr 
                          JOIN users u ON pr.user_id = u.user_id 
                          WHERE pr.token = ? 
                          LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("<h3>This password reset link is invalid or has expired.</h3>");
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if token is expired
$expires_at = $user['expires_at'];
if (strtotime($expires_at) < time()) {
    die("<h3>This password reset link has expired.</h3>");
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update user's password
        $stmtUpdate = $mysqli->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
        $stmtUpdate->bind_param("si", $hashed_password, $user['user_id']);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Delete the token
        $stmtDelete = $mysqli->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmtDelete->bind_param("s", $token);
        $stmtDelete->execute();
        $stmtDelete->close();

        $success = "Your password has been successfully reset. You can now <a href='login.php'>log in</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Licrestor - Reset Password</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/style.css"/>

    <style>
        #countdown {
            font-size: 16px;
            font-weight: bold;
            color: #ff6600;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div id="forgot-section" class="section">
    <div class="container">
        <div class="row signup-container">

            <!-- Left Side -->
            <div class="col-md-6 col-sm-12 signup-welcome text-center">
                <div class="welcome-content">
                    <h2>Reset Your Password</h2>
                    <p>Enter your new password below to regain access to your account.</p>
                    <p id="countdown"></p>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-md-6 col-sm-12 signup-form-wrapper">
                <div class="signup-form">

                    <!-- Messages -->
                    <?php if($error): ?>
                        <p class="text-danger text-center"><?= htmlspecialchars($error) ?></p>
                    <?php elseif($success): ?>
                        <p class="text-success text-center"><?= $success ?></p>
                    <?php endif; ?>

                    <?php if(!$success): ?>
                    <!-- Reset Password Form -->
                    <form id="resetForm" method="post" onsubmit="return validatePasswords();">
                        <div class="form-group">
                            <input type="password" id="password" class="input" name="password" placeholder="New Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="confirm_password" class="input" name="confirm_password" placeholder="Confirm New Password" required>
                        </div>
                        <button type="submit" class="main-button btn btn-block">Reset Password</button>
                    </form>
                    <?php endif; ?>

                    <p class="login-link text-center"><a href="login.php">Back to login</a></p>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script>
function validatePasswords() {
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();

    if(password.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }
    if(password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
    }
    return true;
}

// Countdown timer
const countdownElem = document.getElementById('countdown');
const expiryTime = new Date("<?= $expires_at ?>").getTime();

const timer = setInterval(function(){
    const now = new Date().getTime();
    const distance = expiryTime - now;

    if(distance <= 0) {
        clearInterval(timer);
        countdownElem.textContent = "⏰ The reset link has expired. Please request a new one.";
        document.getElementById('resetForm').style.display = 'none';
        return;
    }

    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    countdownElem.textContent = `⏳ Reset link expires in ${minutes}:${seconds < 10 ? '0'+seconds : seconds}`;
}, 1000);
</script>

</body>
</html>
