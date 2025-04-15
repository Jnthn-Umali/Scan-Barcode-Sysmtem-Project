<?php
session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("Location: login.php");
    exit();
}

require_once "config.php"; // Database connection
require 'vendor/autoload.php'; // Include Composer dependencies

use Picqer\Barcode\BarcodeGeneratorPNG;

// Ensure 'barcodes' folder exists
$barcodeDir = "barcodes/";
if (!is_dir($barcodeDir)) {
    mkdir($barcodeDir, 0777, true);
}

// Handle Add Student
if (isset($_POST['addStudent'])) {
    $studentnumber = $_POST['studentnumber'];
    $name = $_POST['name'];
    $campus = $_POST['campus'];
    $yearlevel = $_POST['yearlevel'];
    $grade = $_POST['grade'];
    $yearenrolled = $_POST['yearenrolled'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $emailstudent = $_POST['emailstudent'];
    $emailguardian = $_POST['emailguardian'];
    $password = $_POST['password']; // Plain text password
    $createdby = $_SESSION['username']; // Assuming logged-in user
    $datecreated = date('m/d/Y'); // Current timestamp

    // Insert student into database
    $sql = "INSERT INTO tblstudents (studentnumber, name, campus, yearlevel, grade, yearenrolled, age, gender, address, emailstudent, emailguardian) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssissss", $studentnumber, $name, $campus, $yearlevel, $grade, $yearenrolled, $age, $gender, $address, $emailstudent, $emailguardian);
    
    if (mysqli_stmt_execute($stmt)) {
        // Generate Barcode
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($studentnumber, $generator::TYPE_CODE_128);
        $barcodeDir = "barcodes/"; // Ensure this directory exists
        $barcodePath = $barcodeDir . $studentnumber . ".png";
        file_put_contents($barcodePath, $barcode);

        // Insert student account into tblaccounts
        $query2 = "INSERT INTO tblaccounts (username, password, usertype, userstatus, email, createdby, datecreated) 
                   VALUES (?, ?, 'STUDENT', 'ACTIVE', ?, ?, ?)";
        $stmt2 = mysqli_prepare($link, $query2);
        mysqli_stmt_bind_param($stmt2, "sssss", $studentnumber, $password, $emailstudent, $createdby, $datecreated);
        mysqli_stmt_execute($stmt2);

        $_SESSION['message'] = "Student added successfully with barcode and account!";
    }
}


// Fetch students data
$result = mysqli_query($link, "SELECT * FROM tblstudents");

// Handle Delete Student
if (isset($_GET['delete']) && isset($_GET['confirmation']) && $_GET['confirmation'] === 'yes') {
    $studentnumber = $_GET['delete'];

    // Delete from tblaccounts first
    $sql1 = "DELETE FROM tblaccounts WHERE username=?";
    $stmt1 = mysqli_prepare($link, $sql1);
    mysqli_stmt_bind_param($stmt1, "s", $studentnumber);
    mysqli_stmt_execute($stmt1);
    
    // Delete from tblstudents
    $sql2 = "DELETE FROM tblstudents WHERE studentnumber=?";
    $stmt2 = mysqli_prepare($link, $sql2);
    mysqli_stmt_bind_param($stmt2, "s", $studentnumber);

    if (mysqli_stmt_execute($stmt2)) {
        // Delete the barcode image
        $barcodePath = "barcodes/" . $studentnumber . ".png";
        if (file_exists($barcodePath)) {
            unlink($barcodePath);
        }
        $_SESSION['message'] = "Student deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting student.";
    }
}


// Handle Update Student
if (isset($_POST['updateStudent'])) {
    $studentnumber = $_POST['edit_studentnumber'];
    $name = $_POST['edit_name'];
    $campus = $_POST['edit_campus'];
    $yearlevel = $_POST['edit_yearlevel'];
    $grade = $_POST['edit_grade'];
    $yearenrolled = $_POST['edit_yearenrolled'];
    $age = $_POST['edit_age'];
    $gender = $_POST['edit_gender'];
    $address = $_POST['edit_address'];
    $emailstudent = $_POST['edit_emailstudent'];
    $emailguardian = $_POST['edit_emailguardian'];

    $sql = "UPDATE tblstudents SET name=?, campus=?, yearlevel=?, grade=?, yearenrolled=?, age=?, gender=?, address=?, emailstudent=?, emailguardian=? WHERE studentnumber=?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "sssssiissss", $name, $campus, $yearlevel, $grade, $yearenrolled, $age, $gender, $address, $emailstudent, $emailguardian, $studentnumber);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Student updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating student.";
    }
}


// Fetch students data
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM tblstudents WHERE studentnumber LIKE ? OR name LIKE ? OR campus LIKE ?";
    $stmt = mysqli_prepare($link, $sql);
    $searchTerm = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($link, "SELECT * FROM tblstudents");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-info'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']); // Clear message after displaying
        }
        ?>

    <div class="container mt-5">
        <h2 class="text-center">Manage Students</h2>
        
        <!-- Add Student Button -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addStudentModal">Add Student</button>

        
        <!-- Students Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Name</th>
                    <th>Campus</th>
                    <th>Year Level</th>
                    <th>Grade</th>
                    <th>Year Enrolled</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Email (Student)</th>
                    <th>Email (Guardian)</th>
                    <th>Barcode</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['studentnumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['campus']); ?></td>
                        <td><?php echo htmlspecialchars($row['yearlevel']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade']); ?></td>
                        <td><?php echo htmlspecialchars($row['yearenrolled']); ?></td>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['emailstudent']); ?></td>
                        <td><?php echo htmlspecialchars($row['emailguardian']); ?></td>
                        <td><img src="barcodes/<?php echo $row['studentnumber']; ?>.png" width="100" alt="Barcode"></td>
                        <td>
                            <button class="btn btn-info btn-sm editBtn" 
                                    data-studentnumber="<?php echo htmlspecialchars($row['studentnumber']); ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-campus="<?php echo htmlspecialchars($row['campus']); ?>"
                                    data-yearlevel="<?php echo htmlspecialchars($row['yearlevel']); ?>"
                                    data-grade="<?php echo htmlspecialchars($row['grade']); ?>"
                                    data-yearenrolled="<?php echo htmlspecialchars($row['yearenrolled']); ?>"
                                    data-age="<?php echo htmlspecialchars($row['age']); ?>"
                                    data-gender="<?php echo htmlspecialchars($row['gender']); ?>"
                                    data-address="<?php echo htmlspecialchars($row['address']); ?>"
                                    data-emailstudent="<?php echo htmlspecialchars($row['emailstudent']); ?>"
                                    data-emailguardian="<?php echo htmlspecialchars($row['emailguardian']); ?>">
                                Edit
                            </button>
                            <a href="#" class="btn btn-danger btn-sm deleteBtn" data-studentnumber="<?php echo htmlspecialchars($row['studentnumber']); ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addStudentForm">
                        <div class="form-group">
                            <label>Student Number</label>
                            <input type="text" class="form-control" name="studentnumber" required>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Campus</label>
                            <select class="form-control" name="campus" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year Level</label>
                            <select class="form-control" name="yearlevel" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Grade</label>
                            <select class="form-control" name="grade" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year Enrolled</label>
                            <input type="text" class="form-control datepicker" name="yearenrolled" required>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" class="form-control" name="age" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control" name="gender" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                        <div class="form-group">
                            <label>Email (Student)</label>
                            <input type="email" class="form-control" name="emailstudent" required>
                        </div>
                        <div class="form-group">
                            <label>Email (Guardian)</label>
                            <input type="email" class="form-control" name="emailguardian" required>
                        </div>
                        <input type="hidden" name="confirmation" id="addConfirmation" value="no">
                        <button type="submit" class="btn btn-primary" name="addStudent">Add Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="edit_studentnumber" id="edit_studentnumber">
                        
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="edit_name" id="edit_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Campus</label>
                            <select name="edit_campus" class="form-control" id="edit_campus" required>
                                <option value="A">Campus A</option>
                                <option value="B">Campus B</option>
                                <option value="C">Campus C</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Year Level</label>
                            <input type="text" class="form-control" name="edit_yearlevel" id="edit_yearlevel" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Grade</label>
                            <input type="text" class="form-control" name="edit_grade" id="edit_grade" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Year Enrolled</label>
                            <input type="date" class="form-control" name="edit_yearenrolled" id="edit_yearenrolled" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" class="form-control" name="edit_age" id="edit_age" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control" name="edit_gender" id="edit_gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="edit_address" id="edit_address" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email (Student)</label>
                            <input type="email" class="form-control" name="edit_emailstudent" id="edit_emailstudent" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email (Guardian)</label>
                            <input type="email" class="form-control" name="edit_emailguardian" id="edit_emailguardian" required>
                        </div>
                        
                        <button type="submit" name="updateStudent" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Open Edit Modal and Populate Fields
            $(".editBtn").click(function() {
                $("#edit_studentnumber").val($(this).data("studentnumber"));
                $("#edit_name").val($(this).data("name"));
                $("#edit_campus").val($(this).data("campus"));
                $("#edit_yearlevel").val($(this).data("yearlevel"));
                $("#edit_grade").val($(this).data("grade"));
                $("#edit_yearenrolled").val($(this).data("yearenrolled")); // Ensure this is a valid date input
                $("#edit_age").val($(this).data("age"));
                $("#edit_gender").val($(this).data("gender"));
                $("#edit_address").val($(this).data("address"));
                $("#edit_emailstudent").val($(this).data("emailstudent"));
                $("#edit_emailguardian").val($(this).data("emailguardian"));

                $("#editStudentModal").modal("show");
            });

            // Delete Confirmation
            $(".deleteBtn").click(function(e) {
                e.preventDefault();
                var studentnumber = $(this).data("studentnumber");
                if (confirm("Delete student?")) {
                    window.location.href = "students.php?delete=" + studentnumber + "&confirmation=yes";
                }
            });
        });
    </script>

    

</body>
</html>
