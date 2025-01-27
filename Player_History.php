<?php 
        require_once("Include/Session.php");
        require_once("Include/Functions.php");
        require_once("db_connection.php");

        // Check if user is logged in
        Confirm_Login();

        // Check if FullName is set in session
        $fullName = isset($_SESSION['FullName']) ? $_SESSION['FullName'] : 'Guest';

        // Generate a unique alphanumeric code for the SAFA ID
        function generateSAFAID() {
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $sa_fa_id = '';
            for ($i = 0; $i < 6; $i++) { // Generate a 6 character long alphanumeric string
                $sa_fa_id .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $sa_fa_id;
        }

        $sa_id = $_GET['sa_id'] ?? '';
        $sa_id = isset($_SESSION['SouthAfricanID']) ? $_SESSION['SouthAfricanID'] : '';

        if ($sa_id) {
            // Fetch player history
            $sql = "SELECT ph.Year, ph.Goals, ph.RedCards, ph.Injury, ph.ClubName, ph.LeagueName
                    FROM player_history ph
                    WHERE ph.SouthAfricanID = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $sa_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $playerHistory = [];
            while ($row = $result->fetch_assoc()) {
                $playerHistory[] = $row;
            }
            
            $stmt->close();
            $conn->close();
        }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player History - MYSAFA</title>
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
        <a href="Logout.php" class="btn btn-danger logout" role="button">Log Out</a> <!-- Log Out Button -->
    </div>

    <div class="content">
        <div class="container mt-5">
            <h2 class="text-center">Player History</h2>

            <?php if ($sa_id && $playerHistory): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Club</th>
                            <th>League</th>
                            <th>Goals</th>
                            <th>Red Cards</th>
                            <th>Injury</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playerHistory as $history): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($history['Year']); ?></td>
                                <td><?php echo htmlspecialchars($history['ClubName']); ?></td>
                                <td><?php echo htmlspecialchars($history['LeagueName']); ?></td>
                                <td><?php echo htmlspecialchars($history['Goals']); ?></td>
                                <td><?php echo htmlspecialchars($history['RedCards']); ?></td>
                                <td><?php echo htmlspecialchars($history['Injury']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No player history found for the provided South African ID.</p>
            <?php endif; ?>

            <a href="Documents.php" class="btn btn-success mt-3">Proceed</a>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
