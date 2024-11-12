<?php
session_start(); // Start the session

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // Include your database connection file

// Initialize an error variable
$error = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Get the selected role (trainer or trainee)

    // Validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Prepare the SQL query to check the user
        $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ? AND role = ?");
        $stmt->bind_param('ss', $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify password if user exists
        if ($user && password_verify($password, $user['password'])) {
            // Successful login, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['firstName'] . ' ' . $user['lastName'];
            $_SESSION['user_type'] = $user['role']; // trainer or trainee

            // Redirect to dashboard based on role
            header('Location: dashboard.php');
            exit();
        } else {
            // More specific error messages
            if ($result->num_rows == 0) {
                $error = "Invalid email or role.";
            } else {
                $error = "Invalid password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/nav.css">
    <link rel="stylesheet" href="../static/css/login.css">
    <script src="../static/scripts/login.js" defer></script>
    <title>FitFlex | Login Page</title>
</head>
<body>
    <!-- Navigation Section -->
    <header class="header">
        <div><a href="./index.php"><img class="logo" src="../static/images/FitFlex.png" alt="website logo" width="70px"></a></div>
        <nav>
            <ul class="menu-items">
                <li><a href="./index.php">Home</a></li>
                <li><a href="./about.php">About Us</a></li>
                <li><button><a href="./login.php">Login</a></button></li>
                <li><button><a href="./sign-up.php">Sign-up</a></button></li>
            </ul>
        </nav>
    </header>

    <!-- Body section -->
    <div class="container">
        <div class="banner-login"></div>

        <div class="right-content">
            <div class="header-wrapper">
                <div class="heading" style="color: #122331;"><h1>WELCOME BACK!</h1></div>
            </div>

            <div class="inner-container">
                <!-- Form for login -->
                <form id="loginForm" method="POST" action="login.php">
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div><input id="email" class="input_area" name="email" type="email" placeholder="Enter your email" required></div>
                    <div id="emailError" class="error-message"></div> <!-- Added error message div -->
                    <div><input id="password" class="input_area" name="password" type="password" placeholder="Enter your password" required></div>
                    <div id="passwordError" class="error-message"></div> <!-- Added error message div -->

                    <div class="select-role">
                        <label for="role">Select role:</label><br>
                        <input type="radio" id="trainer" name="role" value="trainer" required> Trainer<br>
                        <input type="radio" id="trainee" name="role" value="trainee" required> Trainee (Gym user)<br><br>
                    </div>

                    <div class="btn-wrapper">
                        <input class="submit_btn" type="submit" value="Log in">
                        <div class="create"><p>Do not have an account?</p><a href="./sign-up.php">Sign-up</a></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
