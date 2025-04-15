<?php
session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("Location: login.php");
    exit();
}

require_once "config.php"; // Database connection

// Handle Add Account
if (isset($_POST['addAccount'])) {
    // Check if confirmation is set and equals 'yes'
    if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'yes') {
        $username = $_POST['username'];
        $password = $_POST['password']; // Plain text password
        $usertype = $_POST['usertype'];
        $userstatus = "ACTIVE"; // Always set to ACTIVE
        $createdby = $_SESSION['username'];
        $email = $_POST['email'];
        $datecreated = date("m/d/Y");

        $sql = "INSERT INTO tblaccounts (username, password, usertype, userstatus, createdby, datecreated, email) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $username, $password, $usertype, $userstatus, $createdby, $datecreated, $email);
        mysqli_stmt_execute($stmt);

        // Success message
        $_SESSION['message'] = "Account added successfully!";
    }
}

// Handle Delete Account
if (isset($_GET['delete'])) {
    // Check if confirmation is set and equals 'yes'
    if (isset($_GET['confirmation']) && $_GET['confirmation'] === 'yes') {
        $username = $_GET['delete'];

        // Delete the student from tblstudents first
        mysqli_query($link, "DELETE FROM tblstudents WHERE studentnumber='$username'");

        // Then delete the account from tblaccounts
        mysqli_query($link, "DELETE FROM tblaccounts WHERE username='$username'");

        // Success message
        $_SESSION['message'] = "Account and student record deleted successfully!";
    }
}


// Handle Update Account
if (isset($_POST['updateAccount'])) {
    // Check if confirmation is set and equals 'yes'
    if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'yes') {
        $username = $_POST['edit_username'];
        $usertype = $_POST['edit_usertype'];
        $userstatus = $_POST['edit_userstatus'];
        $email = $_POST['edit_email'];
        $newPassword = $_POST['edit_password'];

        $sql = "UPDATE tblaccounts SET usertype=?, userstatus=?, email=?";
        $params = array($usertype, $userstatus, $email);

        if (!empty($newPassword)) {
            $sql .= ", password=?";
            $params[] = $newPassword; // Plain text password
        }

        $sql .= " WHERE username=?";
        $params[] = $username;

        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);
        mysqli_stmt_execute($stmt);

        // Success message
        $_SESSION['message'] = "Account updated successfully!";
    }
}

// Handle Search
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM tblaccounts WHERE username LIKE ? OR usertype LIKE ? OR email LIKE ?";
    $stmt = mysqli_prepare($link, $sql);
    $searchTerm = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($link, "SELECT * FROM tblaccounts");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .navbar {
            background-color: blue;
        }
        .navbar-dark .navbar-nav .nav-link {
            color: white;
        }
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #f8f9fa;
        }
        .dropdown-menu {
            background-color: #343a40;
        }
        .dropdown-item {
            color: white;
        }
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="admin-index.php">Home</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="managementsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Managements
                    </a>
                    <div class="dropdown-menu" aria-labelledby="managementsDropdown">
                        <a class="dropdown-item" href="accounts.php">Accounts</a>
                        <a class="dropdown-item" href="students.php">Students</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center">Manage Accounts</h2>

        <!-- Display Success Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
        </form>

        <!-- Add Account Button -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addAccountModal">Add Account</button>

        <!-- Accounts Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Created By</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td> <!-- Plain text password -->
                        <td><?php echo htmlspecialchars($row['usertype']); ?></td>
                        <td><?php echo htmlspecialchars($row['userstatus']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['createdby']); ?></td>
                        <td><?php echo htmlspecialchars($row['datecreated']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm editBtn" 
                                    data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                    data-password="<?php echo htmlspecialchars($row['password']); ?>"
                                    data-usertype="<?php echo htmlspecialchars($row['usertype']); ?>"
                                    data-userstatus="<?php echo htmlspecialchars($row['userstatus']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>">
                                Edit
                            </button>
                            <a href="#" class="btn btn-danger btn-sm deleteBtn" data-username="<?php echo htmlspecialchars($row['username']); ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addAccountForm">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>User Type</label>
                            <select class="form-control" name="usertype">
                                <option>-- Select User Type --</option>
                                <option>ADMINISTRATOR</option>
                                <option>SECURITY</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <input type="hidden" name="confirmation" id="addConfirmation" value="no">
                        <button type="submit" class="btn btn-primary" name="addAccount">Add Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editAccountForm">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="edit_username" id="edit_username" readonly>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="edit_password" id="edit_password">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="showPasswordCheckbox">
                                <label class="form-check-label">Show Password</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>User Type</label>
                            <select class="form-control" name="edit_usertype" id="edit_usertype">
                                <option>ADMINISTRATOR</option>
                                <option>SECURITY</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="edit_userstatus" id="edit_userstatus">
                                <option>ACTIVE</option>
                                <option>INACTIVE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="edit_email" id="edit_email" required>
                        </div>
                        <input type="hidden" name="confirmation" id="editConfirmation" value="no">
                        <button type="submit" class="btn btn-primary" name="updateAccount">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Show/Hide Password in Modal
            $("#showPasswordCheckbox").change(function() {
                const passwordField = $("#edit_password");
                if (this.checked) {
                    passwordField.attr("type", "text");
                } else {
                    passwordField.attr("type", "password");
                }
            });

            // Edit Button Click Handler
            $(".editBtn").click(function() {
                const username = $(this).data("username");
                const password = $(this).data("password"); // Plain text password
                const usertype = $(this).data("usertype");
                const userstatus = $(this).data("userstatus");
                const email = $(this).data("email");

                $("#edit_username").val(username);
                $("#edit_password").val(password); // Pre-fill with plain text
                $("#edit_usertype").val(usertype);
                $("#edit_userstatus").val(userstatus);
                $("#edit_email").val(email);

                $("#editAccountModal").modal("show");
            });

            // Delete Button Click Handler
            $(".deleteBtn").click(function(e) {
                e.preventDefault();
                const username = $(this).data("username");
                if (confirm("Are you sure you want to delete this account?")) {
                    window.location.href = `?delete=${username}&confirmation=yes`;
                }
            });

            // Add Account Form Submission
            $("#addAccountForm").submit(function(e) {
                if (confirm("Are you sure you want to add this account?")) {
                    $("#addConfirmation").val("yes");
                } else {
                    e.preventDefault();
                }
            });

            // Edit Account Form Submission
            $("#editAccountForm").submit(function(e) {
                if (confirm("Are you sure you want to update this account?")) {
                    $("#editConfirmation").val("yes");
                } else {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>