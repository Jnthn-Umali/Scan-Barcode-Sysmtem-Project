<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$username = $_SESSION['username'];
$usertype = '';
$userstatus = '';

// Fetch user details from the database
$sql = "SELECT usertype, userstatus FROM tblaccounts WHERE username = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $usertype, $userstatus);
        mysqli_stmt_fetch($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password from the database
    $sql = "SELECT password FROM tblaccounts WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $stored_password);
            mysqli_stmt_fetch($stmt);
            
            if ($current_password === $stored_password) {
                if ($new_password === $confirm_password) {
                    $update_sql = "UPDATE tblaccounts SET password = ? WHERE username = ?";
                    if ($update_stmt = mysqli_prepare($link, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "ss", $new_password, $username);
                        if (mysqli_stmt_execute($update_stmt)) {
                            // Check if the password was actually updated
                            if (mysqli_stmt_affected_rows($update_stmt) > 0) {
                                echo "<div class='alert alert-success text-center mt-2'>Password changed successfully</div>";
                            } else {
                                echo "<div class='alert alert-danger text-center mt-2'>No changes made. New password might be the same as the old one.</div>";
                            }
                        } else {
                            // Show SQL execution error
                            echo "<div class='alert alert-danger text-center mt-2'>Error: " . mysqli_error($link) . "</div>";
                        }
                    } else {
                        // Show SQL prepare error
                        echo "<div class='alert alert-danger text-center mt-2'>Error: " . mysqli_error($link) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger text-center mt-2'>New passwords do not match</div>";
                }
            } else {
                echo "<div class='alert alert-danger text-center mt-2'>Current password is incorrect</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center mt-2'>User not found</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center mt-2'>Error: " . mysqli_error($link) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-body">
                <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p class="card-text"><strong>Usertype:</strong> <?php echo htmlspecialchars($usertype); ?></p>
                <p class="card-text"><strong>Userstatus:</strong> <?php echo htmlspecialchars($userstatus); ?></p>
            </div>
        </div>
        <form method="POST" action="">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</body>
</html>