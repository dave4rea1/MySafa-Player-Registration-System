<?php 
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Include/PHPMailer/src/Exception.php';
require 'Include/PHPMailer/src/PHPMailer.php';
require 'Include/PHPMailer/src/SMTP.php';

// Check if user is logged in
Confirm_Login();

// Check if FullName is set in session
$fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';

// Fetch selections from session or database
$sa_id = isset($_SESSION['SouthAfricanID']) ? $_SESSION['SouthAfricanID'] : '';
$safa_id = isset($_SESSION['safaID']) ? $_SESSION['safaID'] : '';
$firstName = isset($_SESSION['FirstName']) ? $_SESSION['FirstName'] : '';
$lastName = isset($_SESSION['LastName']) ? $_SESSION['LastName'] : '';
$club_name = isset($_SESSION['SelectedClub']) ? $_SESSION['SelectedClub'] : '';
$league_name = isset($_SESSION['SelectedLeague']) ? $_SESSION['SelectedLeague'] : '';
$email = isset($_SESSION['emailAddress']) ? $_SESSION['emailAddress'] : '';

// Fetch player's photograph and documents from the database
$query = "SELECT p.photo, d.CertifiedID, d.TeamRegistration, d.MedicalForm, d.ConsentForm 
          FROM player p
          LEFT JOIN documents d ON p.SouthAfricanID = d.SouthAfricanID
          WHERE p.SouthAfricanID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sa_id);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_assoc();

// Save registration information if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = 'Pending';
    $confirmation_code = bin2hex(random_bytes(16)); // Generate a confirmation code

    $sql = "INSERT INTO registration (SouthAfricanID, SAFAID, ClubName, LeagueName, Status, Email, ConfirmationCode) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $sa_id, $safa_id, $club_name, $league_name, $status, $email, $confirmation_code);
    
    if ($stmt->execute()) {
        // Send confirmation email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = 0; 
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kingdayve07@gmail.com'; 
            $mail->Password = 'lvncwjqqhrsksdyk'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Sender info
            $mail->setFrom('kingdayve07@gmail.com', 'mySAFA');

            // Receiver info
            $mail->addAddress($email, $firstName);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Player Registration Confirmation';
            $mail->Body = "Dear $firstName,<br><br>
                            You are about to get registered for the <strong>$league_name</strong> league with the 
                            <strong>$club_name</strong> club.<br><br>
                            Please click the button below to confirm your registration within 24 hours.
                            If you do not confirm within 24 hours, your registration will be canceled:<br><br>
                            <form action='http://localhost/mysafa/confirm_registration.php' method='GET'>
                                <input type='hidden' name='code' value='$confirmation_code'>
                                <button type='submit'>Confirm Registration</button>
                            </form><br><br>
                            Thank you,<br>MYSAFA Team";

            // Send email
            $mail->send();
            $_SESSION['success_message'] = "Registration successful pending player approval! Please check email to confirm registration.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error_message'] = "Registration failed! Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation - MYSAFA</title>
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

        .player-photo {
            max-width: 200px;
            height: auto;
            float: right;
            margin-left: 20px;
        }

        .document-link {
            margin-top: 10px;
        }

        .summary {
            margin-top: 20px;
        }

        .summary div {
            margin-bottom: 10px;
        }

        .summary label {
            font-weight: bold;
        }

        .print-button {
            margin-top: 20px;
        }

        @media print {
            .sidebar, .print-button, form {
                display: none;
            }
            .content {
                margin: 0;
                padding: 0;
            }
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
    <script>
        function printPOR() {
            window.print();
        }
    </script>
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
        <div class="container mt-5">
            <h2 class="text-center">Confirmation</h2>
            <?php if (!empty($documents['photo'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($documents['photo']); ?>" alt="Player Photo" class="player-photo">
            <?php endif; ?>
            <div class="summary">
                <div>
                    <label>First Name:</label>
                    <span><?php echo htmlspecialchars($firstName); ?></span>
                </div>
                <div>
                    <label>Last Name:</label>
                    <span><?php echo htmlspecialchars($lastName); ?></span>
                </div>
                <div>
                    <label>South African ID:</label>
                    <span><?php echo htmlspecialchars($sa_id); ?></span>
                </div>
                <div>
                    <label>SAFA ID:</label>
                    <span><?php echo htmlspecialchars($safa_id); ?></span>
                </div>
                <div>
                    <label>Club Name:</label>
                    <span><?php echo htmlspecialchars($club_name); ?></span>
                </div>
                <div>
                    <label>League Name:</label>
                    <span><?php echo htmlspecialchars($league_name); ?></span>
                </div>
                <div>
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($email); ?></span>
                </div>
                <?php if (!empty($documents['CertifiedID'])): ?>
                    <div class="document-link">
                        <label>Certified ID:</label>
                        <a href="view_document.php?type=CertifiedID&SouthAfricanID=<?php echo htmlspecialchars($sa_id); ?>" target="_blank">View</a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($documents['TeamRegistration'])): ?>
                    <div class="document-link">
                        <label>Team Registration:</label>
                        <a href="view_document.php?type=TeamRegistration&SouthAfricanID=<?php echo htmlspecialchars($sa_id); ?>" target="_blank">View</a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($documents['MedicalForm'])): ?>
                    <div class="document-link">
                        <label>Medical Form:</label>
                        <a href="view_document.php?type=MedicalForm&SouthAfricanID=<?php echo htmlspecialchars($sa_id); ?>" target="_blank">View</a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($documents['ConsentForm'])): ?>
                    <div class="document-link">
                        <label>Consent Form:</label>
                        <a href="view_document.php?type=ConsentForm&SouthAfricanID=<?php echo htmlspecialchars($sa_id); ?>" target="_blank">View</a>
                    </div>
                <?php endif; ?>
            </div>
            <form method="post">
                <button type="submit" class="btn btn-primary">Confirm Registration</button>
            </form>
            <button class="btn btn-secondary print-button" onclick="printPOR()">Print Proof of Registration</button>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success mt-4">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger mt-4">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <a href="Documents.php" class="btn btn-success mt-3">Back to Documents</a>
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
