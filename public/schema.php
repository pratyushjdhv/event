<?php
session_start();
if (!isset($_SESSION['level_db_name'])) {
    header("Location: index.php");
    exit;
}

include 'db.php';
$conn = getDBConnection($_SESSION['level_db_name'] , 'sqluser');
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">


    <title>Schema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(180deg, #fff7e6, #f5e6c8);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }

    /* Modern Navbar */
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

    h2 {
        color: #441a02;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    /* Custom Dropdown Button styled like the Schema Card */
    .btn-schema-dropdown {
        background-color: #fff9e8;
        color: #441a02;
        
        /* The signature thick left border */
        border-left: 6px solid #441a02;
        border-right: 1px solid rgba(68, 26, 2, 0.05);
        border-top: 1px solid rgba(68, 26, 2, 0.05);
        border-bottom: 1px solid rgba(68, 26, 2, 0.05);
        
        border-radius: 12px;
        padding: 20px;
        font-size: 18px;
        font-weight: 600;
        text-align: left;
        width: 100%;
        
        /* Flexbox to align text and arrow */
        display: flex;
        justify-content: space-between;
        align-items: center;
        
        box-shadow: 0 10px 20px rgba(68, 26, 2, 0.05);
        transition: all 0.3s ease;
    }

    .btn-schema-dropdown:hover, 
    .btn-schema-dropdown:focus, 
    .btn-schema-dropdown[aria-expanded="true"] {
        background-color: #fff;
        color: #441a02;
        transform: translateX(5px);
        box-shadow: 0 15px 30px rgba(68, 26, 2, 0.1);
        border-color: rgba(68, 26, 2, 0.1);
    }

    /* Style the dropdown arrow */
    .btn-schema-dropdown::after {
        color: #441a02;
        margin-left: 15px;
        transform: scale(1.2);
    }

    /* Dropdown Menu Styling */
    .dropdown-menu {
        background-color: #fff;
        border: 1px solid rgba(68, 26, 2, 0.1);
        box-shadow: 0 10px 30px rgba(68, 26, 2, 0.1);
        border-radius: 12px;
        padding: 10px;
        margin-top: 8px !important;
        border-left: 6px solid #e6d2a8; /* Lighter accent for the menu */
    }

    .dropdown-item-text {
        color: #5e2403;
        padding: 8px 15px;
        font-size: 16px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(68, 26, 2, 0.05);
    }
    
    .dropdown-item-text:last-child {
        border-bottom: none;
    }

    .col-type {
        color: #8a3c04;
        font-size: 0.85em;
        margin-left: 10px;
        font-style: italic;
        background: #fff7e6;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .badge-pk {
        background-color: #441a02;
        color: #fff;
        font-size: 0.7em;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 8px;
        font-weight: bold;
    }

    .badge-fk {
        background-color: #8a3c04;
        color: #fff;
        font-size: 0.7em;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 8px;
        font-weight: bold;
    }
    </style>
</head>

<body>

    <!-- Bootstrap JS Bundle (Required for Dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

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
<div class="container mt-5">
    <h2 class="mb-4">Database Tables</h2>
    <div class="row">
        <div class="col-md-8">
<?php
$result = pg_query($conn,"
    SELECT table_name
    FROM information_schema.tables
    WHERE table_schema='public'
");

while ($row = pg_fetch_assoc($result)) {
    $tableName = $row['table_name'];
    
    // Get columns with constraint info
    $colQuery = "
        SELECT 
            c.column_name, 
            c.data_type,
            tc.constraint_type
        FROM information_schema.columns c
        LEFT JOIN information_schema.key_column_usage kcu 
            ON c.table_name = kcu.table_name 
            AND c.column_name = kcu.column_name
        LEFT JOIN information_schema.table_constraints tc 
            ON kcu.constraint_name = tc.constraint_name
        WHERE c.table_name = '$tableName'
    ";
    
    $colResult = pg_query($conn, $colQuery);
    
    echo '
    <div class="mb-3">
        <div class="dropdown">
            <button class="btn btn-schema-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                ' . htmlspecialchars($tableName) . '
            </button>
            <ul class="dropdown-menu w-100">
    ';
    
    while ($col = pg_fetch_assoc($colResult)) {
        $pkBadge = ($col['constraint_type'] == 'PRIMARY KEY') ? '<span class="badge-pk">PK</span>' : '';
        $fkBadge = ($col['constraint_type'] == 'FOREIGN KEY') ? '<span class="badge-fk">FK</span>' : '';
        
        echo '
        <li class="dropdown-item-text">
            ' . htmlspecialchars($col['column_name']) . '
            <span class="col-type">' . htmlspecialchars($col['data_type']) . '</span>
            ' . $pkBadge . $fkBadge . '
        </li>';
    }
    
    echo '
            </ul>
        </div>
    </div>';
}
?>
        </div>
    </div>
</div>

    <script>
        setInterval(function() {
            // Check status.txt every 3 seconds
            // We add a timestamp (?t=...) to prevent browser caching
            fetch('status.txt?t=' + new Date().getTime())
                .then(response => response.text())
                .then(status => {
                    // If status becomes 5 (Winners), redirect immediately
                    if (status.trim() === '5') {
                        window.location.href = 'index.php';
                    }
                })
                .catch(err => console.log('Waiting for status update...'));
        }, 3000);
    </script>
</body>
</html>
