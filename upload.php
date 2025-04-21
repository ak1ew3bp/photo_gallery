<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$userId = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'photo_gallery');

// Query to get the most recent 5 photos uploaded by the user
$query = "SELECT * FROM photos 
          JOIN folders ON photos.folder_id = folders.id
          WHERE folders.user_id = ? 
          ORDER BY photos.upload_time DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$recentPhotos = [];
while ($row = $result->fetch_assoc()) {
    $recentPhotos[] = $row;
}

$stmt->close();

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photos'])) {
    $folderName = $_POST['folder_name'];
    $folderDescription = $_POST['folder_description'];
    $uploadDir = "uploads/$userId/$folderName/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Insert folder details into the database
    $stmt = $conn->prepare("INSERT INTO folders (user_id, folder_name, description) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $folderName, $folderDescription);
    $stmt->execute();
    $folderId = $conn->insert_id;

    // Upload photos
    foreach ($_FILES['photos']['tmp_name'] as $index => $tmpFilePath) {
        $fileName = basename($_FILES['photos']['name'][$index]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpFilePath, $targetFilePath)) {
            $stmt = $conn->prepare("INSERT INTO photos (folder_id, file_path) VALUES (?, ?)");
            $stmt->bind_param('is', $folderId, $targetFilePath);
            $stmt->execute();
        }
    }

    $uploadSuccess = true;
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <title>Upload Photos</title>
    <style>
    html, body {
    height: 100%; /* Ensure the body takes up the full height of the viewport */
    margin: 0; /* Remove any default margins */
}

body {
    display: flex;
    flex-direction: column; /* Arrange children vertically */
}

.container {
    flex: 1; /* Push the footer to the bottom by taking up available space */
}

footer {
    text-align: center;
    padding: 10px 0;
    margin-top: auto; /* Push the footer to the bottom */
}    
    body {
        position: relative;
        margin: 0;
        min-height: 100vh;
        font-family: Arial, sans-serif;
        background-color: #000; /* Fallback color */
        color: #fff; /* Text color for contrast */
    }

    body::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('assets/bg.png'); /* Replace with your image path */
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        opacity: 0.5; /* Adjust this for background transparency */
        z-index: -1; /* Ensure the background stays behind the content */
    }

    .container {
        position: relative;
        z-index: 1; /* Ensures content stays above the background */
        background: rgba(255, 255, 255, 0.8); /* Optional: Semi-transparent background for content */
        padding: 20px;
        border-radius: 10px;
        color: #000;
    }        
        .navbar-brand img {
            width: 60px;
            height: auto;
            margin-right: 5px;
            border-radius: 50%;
        }

        .container {
            max-width: 600px;
            margin-top: 10px;
        }

        .form-control, .btn {
            margin-bottom: 15px;
        }

        .success-message {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px;
            }

            .navbar-brand img {
                width: 30px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/logo.png" alt="Logo"> Photo Gallery
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="gallery.php">View Gallery</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-center mb-4">Upload Photos</h2>

        <!-- Display success message if photos are uploaded -->
        <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
            <div class="success-message">
                Photos uploaded successfully! <a href="gallery.php" class="btn btn-light btn-sm">View Gallery</a>
            </div>
        <?php endif; ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="folder_name" class="form-label">Folder Name</label>
                <input type="text" class="form-control" id="folder_name" name="folder_name" placeholder="Enter folder name" required>
            </div>
            <div class="mb-3">
                <label for="folder_description" class="form-label">Folder Description</label>
                <textarea class="form-control" id="folder_description" name="folder_description" rows="3" placeholder="Enter a description for the folder"></textarea>
            </div>
            <div class="mb-3">
                <label for="photos" class="form-label">Select Photos</label>
                <input type="file" class="form-control" id="photos" name="photos[]" multiple required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload Photos</button>
        </form>
    </div>
    <footer class="mt-5 mb-3 text-muted text-center">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CPE Seminar Photo Gallery. All rights reserved.</p>
            <p>Developed with ❤️ by Computer Engineering Students.</p>
        </div>
    </footer>
</body>
</html>
