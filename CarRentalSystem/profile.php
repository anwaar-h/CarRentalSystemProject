<?php
session_start();
include "includes/config.php";

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: Login-Signup-Logout/login.php");
    exit;
}

// Display status messages
$notification = '';
if (isset($_SESSION['message'])) {
    $notification = $_SESSION['message'];
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}

// Get user data
$user_id = $_SESSION['user']['id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$current_role = $user['role'];

// Form submission handling
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Email uniqueness check
    $email_check_query = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
    $email_check_result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($email_check_result) > 0) {
        $errors[] = "Email already exists.";
    }

    // Password validation
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    if (empty($errors)) {
        $password_clause = !empty($new_password) ? ", password = '$new_password'" : "";
        $update_query = "UPDATE users 
                         SET username = '$username', 
                             email = '$email' 
                             $password_clause
                         WHERE id = $user_id";
        mysqli_query($conn, $update_query);
        $success = "Profile updated successfully!";
        
        // Update session data
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        
        header("Location: profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main-content.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/sort-filter.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="offers.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
        <!-- Website Header Section -->
<header>
    <h1>Car Rental Service</h1>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="my_rental.php">My Rentals</a></li>
          <li><a href="offers.php">Special Offers</a></li>
          <li><a href="Login-Signup-Logout/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="Login-Signup-Logout/login.php">Login</a></li>
          <li><a href="Login-Signup-Logout/signup.php">Sign Up</a></li>
        <?php endif; ?>
        
        <li><a href="about us.html">About Us</a></li>
        
        <li>
          <a href="profile.php" id="profile-link" class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </a>
        </li>
      </ul>
    </nav>
  </header>

    <main class="profile">
        <?= $notification ?>
        <div class="profile-container">
        <div class="profile-header">
            <h1>Profile Settings</h1>
            <p>Manage your account information</p>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="profile-form">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                    value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                    value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div>
                <label for="type">Account Type</label>
                <input type="text" id="type" name="type" 
                    value="<?= htmlspecialchars($current_role) ?>" disabled>
            </div>

            <div class="full-width">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" 
                    placeholder="Leave blank to keep current password">
            </div>
            
            <div class="full-width">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="full-width">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>

        <?php if (in_array($current_role, ['client', 'premium'])): ?>
            <div class="full-width" style="margin-top: 30px;">
                <form action="request_to_change_role.php" method="POST">
                    <input type="hidden" name="request_type" 
                        value="<?= $current_role == 'client' ? 'premium' : 'client' ?>">
                    <button type="submit" class="btn <?= $current_role == 'client' ? 'btn-success' : 'btn-warning' ?>">
                        <?= $current_role == 'client' ? 'Upgrade to Premium' : 'Downgrade to Client' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="full-width" style="margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dismiss notifications
            document.querySelectorAll('.notification-card .close-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const notification = this.closest('.notification-card');
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                });
            });
        });
    </script>
        <!-- bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>