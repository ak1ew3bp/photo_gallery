<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$userId = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'photo_gallery');
$folders = $conn->query("SELECT * FROM folders WHERE user_id = $userId");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="icon" href="assets/logo.png" type="image/png"> <!-- Adjust path as needed -->
    <title>Gallery</title>
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
        background: rgba(255, 255, 255, 0.8); 
        padding: 20px;
        border-radius: 10px;
        color: #000;
    }
    .gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
    }
    .photo:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    }
    .navbar-brand img {
        width: 60px;
        height: auto;
        margin-right: 5px;
        border-radius: 50%;
    }
    .folder-description {
        font-size: 1rem;
        color: #555;
        margin-top: 10px;
        padding: 10px;
        background-color: #f9f9f9;
        border-left: 5px solid #007bff;
        border-radius: 5px;
        line-height: 1.5;
        max-width: 100%;
        word-wrap: break-word;
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
                    <li class="nav-item"><a class="nav-link" href="upload.php">Upload</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="text-center mb-4">Your Photo Gallery</h1>
        <?php while ($folder = $folders->fetch_assoc()): ?>
            <div class="mb-5">
                <h3 class="text-primary"><?php echo htmlspecialchars($folder['folder_name']); ?></h3>
                <div class="gallery">
                    <?php
                    $photos = $conn->query("SELECT * FROM photos WHERE folder_id = {$folder['id']}");
                    while ($photo = $photos->fetch_assoc()):
                    ?>
                        <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Photo" class="photo" 
                             data-bs-toggle="modal" 
                             data-bs-target="#photoModal" 
                             data-photo-path="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                             data-photo-id="<?php echo $photo['id']; ?>">
                    <?php endwhile; ?>
                </div>
                <!-- Folder description with improved styling -->
                <p class="folder-description"><?php echo htmlspecialchars($folder['description']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Modal for Viewing and Deleting Photo -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">View Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fullSizePhoto" src="" alt="Full Size Photo" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <a id="downloadButton" class="btn btn-primary" href="" download>Download</a>
                    <button type="button" class="btn btn-danger" id="deleteButton">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const photoModal = document.getElementById('photoModal');
        const fullSizePhoto = document.getElementById('fullSizePhoto');
        const downloadButton = document.getElementById('downloadButton');
        const deleteButton = document.getElementById('deleteButton');
        let currentPhotoId;

        photoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const photoPath = button.getAttribute('data-photo-path');
            currentPhotoId = button.getAttribute('data-photo-id');

            fullSizePhoto.src = photoPath;
            downloadButton.href = photoPath;
        });

        deleteButton.addEventListener('click', function () {
            if (confirm('Are you sure you want to delete this photo?')) {
                axios.post('delete_photo.php', {
                    photo_id: currentPhotoId
                }).then(response => {
                    if (response.data.success) {
                        alert('Photo deleted successfully.');
                        location.reload();
                    } else {
                        alert('Error deleting photo: ' + response.data.error);
                    }
                }).catch(error => {
                    console.error('There was an error deleting the photo:', error);
                });
            }
        });
    </script>
        <footer class="mt-5 mb-3 text-muted text-center">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CPE Seminar Photo Gallery. All rights reserved.</p>
            <p>Developed with ❤️ by Computer Engineering Students.</p>
        </div>
    </footer>
</body>
</html>
