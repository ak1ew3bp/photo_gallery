<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'photo_gallery');
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param('ss', $username, $password);

    if ($stmt->execute()) {
        $success = "Registration successful! <a href='login.php'>Login</a>";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="assets/logo.png" type="image/png"> <!-- Adjust path as needed -->
    <title>Register</title>
    <style>
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
        padding: 20px;
        border-radius: 10px;
        color: #000;
    }
                @media (max-width: 576px) {
            .container {
                padding: 20px;
            }
            .navbar-brand img {
                width: 30px; /* Smaller logo on smaller screens */
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
    <div class="col-md-8 text-center mb-4">
            <h5 class="mb-3"><strong>CPE Seminar Photo Gallery</strong></h5>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Register</h4>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                        <p class="text-center mt-3">
                            Already have an account? <a href="login.php">Login</a>
                        </p>
                        <footer class="mt-5 mb-3 text-muted text-center">
                        <p class="mt-5 mb-3 text-muted text-center">&copy; <?php echo date('Y'); ?> CPE Seminar Photo Gallery. All rights reserved.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
