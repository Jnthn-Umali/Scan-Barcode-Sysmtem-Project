<?php
session_start();
include 'config.php';
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Ensure student is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$studentnumber = $_SESSION['username'];
$query = "SELECT * FROM tblstudents WHERE studentnumber='$studentnumber'";
$result = mysqli_query($link, $query);
$student = mysqli_fetch_assoc($result);

// Generate Barcode
$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($studentnumber, $generator::TYPE_CODE_128);
$barcodePath = "barcodes/{$studentnumber}.png";
file_put_contents($barcodePath, $barcode);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }
        .card {
            width: 400px;
            padding: 20px;
            border-radius: 15px;
            background: white;
            margin: auto;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }
        .barcode {
            margin: 10px 0;
            width: 80%;
        }
        .details {
            text-align: left;
            margin-top: 10px;
            font-size: 16px;
            line-height: 1.6;
        }
        button {
            padding: 10px;
            margin-top: 10px;
            cursor: pointer;
            border: none;
            background: #007BFF;
            color: white;
            border-radius: 5px;
            font-size: 16px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>Student Profile</h2>

    <div class="card" id="studentCard">
        <h3 class="title">Student Access Card</h3>
        <hr>
        <div class="details">
            <p><strong>Name:</strong> <?php echo strtoupper($student['name']); ?></p>
            <p><strong>Student Number:</strong> <?php echo $studentnumber; ?></p>
            <p><strong>Campus:</strong> <?php echo $student['campus']; ?></p>
            <p><strong>Year Level:</strong> <?php echo $student['yearlevel']; ?></p>
            <p><strong>Grade:</strong> <?php echo $student['grade']; ?></p>
        
        </div>
        <img class="barcode" src="<?php echo $barcodePath; ?>" alt="Barcode">
    </div>

    <button onclick="downloadCard()">Download ID Card</button>
    <button onclick="window.location.href='logout.php'">Logout</button>

    <script>
        function downloadCard() {
            html2canvas(document.getElementById("studentCard")).then(canvas => {
                let link = document.createElement("a");
                link.download = "Student_ID_<?php echo $studentnumber; ?>.png";
                link.href = canvas.toDataURL();
                link.click();
            });
        }
    </script>

</body>
</html>
