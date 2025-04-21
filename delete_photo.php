<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $photoId = $input['photo_id'] ?? null;

    if (!$photoId) {
        echo json_encode(['success' => false, 'error' => 'Photo ID is missing']);
        exit;
    }

    $userId = $_SESSION['user_id'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'photo_gallery');

    // Check for connection errors
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    // Verify photo ownership
    $stmt = $conn->prepare("
        SELECT file_path, folder_id 
        FROM photos 
        JOIN folders ON photos.folder_id = folders.id 
        WHERE photos.id = ? AND folders.user_id = ?
    ");
    $stmt->bind_param('ii', $photoId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $photo = $result->fetch_assoc();
        $filePath = $photo['file_path'];
        $folderId = $photo['folder_id'];

        // Delete the photo record from the database
        $deleteStmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
        $deleteStmt->bind_param('i', $photoId);
        $deleteStmt->execute();

        if ($deleteStmt->affected_rows > 0) {
            // Attempt to delete the file from the server
            if (file_exists($filePath) && unlink($filePath)) {
                // Check if the folder is now empty
                $folderCheckStmt = $conn->prepare("SELECT COUNT(*) AS photo_count FROM photos WHERE folder_id = ?");
                $folderCheckStmt->bind_param('i', $folderId);
                $folderCheckStmt->execute();
                $folderCheckResult = $folderCheckStmt->get_result();
                $folderData = $folderCheckResult->fetch_assoc();

                if ($folderData['photo_count'] == 0) {
                    // Delete the folder if empty
                    $deleteFolderStmt = $conn->prepare("DELETE FROM folders WHERE id = ?");
                    $deleteFolderStmt->bind_param('i', $folderId);
                    $deleteFolderStmt->execute();
                    $deleteFolderStmt->close();
                }

                $folderCheckStmt->close();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'File deletion failed']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete photo record']);
        }

        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Photo not found or unauthorized']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
