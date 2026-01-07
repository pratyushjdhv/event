<?php
session_start();
if (!isset($_SESSION['level_db_name'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>SQL Challenge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: linear-gradient(180deg, #fff7e6, #f5e6c8);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
        background-color: rgba(255, 243, 220, 0.95) !important;
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

    h2, h4 {
        color: #441a02;
        font-weight: 700;
    }

    .form-control {
        border: 2px solid #e6d2a8;
        border-radius: 12px;
        background-color: #fff;
        font-family: 'Courier New', monospace; /* Code font for SQL */
    }

    .form-control:focus {
        border-color: #441a02;
        box-shadow: 0 0 0 4px rgba(68, 26, 2, 0.1);
    }

    .btn-primary {
        background-color: #441a02;
        border-color: #441a02;
        padding: 10px 30px;
        border-radius: 10px;
        font-weight: 600;
        margin-top: 10px;
    }

    .btn-primary:hover {
        background-color: #5e2403;
        border-color: #5e2403;
    }

    /* Table Styling */
    .table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .table thead th {
        background-color: #441a02;
        color: #fff;
        border: none;
        padding: 15px;
    }
    
    .table tbody td {
        background-color: #fff;
        border-color: #f0e0c0;
        color: #441a02;
    }
</style>





</head>

<body>

<!-- NAVBAR -->
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
                    <a class="nav-link" href="answer_page.php">ANSWER</a>
                    <a class="nav-link" href="schema.php">SCHEMA</a>
                </div>
            </div>
        </div>
    </nav>

<div class="container mt-4">

    <h2>SQL Murder Mystery</h2>
    <p>Write your SQL query below:</p>

    <form method="post">
        <textarea name="user_sql"
                  class="form-control"
                  rows="3"
                  placeholder="SELECT * FROM table_name WHERE condition;"
                  required><?php echo htmlspecialchars($_POST['user_sql'] ?? ''); ?></textarea>
        <br>
        <input type="submit" class="btn btn-primary" value="Run SQL">
    </form>

<?php
/* ================= DATABASE CONNECTION ================= */

// IMPORTANT:
// Ideally this should be a READ-ONLY user and CASE database
include 'db.php';
$conn = getDBConnection($_SESSION['level_db_name'] , 'sqluser');

if (!$conn) {
    die("<p class='text-danger'>Database connection failed</p>");
}

/* ================= RUN USER QUERY ================= */

$user_sql = $_POST['user_sql'] ?? '';

if ($user_sql != '') {

    $result = pg_query($conn, $user_sql);

    if (!$result) {
        echo "<div class='alert alert-danger mt-3'>
                Error: " . pg_last_error($conn) . "
              </div>";
    } else {

        $num_rows = pg_num_rows($result);

        if ($num_rows == 0) {
            echo "<div class='alert alert-warning mt-3'>
                    Query executed successfully, but no records found.
                  </div>";
        } else {

            echo "<h4 class='mt-4'>Query Result</h4>";
            
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped table-hover mt-2'>";

            /* ---------- TABLE HEADER ---------- */
            echo "<thead><tr>";
            $num_fields = pg_num_fields($result);
            for ($i = 0; $i < $num_fields; $i++) {
                echo "<th scope='col'>" . pg_field_name($result, $i) . "</th>";
            }
            echo "</tr></thead>";

            /* ---------- TABLE BODY ---------- */
            echo "<tbody>";
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";

            echo "</table>";
            echo "</div>";
        }
    }
}
?>

</div>

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
