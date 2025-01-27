<?php 
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

// Check if user is logged in
Confirm_Login();

// Check if FullName is set in session
$fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';

// Check if SouthAfricanID is set in session
if (!isset($_SESSION['SouthAfricanID'])) {
    // For testing purposes, set a default value
    // In real-world application, ensure this is set appropriately when the player is registered or logged in
    $_SESSION['SouthAfricanID'] = '9809025800086'; // Default value for testing
}

$SouthAfricanID = $_SESSION['SouthAfricanID'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload_documents'])) {
        // File upload handling
        $certifiedID = file_get_contents($_FILES['certified_id']['tmp_name']);
        $teamRegistration = file_get_contents($_FILES['team_registration']['tmp_name']);
        $medicalForm = file_get_contents($_FILES['medical_form']['tmp_name']);
        $consentForm = file_get_contents($_FILES['consent_form']['tmp_name']);

        // Assign the current date and time to DateUploaded
        $DateUploaded = date('Y-m-d H:i:s');

        // Insert or update document details in the database
        $query = "REPLACE INTO documents (SouthAfricanID, CertifiedID, TeamRegistration, MedicalForm, ConsentForm, DateUploaded)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $SouthAfricanID, $certifiedID, $teamRegistration, $medicalForm, $consentForm, $DateUploaded);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Documents uploaded successfully!";
            header("Location: Confirmation.php"); // Navigate to Confirmation.php after successful upload
            exit();
        } else {
            $_SESSION['error_message'] = "Document upload failed! Please try again.";
        }

    } elseif (isset($_POST['remove_document'])) {
        $documentType = $_POST['document_type'];

        // Update the database to remove the specified document
        $query = "UPDATE documents SET $documentType = NULL WHERE SouthAfricanID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $SouthAfricanID);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Document removed successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to remove document! Please try again.";
        }

        header("Location: Documents.php"); // Refresh the page
        exit();
    }
}

// Fetch existing document details from the database
$query = "SELECT * FROM documents WHERE SouthAfricanID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $SouthAfricanID);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Documents - MYSAFA</title>
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
            max-width: 100%;
            height: auto;
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
            <h2 class="text-center">Upload Documents</h2>
            <?php echo isset($_SESSION['success_message']) ? '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>' : ''; ?>
            <?php echo isset($_SESSION['error_message']) ? '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>' : ''; ?>
            <form method="post" enctype="multipart/form-data" class="mb-4">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Document Type</th>
                            <th>Uploaded File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Certified ID</td>
                            <td>
                                <?php if (!empty($documents['CertifiedID'])): ?>
                                    <a href="view_document.php?type=CertifiedID&SouthAfricanID=<?php echo htmlspecialchars($SouthAfricanID); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    Not Uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($documents['CertifiedID'])): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="document_type" value="CertifiedID">
                                        <button type="submit" name="remove_document" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                <?php endif; ?>
                                <input type="file" name="certified_id" class="form-control-file mt-2">
                            </td>
                        </tr>
                        <tr>
                            <td>Team Registration</td>
                            <td>
                                <?php if (!empty($documents['TeamRegistration'])): ?>
                                    <a href="view_document.php?type=TeamRegistration&SouthAfricanID=<?php echo htmlspecialchars($SouthAfricanID); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    Not Uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($documents['TeamRegistration'])): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="document_type" value="TeamRegistration">
                                        <button type="submit" name="remove_document" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                <?php endif; ?>
                                <input type="file" name="team_registration" class="form-control-file mt-2">
                            </td>
                        </tr>
                        <tr>
                            <td>Medical Form</td>
                            <td>
                                <?php if (!empty($documents['MedicalForm'])): ?>
                                    <a href="view_document.php?type=MedicalForm&SouthAfricanID=<?php echo htmlspecialchars($SouthAfricanID); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    Not Uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($documents['MedicalForm'])): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="document_type" value="MedicalForm">
                                        <button type="submit" name="remove_document" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                <?php endif; ?>
                                <input type="file" name="medical_form" class="form-control-file mt-2">
                            </td>
                        </tr>
                        <tr>
                            <td>Consent Form</td>
                            <td>
                                <?php if (!empty($documents['ConsentForm'])): ?>
                                    <a href="view_document.php?type=ConsentForm&SouthAfricanID=<?php echo htmlspecialchars($SouthAfricanID); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    Not Uploaded
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($documents['ConsentForm'])): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="document_type" value="ConsentForm">
                                        <button type="submit" name="remove_document" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                <?php endif; ?>
                                <input type="file" name="consent_form" class="form-control-file mt-2">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" name="upload_documents" class="btn btn-primary">Upload Documents</button>
            </form>
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
