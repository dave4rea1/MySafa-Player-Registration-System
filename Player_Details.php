<?php
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

// Check if user is logged in
Confirm_Login();

// Check if player details are set in session
$firstName = isset($_SESSION['FirstName']) ? $_SESSION['FirstName'] : '';
$lastName = isset($_SESSION['LastName']) ? $_SESSION['LastName'] : '';
$southAfricanID = isset($_SESSION['SouthAfricanID']) ? $_SESSION['SouthAfricanID'] : '';


$fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';

// Retrieve SAFA ID from database
$safaID = '';
if ($southAfricanID) {
    $sql = "SELECT SAFAID FROM player WHERE SouthAfricanID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $southAfricanID);
    $stmt->execute();
    $stmt->bind_result($safaID);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_details'])) {
    $safaID = $_POST['safa_id'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // Set the session variable for the email address
    $_SESSION['emailAddress'] = $email;
    $_SESSION['safaID'] = $safaID;

    // Handle photo upload
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Insert details into player_details table
    $sql = "INSERT INTO player_details (SouthAfricanID, SAFAID, first_name, last_name, email, contact, physical_address, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $southAfricanID, $safaID, $firstName, $lastName, $email, $contact, $address, $photo);

    if ($stmt->execute()) {
        $_SESSION["SuccessMessage"] = "Details saved successfully.";
        header("Location: Documents.php"); // Redirect to Documents.php
        exit;
    } else {
        $_SESSION["ErrorMessage"] = "Something went wrong. Try again.";
    }
}

// Handle photo removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_photo'])) {
    // Logic to remove photo from database
    $sql = "UPDATE player_details SET photo = NULL WHERE SouthAfricanID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $southAfricanID);

    if ($stmt->execute()) {
        $_SESSION["SuccessMessage"] = "Photo removed successfully.";
        header("Location: Player_Details.php"); // Refresh the page
        exit;
    } else {
        $_SESSION["ErrorMessage"] = "Something went wrong. Try again.";
    }
}

// Fetch player photo from database
$photoSrc = '#';
$sql = "SELECT photo FROM player_details WHERE SouthAfricanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $southAfricanID);
$stmt->execute();
$stmt->bind_result($photo);
if ($stmt->fetch() && $photo) {
    $photoSrc = 'data:image/jpeg;base64,' . base64_encode($photo);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Details - MYSAFA</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #130f40;
            background-image: linear-gradient(315deg, #130f40 0%, #000000 74%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            animation: gradientAnimation 10s ease infinite;
        }

        .sidebar .links {
            flex-grow: 1;
        }

        .sidebar a {
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            position: relative;
            overflow: hidden;
            transition: background-color 0.3s ease;
        }

        .sidebar a::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2));
            transition: all 0.5s ease-in-out;
        }

        .sidebar a:hover {
            background-color: #3a3a3a;
        }

        .sidebar a:hover::before {
            left: 100%;
        }

        .sidebar .logout {
            margin-bottom: 20px;
            background-color: #130f40;
            color: white;
            border: none;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .sidebar .logout:hover {
            background-color: #cc0000;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
        }

        /* sidebar animation */

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .card {
            margin-top: 20px;
        }

        .card-body img {
            max-width: 100%;
            height: auto;
        }

        .form-group img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        .image-upload-section {
            border: 2px dashed #343a40;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }

        .image-upload-section input[type="file"] {
            display: none;
        }

        .image-upload-section label {
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
            cursor: pointer;
        }

        .image-upload-section button {
            margin-top: 10px;
        }

        /* process timeline styling */

        .registration-timeline {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            background-color: #ccc;
            border-radius: 50%;
            text-align: center;
            font-weight: bold;
            color: #fff;
        }

        .step-name {
            margin-top: 5px;
            font-size: 14px;
            text-align: center;
        }

        .timeline-step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 15px;
            left: calc(50% + 15px);
            width: calc(100% - 30px);
            height: 2px;
            background-color: #ccc;
        }

        .timeline-step.active .step-number {
            background-color: #007bff;
        }

        .timeline-step.completed .step-number {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <h4 class="text-white text-center">Logged in as: <?php echo htmlspecialchars($fullName); ?></h4>
            <div class="links">
                <a href="Leagues.php">Leagues</a>
                <a href="Clubs.php">Clubs</a>
                <a href="player.php">Register Player</a>
                <a href="Player_Details.php">Player Details</a>
                <a href="Player_History.php">Player History</a>
                <a href="Documents.php">Documents</a>
                <a href="Confirmation.php">Confirmation</a>
                <a href="Manage_Club.php">Manage Club</a>
                <a href="Registrations.php">Registrations</a>
            </div>
        </div>
        <a href="Logout.php" class="btn btn-danger logout" role="button">Log Out</a>
    </div>

    <div class="content">
        <div class="container mt-5">
            <!-- process timeline -->
        <div class="container mt-4">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="registration-timeline">
                    <div class="timeline-step" data-step="1">
                        <span class="step-number">1</span>
                        <span class="step-name">Select League</span>
                    </div>
                    <div class="timeline-step" data-step="2">
                        <span class="step-number">2</span>
                        <span class="step-name">Select Club</span>
                    </div>
                    <div class="timeline-step" data-step="3">
                        <span class="step-number">3</span>
                        <span class="step-name">Search Player</span>
                    </div>
                    <div class="timeline-step" data-step="4">
                        <span class="step-number">4</span>
                        <span class="step-name">Player Details</span>
                    </div>
                    <div class="timeline-step" data-step="5">
                        <span class="step-number">5</span>
                        <span class="step-name">Upload Documents</span>
                    </div>
                    <div class="timeline-step" data-step="6">
                        <span class="step-number">6</span>
                        <span class="step-name">Confirm Registration</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- process timeline end -->
            <h2 class="text-center">Player Details</h2>
            <?php echo Message(); ?>
            <?php echo SuccessMessage(); ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?php echo $photoSrc; ?>" alt="Player Photo" class="img-thumbnail">
                            
                            <form method="post" enctype="multipart/form-data">
                                <div class="image-upload-section">
                                    <label for="photo">Click to Upload Image:</label>
                                    <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                                    <img id="photo-preview" style="display:none; max-width: 200px; margin-top: 10px;" class="img-thumbnail">
                                </div>
                                <button type="submit" class="btn btn-primary mt-3" name="upload_photo">Upload</button>
                                
                                <button type="submit" class="btn btn-danger remove-photo" name="remove_photo">Remove Photo</button>
                            </form>
                           
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <form method="post">
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="south_african_id">South African ID:</label>
                            <input type="text" class="form-control" id="south_african_id" name="south_african_id" value="<?php echo htmlspecialchars($southAfricanID); ?>" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="safa_id">SAFA ID:</label>
                            <input type="text" class="form-control" id="safa_id" name="safa_id" value="<?php echo htmlspecialchars($safaID); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact:</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Physical Address:</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" name="save_details">Save Details</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='player.php';">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('photo').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photo-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // functions for process timeline 

        function highlightCurrentStep() {
        const currentPage = window.location.pathname.split("/").pop().replace(".php", "");
        const steps = document.querySelectorAll(".timeline-step");
    
        steps.forEach((step) => {
            const stepNumber = step.getAttribute("data-step");
            const stepName = step.querySelector(".step-name").textContent.replace(" ", "").toLowerCase();
    
            if (currentPage === stepName) {
                step.classList.add("active");
            } else if (parseInt(stepNumber) < getCurrentStepNumber(currentPage)) {
                step.classList.add("completed");
            } else {
                step.classList.remove("active", "completed");
            }
        });
    }
    
    function getCurrentStepNumber(currentPage) {
        const pages = ["Leagues", "Clubs", "player", "Player_Details", "Documents", "Confirmation"];
        return pages.indexOf(currentPage) + 1;
    }

    window.addEventListener("load", highlightCurrentStep);
    
    </script>
</body>
</html>
