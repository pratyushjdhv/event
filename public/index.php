<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(180deg, #fff7e6, #f5e6c8);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: rgba(255, 247, 230, 0.95) !important;
            border-bottom: 1px solid rgba(68, 26, 2, 0.1);
        }

        .navbar-brand {
            color: #441a02 !important;
            font-weight: 800;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .menu-card {
            background-color: #fffaf0;
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(68, 26, 2, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        h1 {
            color: #441a02;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            color: #8c6b5d;
            margin-bottom: 2rem;
        }

        .btn-round {
            background-color: #fff;
            color: #441a02;
            border: 2px solid #e6d2a8;
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            width: 100%;
            transition: all 0.2s ease;
            display: block;
            text-decoration: none;
        }

        .btn-round:hover {
            background-color: #441a02;
            color: #fff;
            border-color: #441a02;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(68, 26, 2, 0.2);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">EVENT PORTAL</a>
        </div>
    </nav>

    <div class="container main-content">
        <div class="menu-card">
            <h1>Welcome</h1>
            <p class="subtitle">Select the active round to begin</p>

            <div class="d-grid gap-2">
                <?php
                $status = '';
                // Read the status file
                if (file_exists('status.txt')) {
                    $status = trim(file_get_contents('status.txt'));
                }

                // 1. Check for RESET command
                if ($status === 'reset') {
                    include_once 'db.php';
                    $conn = getDBConnection('users', 'postgres');
                    
                    if ($conn) {
                        // Clear the database automatically
                        $query = "TRUNCATE TABLE users RESTART IDENTITY";
                        @pg_query($conn, $query);
                    }

                    echo '
                    <div class="alert alert-info" role="alert" style="background-color: #e8f4f8; border-color: #b8daff; color: #004085;">
                        <strong>🔄 System Reset</strong><br>
                        The previous round data has been cleared. Please wait for the host to start the next level.
                    </div>
                    <button onclick="location.reload()" class="btn btn-outline-dark btn-sm w-100 mt-2">Check for Updates</button>';
                } 
                // 2. Normal Round Logic
                else {
                    $round = (int)$status;

                    // Show button based on the number in status.txt
                    switch ($round) {
                        case 1:
                            $_SESSION['level_db_name'] = 'level_1';
                            $_SESSION['level_title'] = 'The Vanishing Briefcase';
                            $_SESSION['level_description'] = 'Set in the gritty 1980s, a valuable briefcase has disappeared from the Blue Note Lounge. A witness reported that a man in a trench coat was seen fleeing the scene. Investigate the crime scene, review the list of suspects, and examine interview transcripts to reveal the culprit.';
                            $_SESSION['level_objectives'] = '1.Retrieve the correct crime scene details to gather the key clue.<br>2.Identify the suspect whose profile matches the witness description. <br>3.Verify the suspect using their interview transcript. <br>';
                            echo '<a href="level.php" class="btn-round">Start Level 1</a>';
                            break;
                        case 2:
                            $_SESSION['level_db_name'] = 'level_2';
                            $_SESSION['level_title'] = 'The Stolen Sound';
                            $_SESSION['level_description'] = 'A body was found floating near the docks of Coral Bay Marina in the early hours of August 14, 1986. Your job, detective, is to find the murderer and bring them to justice. This case might require the use of JOINs, wildcard searches, and logical deduction. Get to work, detective.';
                            $_SESSION['level_objectives'] = '1.Retrieve the crime scene report for the record theft using the known date and location. <br>2.Retrieve witness records linked to that crime scene to obtain their clues. <br>3.Use the clues from the witnesses to find the suspect in the suspects table.<br>4.Retrieve the suspect\'s interview transcript to confirm the confession.<br>';
                            echo '<a href="level.php" class="btn-round">Start Level 2</a>';
                            break;
                        case 3:
                            $_SESSION['level_db_name'] = 'final_lvl1';
                            $_SESSION['level_title'] = 'The Miami Marina Murder';
                            $_SESSION['level_description'] = 'On October 31, 1987, at a Coconut Grove mansion masked ball, Leonard Pierce was found dead in the garden. Can you piece together all the clues to expose the true murderer?';
                            $_SESSION['level_objectives'] = '1. Find the murderer. ( Start by finding the crime scene and go from there ) <br>';
                            echo '<a href="level.php" class="btn-round">Start Final Level 1</a>';
                            break;
                        case 4:
                            $_SESSION['level_db_name'] = 'final_lvl2';
                            $_SESSION['level_title'] = ' The Vanishing Diamond';
                            $_SESSION['level_description'] = 'At Miami’s prestigious Fontainebleau Hotel charity gala, the famous “Heart of Atlantis” diamond necklace suddenly disappeared from its display.';
                            $_SESSION['level_objectives'] = '1.Find who stole the diamond. <br>';
                            echo '<a href="level.php" class="btn-round">Start Final Level 2</a>';
                            break;
                        
                        // Case 5 for Results
                        case 5:
                            include_once 'db.php';
                            $conn = getDBConnection('users', 'postgres');
                            
                            echo '<h2 class="mb-4" style="color:#441a02;">🏆 Round Winners 🏆</h2>';
                            
                            if ($conn) {
                                // 1. Read the correct answer from file
                                $correct_ans = '';
                                if (file_exists('correct_answer.txt')) {
                                    $correct_ans = trim(file_get_contents('correct_answer.txt'));
                                }

                                // 2. Build Query based on whether an answer is set
                                if ($correct_ans !== '') {
                                    // Filter: Only show users with the EXACT answer (Case Insensitive)
                                    $safe_ans = pg_escape_string($conn, $correct_ans);
                                    $sql = "SELECT name, time FROM users WHERE LOWER(ans) = LOWER('$safe_ans') AND time IS NOT NULL ORDER BY time ASC LIMIT 10";
                                    
                                    echo '<div class="alert alert-success py-2 mb-3">Showing winners for answer: <strong>' . htmlspecialchars($correct_ans) . '</strong></div>';
                                } else {
                                    // Fallback: If file is empty, show all submissions
                                    $sql = "SELECT name, time FROM users WHERE ans IS NOT NULL AND time IS NOT NULL ORDER BY time ASC LIMIT 10";
                                }

                                $result = pg_query($conn, $sql);
                                
                                if (pg_num_rows($result) > 0) {
                                    echo '<div class="list-group shadow-sm">';
                                    $rank = 1;
                                    while ($row = pg_fetch_assoc($result)) {
                                        $medal = "";
                                        if($rank == 1) $medal = "🥇";
                                        elseif($rank == 2) $medal = "🥈";
                                        elseif($rank == 3) $medal = "🥉";
                                        
                                        // Format time only showing H:M:S
                                        $timeDisplay = date('H:i:s', strtotime($row['time']));
                                        
                                        echo '
                                        <div class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #fff; border-color: #e6d2a8;">
                                            <span class="fw-bold" style="color:#441a02; font-size: 1.2rem;">' . $medal . ' ' . $rank . '. ' . htmlspecialchars($row['name']) . '</span>
                                            <span class="badge rounded-pill" style="background-color: #441a02;">' . $timeDisplay . '</span>
                                        </div>';
                                        $rank++;
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-warning">No submissions yet!</div>';
                                }

                                echo '<a href="index.php" class="btn btn-outline-dark btn-sm w-100 mt-3">Refresh Leaderboard</a>';
                            }
                            break;

                        default:
                            echo '
                            <div class="alert alert-warning" role="alert" style="background-color: #fff9e8; border-color: #e6d2a8; color: #856404;">
                                <strong>⏸️ Event Paused</strong><br>
                                Please wait for the host to start the next round.
                            </div>
                            <button onclick="location.reload()" class="btn btn-outline-dark btn-sm w-100">Check for Updates</button>';
                            break;
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const initialStatus = "<?php echo $status; ?>";
        setInterval(function() {
            fetch('status.txt?t=' + new Date().getTime())
                .then(response => response.text())
                .then(status => {
                    status = status.trim();
                    if (status !== initialStatus || ['0', '5', 'reset'].includes(status)) {
                        location.reload();
                    }
                })
                .catch(err => console.log('Waiting for status update...'));
        }, 3000);
    </script>
</body>

</html>