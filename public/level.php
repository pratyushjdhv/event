<?php
session_start();

// Redirect if session variables are not set
if (!isset($_SESSION['level_title'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        body {
            background: linear-gradient(180deg, #fff7e6, #f5e6c8);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background-color: rgba(255, 247, 230, 0.95) !important;
            border-bottom: 1px solid rgba(68, 26, 2, 0.1);
        }

        .navbar-brand {
            color: #441a02 !important;
            font-weight: 800;
        }

        .nav-link {
            color: #441a02 !important;
            font-weight: 600;
        }

        .card {
            border-radius: 16px;
            background-color: #fffaf0;
            border: none;
            box-shadow: 0 10px 30px rgba(68, 26, 2, 0.05);
            margin-bottom: 2rem;
        }

        .card-body {
            padding: 2rem;
        }

        h4,
        h5 {
            color: #441a02;
            font-weight: 700;
        }

        .btn-primary {
            background-color: #441a02;
            border-color: #441a02;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #5e2403;
            border-color: #5e2403;
        }

        .form-control {
            border: 2px solid #e6d2a8;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control:focus {
            border-color: #441a02;
            box-shadow: 0 0 0 4px rgba(68, 26, 2, 0.1);
        }
    </style>


</head>



<body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y"
        crossorigin="anonymous"></script>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="level.php">HOME</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
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
    <div class="container mt-4">

        <!-- Case Brief Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h4 class="mb-3"><?php echo htmlspecialchars($_SESSION['level_title']); ?></h4>

                <p>
                    <?php echo htmlspecialchars($_SESSION['level_description']); ?>
                </p>

                <p>
                <h4 class="mb-3">Objectives </h4>

                <?php echo $_SESSION['level_objectives']; ?>
                </p>

            </div>
        </div>

        <!-- Name Input Card -->
        <div class="card shadow-sm">
            <div class="card-body">

                <h5 class="mb-3">🕵️ Detective Details</h5>

                <form method="post">
                    <input type="text" name="name" class="form-control mb-3" placeholder="Enter your name" required>

                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                </form>

            </div>
        </div>

    </div>

    <?php

    // PostgreSQL connection
    include 'db.php';
    $conn = getDBConnection('users', 'postgres');

    if (!$conn) {
        die("Database connection failed");
    }

    // Get input
    $name = $_POST['name'] ?? '';
    if ($name != '') {
        $_SESSION['name'] = $name;
        // Insert into database
        $query = "INSERT INTO users (name) VALUES ('$name')";
        $result = @pg_query($conn, $query); // Use @ to suppress the warning

        if ($result) {
            echo '
            <div class="container mt-3">
                <div class="alert alert-dismissible fade show shadow-sm" role="alert" 
                     style="background-color: #fff9e8; border-left: 5px solid #441a02; color: #441a02;">
                    <strong>👋 Hello ' . htmlspecialchars($name) . '!</strong> Your name is saved!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>';
        } else {
            $error = pg_last_error($conn);
            if (strpos($error, 'users_name_key') !== false) {
                echo '
                <div class="container mt-3">
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" 
                         style="border-left: 5px solid #ffc107; color: #856404;">
                        <strong>⚠️ Username Taken!</strong> The name <strong>' . htmlspecialchars($name) . '</strong> is already registered. Please try a different name.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>';
            }
        }
    }
    ?>

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
