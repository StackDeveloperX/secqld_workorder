<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
// Database connection
include('includes/connection.php');

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $name = $user['name'];       // adjust field names as per your DB
    $email = $user['email'];     // adjust accordingly
} else {
    // Invalid user_id or deleted user
    session_destroy();
    header("Location: index.php");
    exit;
}

$stmt->close();
$conn->close();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($name); ?> - Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>


    <section class="dashboard">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-sm-2 left-sidebar shadow pt-5 pb-5">
                    <div class="text-center">
                        <img src="assets/images/logo.png" class="logo-image" alt="">
                    </div>
                    <div class="menu mt-5">
                        <a href="dashboard.php"><p>Home</p></a>
                        <p class="toggle-submenu d-flex justify-content-between align-items-center" style="cursor:pointer;">
                            Work Request
                            <i class="bi bi-caret-down-fill caret-icon"></i>
                        </p>
                        <div class="submenu" style="display: none;">
                            <a href="running-sheet.php"><p>Running Sheet</p></a>
                            <p>Search</p>
                        </div>
                        <a href="profile.php"><p class="active_menu">Profile</p></a>
                        <p>Contact Us</p>
                        <p><a href="logout.php">Logout</a></p>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-sm-10 right-sidebar">
                    <nav class="navbar navbar-expand-lg  shadow">
                        <div class="container">
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?php echo htmlspecialchars($name); ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Action</a></li>
                                            <li><a class="dropdown-item" href="#">Another action</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                    <div class="container mt-5">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="card shadow text-center">
                                    <div class="text-center">
                                        <img src="assets/images/search.png" class="card-img" alt="...">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Search Work Request</h5>
                                        <a href="#" class="btn btn-green mt-4">Search</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card shadow text-center">
                                    <div class="text-center">
                                        <img src="assets/images/document.png" class="card-img" alt="...">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Work Request Running Sheet</h5>
                                        <a href="#" class="btn btn-green mt-4">View</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card shadow text-center">
                                    <div class="text-center">
                                        <img src="assets/images/user.svg" class="card-img" alt="...">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Update my Details</h5>
                                        <a href="#" class="btn btn-green mt-4">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.toggle-submenu').on('click', function () {
                let submenu = $(this).next('.submenu');
                let icon = $(this).find('.caret-icon');

                submenu.slideToggle(300);

                // Toggle caret icon class
                icon.toggleClass('bi-caret-down-fill bi-caret-up-fill');
            });
        });
    </script>
    </body>
</html>