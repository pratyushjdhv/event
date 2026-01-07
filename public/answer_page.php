<?php
session_start();

// 1. PHP Redirect: Add this near the top, after session_start()
$status = '';
if (file_exists('status.txt')) {
    $status = trim(file_get_contents('status.txt'));
    if ($status === '5') {
        header("Location: index.php");
        exit;
    }
}

// Handle form submission logic at the top
$message = "";
$msgType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php';
    $conn = getDBConnection('users', 'postgres');

    $answer = $_POST['answer'] ?? '';
    $name = $_SESSION['name'] ?? '';

    if ($name == '') {
        // Redirect if session expired
        header("Location: index.php");
        exit();
    }

    if ($conn && $answer != '') {
        // Update answer and timestamp
        $query = "UPDATE users SET ans = '$answer', time = NOW() WHERE name = '$name'";
        $result = pg_query($conn, $query);

        if ($result) {
            $msgType = "success";
            $message = "<strong>✅ Saved!</strong> Your answer was recorded at " . date('H:i:s');
        } else {
            $msgType = "danger";
            $message = "<strong>❌ Error:</strong> " . pg_last_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Submit Answer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #fff7e6, #f5e6c8);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar {
            background-color: rgba(253, 242, 217, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(68, 26, 2, 0.1);
        }
        .navbar-brand {
            color: #441a02 !important;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .nav-link {
            color: #441a02 !important;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #8a3c04 !important;
            transform: translateY(-1px);
        }
        .answer-card {
            background-color: #fff9e8;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(68, 26, 2, 0.08);
            border: 1px solid rgba(68, 26, 2, 0.05);
        }
        .answer-title {
            color: #441a02;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .answer-desc {
            color: #5e2403;
            font-size: 1.1rem;
        }
        .custom-input {
            border-radius: 12px;
            padding: 15px;
            border: 2px solid #e6d2a8;
            background-color: #fff;
            transition: all 0.3s;
        }
        .custom-input:focus {
            border-color: #441a02;
            box-shadow: 0 0 0 4px rgba(68, 26, 2, 0.1);
        }
        .submit-btn {
            background-color: #441a02;
            color: white;
            padding: 12px 35px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #5e2403;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(68, 26, 2, 0.2);
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="level.php">HOME</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link" href="sql.php">SQL</a>
                <a class="nav-link" href="schema.php">SCHEMA</a>
                <a class="nav-link" href="answer_page.php">ANSWER</a>
            </div>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="col-md-8 answer-card">

        <h2 class="answer-title mb-3">Submit Your Findings</h2>

        <!-- Display Success/Error Message Here -->
        <?php if ($message != ""): ?>
            <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <p class="answer-desc mb-4">
            Once you have identified the culprit, enter their name below. 
            Make sure you are certain—accuracy is key!
        </p>

        <form method="post">
            <div class="mb-4">
                <label for="answer" class="form-label fw-bold" style="color: #441a02;">Culprit's Name</label>
                <input type="text" class="form-control custom-input" id="answer" name="answer" placeholder="e.g. John Doe" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="submit-btn">Submit Answer</button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setInterval(function() {
        // Check status.txt every 3 seconds
        // We add a timestamp (?t=...) to prevent browser caching
        fetch('status.txt?t=' + new Date().getTime())
            .then(response => response.text())
            .then(status => {
                // If status becomes 0, 5, or reset, redirect immediately
                const s = status.trim();
                if (s === '5' || s === '0' || s === 'reset') {
                    window.location.href = 'index.php';
                }
            })
            .catch(err => console.log('Waiting for status update...'));
    }, 3000);
</script>
</body>
</html>
