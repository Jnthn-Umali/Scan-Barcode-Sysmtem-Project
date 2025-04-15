<?php
session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .content-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 30px;
            padding: 20px;
            justify-content: center;
        }
        .framed-box {
            flex: 1;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 10px;
        }
        .message-box {
            max-width: 45%;
        }
        .carousel-container {
            max-width: 45%;
        }
        @media (max-width: 768px) {
            .content-container {
                flex-direction: column;
            }
            .message-box, .carousel-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#profileModal">Profile</a>
                </li>
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

    <div class="container content-container">
        <!-- Message Box -->
        <div class="framed-box message-box">
            <h4>Welcome to the Barcode Student Identification System</h4>
            <p>
                The Barcode Student Identification System is a secure and efficient student management system 
                designed for Arellano University. This system streamlines student identification and campus 
                access through unique barcode IDs assigned to each student.
            </p>
        </div>

        <!-- Image Carousel -->
        <div class="framed-box carousel-container">
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="1.jpg" class="d-block w-100" alt="Image 1">
                    </div>
                    <div class="carousel-item">
                        <img src="2.jpg" class="d-block w-100" alt="Image 2">
                    </div>
                    <div class="carousel-item">
                        <img src="3.jpg" class="d-block w-100" alt="Image 3">
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Change Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="profileContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#profileModal').on('show.bs.modal', function () {
                $('#profileContent').load('profile.php');
            });

            // Handle form submission via AJAX
            $(document).on('submit', '#profileContent form', function(e) {
                e.preventDefault(); // Prevent default form submission
                var form = $(this);
                $.ajax({
                    type: 'POST',
                    url: 'profile.php',
                    data: form.serialize(),
                    success: function(response) {
                        $('#profileContent').html(response); // Update modal content with response
                    }
                });
            });
        });
    </script>
</body>
</html>
