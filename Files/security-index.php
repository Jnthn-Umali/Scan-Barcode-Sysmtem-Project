<?php
session_start();
include 'config.php';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- For camera access, ensure you're on HTTPS or use your PC's local IP on mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Barcode Scanner</title>

    <!-- QuaggaJS -->
    <script src="https://serratus.github.io/quaggaJS/examples/js/quagga.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
        }
        .header {
            background-color: blue;
            color: white;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            position: relative;
        }
        .logo img {
            width: 120px;
            margin: 20px auto;
            display: block;
        }
        .logs-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background: gray;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .scanner {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        /* Use a container div for the camera preview */
        #scanner-preview {
            width: 100%;
            height: 480px; /* Set a fixed height */
            border: 2px solid #ccc;
            margin-top: 10px;
        }
        .button {
            background-color: blue;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        .button:hover {
            background-color: darkblue;
        }
    </style>
</head>
<body>

    <div class="header">
        ARELLANO UNIVERSITY BARCODE STUDENT
        <a href="logs.php" class="logs-button">View Logs</a>
        <a href="?logout" class="logout">Logout</a>
    </div>

    <div class="logo">
        <img src="au.jpg" alt="AU Logo">
    </div>

    <div class="container">
        <div class="scanner">
            <h3>Scan Student Barcode</h3>
            <button class="button" id="openCamBtn">Open Camera</button>
            <!-- Use a container div instead of a video element -->
            <div id="scanner-preview"></div>
        </div>
    </div>

    <script>
        const openCamBtn = document.getElementById("openCamBtn");
        const scannerPreview = document.getElementById("scanner-preview");

        // Start camera function
        async function startCamera() {
            try {
                openCamBtn.disabled = true;
                openCamBtn.textContent = "Initializing...";

                // Check for HTTPS or localhost (on mobile, use your PC's IP instead of "localhost")
                if (location.protocol !== 'https:' && location.hostname === 'localhost') {
                    Swal.fire("Camera Error", "On mobile, please use your computer's IP address instead of 'localhost'.", "error");
                    openCamBtn.disabled = false;
                    openCamBtn.textContent = "Open Camera";
                    return;
                }

                // Request camera permission
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                // Stop test stream; Quagga will handle the live feed
                stream.getTracks().forEach(track => track.stop());

                // Initialize Quagga with our container div
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: scannerPreview,
                        constraints: {
                            facingMode: "environment",
                            width: { min: 320, ideal: 640 },
                            height: { min: 240, ideal: 480 }
                        },
                        // Adding inline attributes for mobile browsers
                        attributes: {
                            playsinline: true,
                            autoplay: true,
                            muted: true
                        },
                        area: {
                            top: "0%",
                            right: "0%",
                            left: "0%",
                            bottom: "0%"
                        }
                    },
                    decoder: {
                        readers: ["code_128_reader"]
                    }
                }, (err) => {
                    if (err) {
                        console.error("Quagga init error:", err);
                        Swal.fire("Camera Error", err.message, "error");
                        openCamBtn.disabled = false;
                        openCamBtn.textContent = "Open Camera";
                        return;
                    }
                    Quagga.start();
                    console.log("Quagga started...");
                    openCamBtn.textContent = "Scanning...";
                });

                // Handle detections
                Quagga.onDetected(onBarcodeDetected);

            } catch (error) {
                console.error("startCamera error:", error);
                Swal.fire("Camera Error", error.message, "error");
                openCamBtn.disabled = false;
                openCamBtn.textContent = "Open Camera";
            }
        }

        function onBarcodeDetected(result) {
            const code = result.codeResult.code;
            console.log("Barcode detected:", code);

            // Stop scanning
            Quagga.stop();

            // Send barcode to scan.php
            fetch("scan.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `barcode=${encodeURIComponent(code)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        title: "Scan Successful!",
                        html: `Student: ${data.student.name}<br>Number: ${data.student.studentnumber}`,
                        icon: "success"
                    });
                } else {
                    Swal.fire("Error", data.message, "error");
                }
            })
            .catch(err => {
                console.error("Scan request error:", err);
                Swal.fire("Network Error", "Failed to process scan. Check connection.", "error");
            })
            .finally(() => {
                openCamBtn.disabled = false;
                openCamBtn.textContent = "Open Camera";
                // Optionally, leave the preview visible for subsequent scans or clear it.
            });
        }

        // Attach startCamera to button
        openCamBtn.addEventListener("click", startCamera);

        // Check if the browser supports camera
        window.addEventListener('DOMContentLoaded', () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                Swal.fire({
                    title: "Unsupported Browser",
                    text: "Camera access is not supported in this browser.",
                    icon: "error"
                });
            }
        });
    </script>
</body>
</html>
