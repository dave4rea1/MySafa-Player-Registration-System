<?php 
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

// Check if user is logged in
Confirm_Login();

// Check if FullName is set in session
$fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Player - MYSAFA</title>
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

        .id-card {
            border: 1px solid #000;
            padding: 20px;
            max-width: 500px; /* Decreased width */
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            background: linear-gradient(145deg, #d4f3d8, #ffffff); /* Subtle gradient for depth */
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3); /* 3D shadow effect */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Add some padding for content */
        }

        .id-card .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .id-card .header .title {
            font-size: 20px; /* Increased font size */
            font-weight: bold;
            color: #000; /* Bold color */
        }

        .id-card .header .flag {
            max-width: 50px;
            max-height: 30px;
        }

        .id-card .header .flag img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .id-card .details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .id-card .details .info {
            flex-grow: 1;
        }

        .id-card .details .info p {
            margin: 0;
        }

        .id-card .details img {
            width: 120px;
            height: 160px;
            border: 1px solid #000;
            background-color: #fff;
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
            <h2 class="text-center">Search Player</h2>
            <form method="post" class="mb-4">
                <div class="form-group">
                    <label for="sa_id">South African ID:</label>
                    <input type="text" class="form-control" id="sa_id" name="sa_id" placeholder="Enter South African ID" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.reload();">Refresh</button>
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $sa_id = $_POST["sa_id"];

                // Include database connection
                include 'db_connection.php';

                // Search for player
                $sql = "SELECT * FROM Player WHERE SouthAfricanID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $sa_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Display player details
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='id-card'>";
                        echo "<div class='header'>";
                        echo "<div class='title'>REPUBLIC OF SOUTH AFRICA<br>NATIONAL IDENTITY CARD</div>";
                        echo "<div class='flag'><img src='images/Flag_of_South_Africa.svg.png' alt='South African Flag'></div>";
                        echo "</div>";
                        echo "<div class='details'>";
                        echo "<div class='info'>";
                        echo "<p><strong>Surname:</strong> " . (isset($row["lastName"]) ? $row["lastName"] : "") . "</p>";
                        echo "<p><strong>Name:</strong> " . (isset($row["firstName"]) ? $row["firstName"] : "") . "</p>";
                        echo "<p><strong>Sex:</strong> M</p>"; // Adjust this based on your database structure
                        echo "<p><strong>Nationality:</strong> " . (isset($row["nationality"]) ? $row["nationality"] : "") . "</p>";
                        echo "<p><strong>Id number:</strong> " . (isset($row["SouthAfricanID"]) ? $row["SouthAfricanID"] : "") . "</p>";
                        echo "<p><strong>Date of birth:</strong> " . (isset($row["dateOfBirth"]) ? $row["dateOfBirth"] : "") . "</p>";
                        echo "<p><strong>Status:</strong> Citizen</p>"; // Assuming status is always Citizen
                        echo "</div>";
                        if (isset($row["photo"]) && $row["photo"]) {
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($row["photo"]) . '" alt="Player Photo">';
                        } else {
                            echo '<p>No photo available</p>';
                        }
                        echo "</div>";
                        echo "</div>";
                        echo "<a href='Player_Details.php?sa_id=" . $sa_id . "' class='btn btn-success mt-3'>Proceed</a>";
                        // Store player details in session
                        $_SESSION['FirstName'] = $row['firstName'];
                        $_SESSION['LastName'] = $row['lastName'];
                        $_SESSION['SouthAfricanID'] = $row['SouthAfricanID'];
                        $_SESSION['safaID'] = $row['SAFAID'];
                        $_SESSION['PlayerPhoto'] = isset($row["photo"]) && $row["photo"] ? base64_encode($row["photo"]) : null;
                        // $_SESSION['SAFAID'] = $row['SouthAfricanID'];
                    }
                } else {
                    echo "<p class='alert alert-warning'>No player found with the provided South African ID.</p>";
                }

                // Close connection
                $conn->close();
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
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
