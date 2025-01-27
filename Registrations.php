<?php 
require_once("Include/Session.php");
require_once("Include/Functions.php");
require_once("db_connection.php");

// Check if user is logged in
Confirm_Login();

// Check if FullName is set in session
$fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';

// Fetch registrations from the database
$sql = "SELECT RegistrationID, SouthAfricanID, SAFAID, LeagueName, ClubName, Status FROM registration";
$result = $conn->query($sql);

// Fetch status counts for pie chart
$statusCountsSql = "SELECT Status, COUNT(*) as count FROM registration GROUP BY Status";
$statusCountsResult = $conn->query($statusCountsSql);

$statusCounts = [
    'Confirmed' => 0,
    'Pending' => 0,
];
if ($statusCountsResult && $statusCountsResult->num_rows > 0) {
    while ($row = $statusCountsResult->fetch_assoc()) {
        if ($row['Status'] === 'Confirmed') {
            $statusCounts['Confirmed'] = $row['count'];
        } elseif ($row['Status'] === 'Pending') {
            $statusCounts['Pending'] = $row['count'];
        }
    }
}

// Check if any registration exists
$registrations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registrations[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrations - MYSAFA</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .table-container {
            margin-top: 20px;
        }

        .chart-container {
            width: 50%;
            margin: 0 auto;
            padding-top: 20px;
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
        <div class="container">
            <h2 class="text-center">Registrations</h2>
            <div class="d-flex justify-content-between mb-3">
                <input id="search" type="text" class="form-control w-25" placeholder="Search...">
                <button class="btn btn-success" onclick="printTable()">Print</button>
            </div>
            <div class="table-container">
                <?php if (!empty($registrations)): ?>
                    <table class="table table-bordered" id="registrationsTable">
                        <thead>
                            <tr>
                                <th>Registration ID</th>
                                <th>South African ID</th>
                                <th>SAFA ID</th>
                                <th>League Name</th>
                                <th>Club Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $registration): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registration['RegistrationID']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['SouthAfricanID']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['SAFAID']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['LeagueName']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['ClubName']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['Status']); ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm edit-button" data-id="<?php echo $registration['RegistrationID']; ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-button" data-id="<?php echo $registration['RegistrationID']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No registrations found.</p>
                <?php endif; ?>
            </div>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Function to filter table rows based on search input
        document.getElementById("search").addEventListener("keyup", function() {
            var value = this.value.toLowerCase();
            var rows = document.getElementById("registrationsTable").getElementsByTagName("tr");
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var match = false;
                for (var j = 0; j < cells.length - 1; j++) { // Exclude last cell (actions column)
                    if (cells[j].innerHTML.toLowerCase().indexOf(value) > -1) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? "" : "none";
            }
        });

        // Function to print the table
        function printTable() {
            var divToPrint = document.getElementById("registrationsTable").cloneNode(true);
            var actions = divToPrint.getElementsByTagName("td");
            for (var i = 0; i < actions.length; i++) {
                if (actions[i].lastChild.nodeName == "BUTTON") {
                    actions[i].innerHTML = '';
                }
            }
            var newWin = window.open("");
            newWin.document.write(divToPrint.outerHTML);
            newWin.print();
            newWin.close();
        }

        // Event listeners for edit and delete buttons
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".edit-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    var registrationID = this.getAttribute("data-id");
                    // Add your edit functionality here
                    alert("Edit registration ID: " + registrationID);
                });
            });

            document.querySelectorAll(".delete-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    var registrationID = this.getAttribute("data-id");
                    // Add your delete functionality here
                    alert("Delete registration ID: " + registrationID);
                });
            });
        });

        // Render the pie chart for registration statuses
        var ctx = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Confirmed', 'Pending'],
                datasets: [{
                    data: [<?php echo $statusCounts['Confirmed']; ?>, <?php echo $statusCounts['Pending']; ?>],
                    backgroundColor: ['#28a745', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            }
        });
    </script>
</body>
</html>
