<?php
if (isset($_POST['btnlogin'])) {
    require_once "config.php";
    $sql = "SELECT * FROM tblaccounts WHERE username = ? AND password = ? AND userstatus = 'ACTIVE'";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $_POST['txtusername'], $_POST['txtpassword']);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $account = mysqli_fetch_array($result, MYSQLI_ASSOC);
                session_start();
                $_SESSION['username'] = $_POST['txtusername'];
                $_SESSION['usertype'] = $account['usertype'];

                if ($account['usertype'] == 'ADMINISTRATOR') {
                    header("location: admin-index.php");
                } elseif ($account['usertype'] == 'SECURITY') {
                    header("location: security-index.php");
                } elseif ($account['usertype'] == 'STUDENT') {
                    header("location: students-index.php");
                } else {
                    header("location: index.php");
                }
                exit();
            } else {
                echo "<div class='alert alert-danger text-center mt-2'>Incorrect login details or account is inactive</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Barcode Student Identification System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: url('aubg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            height: auto;
            max-width: 400px;
            margin: auto;
            margin-top: 5%;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }
        .login-container img {
            width: 100px;
            display: block;
            margin: 0 auto 20px auto;
        }
        .form-group label {
            text-align: left;
            display: block;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-check {
            margin-bottom: 20px;
        }
        .btn-block {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="au.jpg" alt="Logo">
        <h3 class="text-center">Barcode Student Identification System</h3>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="txtusername" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="txtpassword" placeholder="Enter password" required>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="showPassword">
                <label class="form-check-label" for="showPassword">Show Password</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block" name="btnlogin">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            var passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>