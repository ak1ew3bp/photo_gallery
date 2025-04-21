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
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="assets/logo.png" sizes="64x64" type="image/png">
    <title>Photo Gallery</title>
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
    
    .recent-photos img {
        border: 2px solid #fff; /* Add a border for better contrast with the background */
    }
        .navbar-brand img {
            width: 60px;
            height: auto;
            margin-right: 5px;
            border-radius: 50%;
        }

        .recent-photos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 30px;
        }

        .recent-photos img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .recent-photos img:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
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
                    <li class="nav-item"><a class="nav-link" href="upload.php">Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container text-center mt-5">
        <h1>Welcome to <strong>CPE Seminar Photo Gallery </strong></h1>
        <p class="text-muted">
                A <strong>Photo Gallery</strong> that captures key moments from Computer Engineering seminars, 
                showcasing dynamic discussions, project demos, and collaborative learning. It celebrates the 
                community’s achievements while preserving memories that inspire future events and innovation.
            </p>
        <!-- <p><a class="btn btn-primary" href="upload.php">Upload Photos</a></p>
        <p><a class="btn btn-success" href="gallery.php">View Gallery</a></p> -->

        <!-- Display Recently Uploaded Photos -->
        <h2 class="mt-5">Recently Uploaded Photos</h2>
        <div class="recent-photos">
            <?php foreach ($recentPhotos as $photo): ?>
                <div class="photo-item">
                    <img src="<?php echo $photo['file_path']; ?>" alt="Recent Upload" 
                         data-bs-toggle="modal" 
                         data-bs-target="#photoModal" 
                         data-bs-src="<?php echo $photo['file_path']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">View Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Full View" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <script>
        const photoModal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modalImage');

        photoModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget; // Button that triggered the modal
            const imageUrl = button.getAttribute('data-bs-src'); // Extract image URL
            modalImage.src = imageUrl; // Set the image source
        });
    </script>
</div>
    <!-- Include this before closing body tag -->
    <footer class="mt-5 mb-3 text-muted text-center">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CPE Seminar Photo Gallery. All rights reserved.</p>
            <p>Developed with ❤️ by Computer Engineering Students.</p>
            <p>
                <a href="https://www.facebook.com/baltazar.akie21" class="text-dark me-3" target="_blank" rel="noopener">
                    <i class="bi bi-facebook"></i> Baltazar
                </a>
                <a href="https://www.facebook.com/rhyan.sheeet" class="text-dark me-3" target="_blank" rel="noopener">
                    <i class="bi bi-facebook"></i> Campo
                </a>
                <a href="https://www.facebook.com/raul.laguinday" class="text-dark me-3" target="_blank" rel="noopener">
                    <i class="bi bi-facebook"></i> Laguinday
                </a>
                <a href="https://www.facebook.com/brentjiyandeyb" class="text-dark me-3" target="_blank" rel="noopener">
                    <i class="bi bi-facebook"></i> Ubias
                </a>
                    
            </p>
        </div>
    </footer>
</body>
</html>
