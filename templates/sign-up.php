<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php'; // Include your database connection here

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize input data
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));
    
    // Capture role////////////////////////////////////
    $role = isset($_POST['role']) ? $_POST['role'] : null; // Default to 'trainee'

    // For trainers, capture additional gym details
    $gymName = isset($_POST['gymName']) ? htmlspecialchars(trim($_POST['gymName'])) : null;
    $gymContact = isset($_POST['gymContact']) ? htmlspecialchars(trim($_POST['gymContact'])) : null;
    $gymLocation = isset($_POST['gymLocation']) ? htmlspecialchars(trim($_POST['gymLocation'])) : null;
    $servicesOffered = isset($_POST['servicesOffered']) ? htmlspecialchars(trim($_POST['servicesOffered'])) : null;

    // Additional user details
    $height = isset($_POST['height']) ? htmlspecialchars(trim($_POST['height'])) : null;
    $weight = isset($_POST['weight']) ? htmlspecialchars(trim($_POST['weight'])) : null;
    $age = isset($_POST['age']) ? htmlspecialchars(trim($_POST['age'])) : null;
    $gender = isset($_POST['gender']) ? htmlspecialchars(trim($_POST['gender'])) : null; // Capture gender

    // Additional validation
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if the email already exists
        $emailQuery = "SELECT email FROM Users WHERE email = ?";
        $stmt = $conn->prepare($emailQuery);

        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }

        // Set gym_id to NULL if the user is a trainer
        $gymIdToInsert = ($role == 'trainer') ? null : $_POST['gymPreferred']; 

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert user data into users table
            $query = "INSERT INTO Users (firstName, lastName, email, password, role, gym_id, height, weight, age, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($query);

            if (!$stmt2) {
                die("Preparation failed: " . $conn->error);
            }

            // Bind parameters for user registration
            $stmt2->bind_param("sssssiiiis", 
                $firstName, 
                $lastName, 
                $email, 
                $hashedPassword, 
                $role, 
                $gymIdToInsert, 
                $height, 
                $weight, 
                $age,
                $gender
            );

            // echo $role;
            print_r($_POST);


            if ($stmt2->execute()) {
                // If the user is a trainer and provides gym details, insert into Gym table
                if ($role == 'trainer' && !empty($gymName) && !empty($gymContact) && !empty($gymLocation) && !empty($servicesOffered)) {
                    // Insert gym details into Gym table
                    $gymQuery = "INSERT INTO Gym (gym_name, gym_location, services_offered, gym_contact) VALUES (?, ?, ?, ?)";
                    $stmt3 = $conn->prepare($gymQuery);

                    if (!$stmt3) {
                        die("Preparation failed: " . $conn->error);
                    }

                    // Bind parameters for gym registration
                    $stmt3->bind_param('ssss', 
                        $gymName, 
                        $gymLocation, 
                        $servicesOffered, 
                        $gymContact
                    );

                    if (!$stmt3->execute()) {
                        die("Gym insertion failed: " . mysqli_error($conn));
                    }
                }

                // Registration successful, redirect to login or dashboard
                header('Location: login.php');
                exit();
            } else {
                die("User insertion failed: " . mysqli_error($conn));
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
  <link rel="stylesheet" href="../static/css/sign-up.css">
  <script src="../static/scripts/sign-up.js" defer></script>
  <title>FitFlex | Sign-up Page</title>
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

<div class="container">
  <h1>Create an Account</h1>
  <form id="registerForm" method="POST" action="sign-up.php">
      <?php if (!empty($error)): ?>
          <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <input type="text" name="firstName" placeholder="First Name" required class="input_area">
      <input type="text" name="lastName" placeholder="Last Name" required class="input_area">
      <input type="email" name="email" placeholder="Email" required class="input_area">
      <input type="password" name="password" placeholder="Password" required class="input_area">
      <input type="password" name="confirmPassword" placeholder="Confirm Password" required class="input_area">

      <!-- New Fields for Height, Weight and Age -->
      <input type="number" name="height" placeholder="Height (cm)" required min="0" class="input_area">
      <input type="number" name="weight" placeholder="Weight (kg)" required min="0" class="input_area">
      <input type="number" name="age" placeholder="Age (years)" required min="0" class="input_area">

      <!-- Gender Selection -->
      <div>
          <label for="gender">Gender:</label>
          <select name="gender" required class='input_area'>
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
          </select>
      </div>

      <div class="select-role">
          <label>Select role:</label><br>
          <input type='radio' id='trainer' name='role' value='trainer' required> Trainer<br> <!-- Changed from gym_owner to trainer -->
          <input type='radio' id='trainee' name='role' value='trainee' required> Trainee (Gym user)<br><br> <!-- Kept trainee -->
      </div>

      <!-- Hidden fields to capture trainer details -->
      <input type='hidden' name='gymName' id='gymNameHidden'>
      <input type='hidden' name='gymContact' id='gymContactHidden'>
      <input type='hidden' name='gymLocation' id='gymLocationHidden'>
      <input type='hidden' name='servicesOffered' id='servicesOfferedHidden'>

      <!-- Submit Button -->
      <input type='submit' value='Register' class='submit_btn'>
  </form>

  <!-- Trainer Modal -->
  <div id='trainerModal' class='modal'>
      <div class='modal-content'>
          <span class='close'>&times;</span>
          <h2>Trainer Details</h2> <!-- Updated modal title -->
          <form id='trainerForm'> <!-- Updated form ID -->
              <input type='text' id='gymName' placeholder='Gym Name' required class='input_area'>
              <input type='text' id='gymContact' placeholder='Contact Details' required class='input_area'>
              <input type='text' id='gymLocation' placeholder='Gym Location' required class='input_area'>
              <input type='text' id='servicesOffered' placeholder='Services Offered' required class='input_area'>
              <button type='button' id='trainerDone'>Done</button> <!-- Updated button ID -->
          </form>
      </div>
  </div>

  <!-- Trainee Modal -->
  <div id='traineeModal' class='modal'>
      <div class='modal-content'>
          <span class='close'>&times;</span>
          <h2>Trainee Gym Details</h2>
          <form id='traineeForm'>
              <label for='gymPreferred'>Select Your Preferred Gym:</label>
              <select id='gymPreferred' name='gymPreferred' required class='input_area'>
                  <!-- Populate gyms from the database -->
                  <?php 
                  include 'db_connect.php'; 
                  $sql = "SELECT gym_id, gym_name FROM Gym";
                  if ($result = mysqli_query($conn, $sql)) {
                      while ($row = mysqli_fetch_assoc($result)) {
                          echo '<option value="' . htmlspecialchars($row['gym_id']) . '">' . htmlspecialchars($row['gym_name']) . '</option>';
                      }
                  } else {
                      echo '<option value="">Error fetching gyms</option>';
                  }
                  ?>
              </select><br><br>
              <button type='button' id='traineeDone'>Done</button>
          </form>
      </div>
  </div>

</div>

<script src="../static/scripts/sign-up.js"></script> <!-- Ensure this script is linked -->
</body>
</html>
