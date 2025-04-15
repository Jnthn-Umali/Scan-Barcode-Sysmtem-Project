<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Load PHPMailer

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];

    // Fetch student details
    $query = "SELECT * FROM tblstudents WHERE studentnumber = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $barcode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if ($student) {
        // Get guardian's email
        $guardianEmail = $student['emailguardian'];  
        $studentName = $student['name'];
        $studentNumber = $student['studentnumber'];

        // Log scan
        $datelog = date("Y-m-d");
        $timelog = date("H:i:s");
        $action = "Scanned Student - {$student['studentnumber']}";
        $module = "Barcode Scanner";
        $performedby = "Security";

        $logQuery = "INSERT INTO tbllogs (datelog, timelog, action, module, performedby) VALUES (?, ?, ?, ?, ?)";
        $logStmt = mysqli_prepare($link, $logQuery);
        mysqli_stmt_bind_param($logStmt, "sssss", $datelog, $timelog, $action, $module, $performedby);
        mysqli_stmt_execute($logStmt);

        // Send email notification
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Use your email provider
            $mail->SMTPAuth = true;
            $mail->Username = 'jonathanumali2126@gmail.com'; // Your email
            $mail->Password = 'asyc dabd cjfc ulpi'; // Your email password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Content
            $mail->setFrom('jonathanumali2126@gmail.com', 'Arellano University Security');
            $mail->addAddress($guardianEmail);
            $mail->Subject = "Student Barcode Scanned - $studentName";
            $mail->Body = "Dear Guardian,\n\nYour child, $studentName (Student No: $studentNumber), has been scanned at the security checkpoint.\n\nDate: $datelog\nTime: $timelog\n\nThank you.\nArellano University Security Team";

            // Send Email
            if ($mail->send()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Student Scanned Successfully! Email sent to guardian.",
                    "student" => $student
                ]);
            } else {
                echo json_encode([
                    "status" => "success",
                    "message" => "Student Scanned Successfully! But email was not sent.",
                    "student" => $student
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "success",
                "message" => "Student Scanned Successfully! But email error: " . $mail->ErrorInfo,
                "student" => $student
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Student not found."
        ]);
    }
    exit;
}
?>
