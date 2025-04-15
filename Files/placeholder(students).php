<?php
session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("Location: login.php");
    exit();
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php"; // Database connection
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Define absolute paths
$rootDir = __DIR__ . DIRECTORY_SEPARATOR; // Root directory of the script
$barcodeDir = $rootDir . 'barcodes' . DIRECTORY_SEPARATOR;

// Ensure the 'barcodes' folder exists
if (!file_exists($barcodeDir)) {
    if (!mkdir($barcodeDir, 0777, true)) {
        die("Failed to create barcodes directory.");
    }
}

// Handle Add Student
if (isset($_POST['addStudent'])) {
    if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'yes') {
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

        // Insert student into database
        $sql = "INSERT INTO tblstudents (studentnumber, name, campus, yearlevel, grade, yearenrolled, age, gender, address, emailstudent, emailguardian) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssissss", $studentnumber, $name, $campus, $yearlevel, $grade, $yearenrolled, $age, $gender, $address, $emailstudent, $emailguardian);
        
        if (mysqli_stmt_execute($stmt)) {
            try {
                $generator = new BarcodeGeneratorPNG();
                $barcode = $generator->getBarcode($studentnumber, $generator::TYPE_CODE_128);
                
                // Debugging: Show barcode instead of saving it
                echo '<img src="data:image/png;base64,' . base64_encode($barcode) . '" width="100">';
                exit(); // Stop execution to check the output

                // Save barcode to file (this part will be skipped for now)
                $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $studentnumber) . ".png";
                $barcodePath = $barcodeDir . $filename;
                file_put_contents($barcodePath, $barcode);

                $_SESSION['message'] = "Student added successfully!";
            } catch (Exception $e) {
                die("Error generating barcode: " . $e->getMessage());
            }
        }

        }
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    if (isset($_GET['confirmation']) && $_GET['confirmation'] === 'yes') {
        $studentnumber = $_GET['delete'];

        // Delete student from database
        $sql = "DELETE FROM tblstudents WHERE studentnumber=?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentnumber);
        mysqli_stmt_execute($stmt);

        // Delete barcode file
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $studentnumber) . ".png";
        $barcodePath = $barcodeDir . $filename;
        if (file_exists($barcodePath)) {
            unlink($barcodePath);
        }

        $_SESSION['message'] = "Student deleted successfully!";
        header("Location: students.php");
        exit();
    }
}

// Handle Update Student
if (isset($_POST['updateStudent'])) {
    if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'yes') {
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
        mysqli_stmt_bind_param($stmt, "sssssisisss", $name, $campus, $yearlevel, $grade, $yearenrolled, $age, $gender, $address, $emailstudent, $emailguardian, $studentnumber);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Student updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update student.";
        }
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Date Picker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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
        <h2 class="text-center">Manage Students</h2>

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
                    <th>Actions</th>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editStudentForm">
                        <input type="hidden" name="edit_studentnumber" id="edit_studentnumber">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="edit_name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Campus</label>
                            <select class="form-control" name="edit_campus" id="edit_campus" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year Level</label>
                            <select class="form-control" name="edit_yearlevel" id="edit_yearlevel" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Grade</label>
                            <select class="form-control" name="edit_grade" id="edit_grade" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year Enrolled</label>
                            <input type="text" class="form-control datepicker" name="edit_yearenrolled" id="edit_yearenrolled" required>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" class="form-control" name="edit_age" id="edit_age" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control" name="edit_gender" id="edit_gender" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
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
                        <input type="hidden" name="confirmation" id="editConfirmation" value="no">
                        <button type="submit" class="btn btn-primary" name="updateStudent">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize Date Picker
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',
                autoclose: true
            });

            // Edit Button Click Handler
            $(".editBtn").click(function() {
                const studentnumber = $(this).data("studentnumber");
                const name = $(this).data("name");
                const campus = $(this).data("campus");
                const yearlevel = $(this).data("yearlevel");
                const grade = $(this).data("grade");
                const yearenrolled = $(this).data("yearenrolled");
                const age = $(this).data("age");
                const gender = $(this).data("gender");
                const address = $(this).data("address");
                const emailstudent = $(this).data("emailstudent");
                const emailguardian = $(this).data("emailguardian");

                $("#edit_studentnumber").val(studentnumber);
                $("#edit_name").val(name);
                $("#edit_campus").val(campus);
                $("#edit_yearlevel").val(yearlevel);
                $("#edit_grade").val(grade);
                $("#edit_yearenrolled").val(yearenrolled);
                $("#edit_age").val(age);
                $("#edit_gender").val(gender);
                $("#edit_address").val(address);
                $("#edit_emailstudent").val(emailstudent);
                $("#edit_emailguardian").val(emailguardian);

                $("#editStudentModal").modal("show");
            });

            // Delete Button Click Handler
            $(".deleteBtn").click(function(e) {
                e.preventDefault();
                const studentnumber = $(this).data("studentnumber");
                if (confirm("Are you sure you want to delete this student?")) {
                    window.location.href = "?delete=" + studentnumber + "&confirmation=yes";
                }
            });

            // Add Student Form Submission
            $("#addStudentForm").submit(function(e) {
                if (confirm("Are you sure you want to add this student?")) {
                    $("#addConfirmation").val("yes");
                } else {
                    e.preventDefault();
                }
            });

            // Edit Student Form Submission
            $("#editStudentForm").submit(function(e) {
                if (confirm("Are you sure you want to update this student?")) {
                    $("#editConfirmation").val("yes");
                } else {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>