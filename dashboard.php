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
    $job_title = $user['job_title'];     // adjust accordingly
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        
        <section class="dashboard">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-2 sidebar">
                        <div class="logo text-center">
                            <img src="assets/images/logo_white.png" alt="">
                        </div>
                        <div class="menu_list">
                            <a href="dashboard.php"><p class="active-menu"><span><i class="fa-solid fa-house"></i> Home</span></p></a>
                            <a href="running-sheet.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Work Request </span></p></a>
                            <!-- <a href="recurring.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Recurring WO </span></p></a>
                            <p class="menu"><span><i class="fa-solid fa-circle-user"></i> Profile</span></p>
                            <p class="menu"><span><i class="fa-solid fa-phone-volume"></i> Contact Us</span></p>
                            <p class="menu"><span><i class="fa-solid fa-gear"></i> Settings</span></p> -->
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <!-- <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($name); ?></p>
                                <p class="role"><?php echo htmlspecialchars($job_title); ?></p>
                            </div>
                            
                        </div> -->
                    </div>
                    <div class="col-sm-10 main-screen">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="page-title">Welcome</h3>
                                <p id="clock"></p>
                            </div>
                            <!-- <div class="col-sm-3">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <img src="assets/images/user.png" width="60px" alt="">
                                    </div>
                                    <div class="col-sm-9 userinfor">
                                        <p class="user-title"><?php echo htmlspecialchars($name); ?></p>
                                        <p class="user-role"><?php echo htmlspecialchars($job_title); ?></p>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card outer-card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="card card-green shadow">
                                                    <div class="card-body">
                                                        <h3 class="card-main-title">Search Work <br> Request</h3>
                                                        <button class="btn btn-green "><i class="fa-solid fa-search"></i> Search</button>
                                                    </div>
                                                    <!-- Image in bottom right -->
                                                    <img src="assets/images/1.png" alt="search icon" class="card-img-icon">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="card card-green shadow">
                                                    <div class="card-body">
                                                        <h3 class="card-main-title">Work Request <br> Running Sheet</h3>
                                                        <a href="running-sheet.php" class="btn btn-green "><i class="fa-solid fa-eye"></i> View</a>
                                                    </div>
                                                    <!-- Image in bottom right -->
                                                    <img src="assets/images/2.png" alt="search icon" class="card-img-icon">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="card card-green shadow">
                                                    <div class="card-body">
                                                        <h3 class="card-main-title">Update My <br> Details</h3>
                                                        <button class="btn btn-green "><i class="fa-solid fa-user"></i> View Profile</button>
                                                    </div>
                                                    <!-- Image in bottom right -->
                                                    <img src="assets/images/3.png" alt="search icon" class="card-img-icon">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-4">
                                <div class="card outer-card pstatus">
                                    <div class="card-body">
                                        <h3 class="main-title">Priority Status</h3>
                                        <hr>
                                        <div class="text-center">
                                            <img src="assets/images/ps.png" alt="">
                                            <p class="sub-content">Track and manage your tasks by priority levels.</p>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-4">
                                <div class="card outer-card pstatus">
                                    <div class="card-body">
                                        <h3 class="main-title">Status</h3>
                                        <hr>
                                        <div class="text-center">
                                            <img src="assets/images/status.png" class="status-img" alt="">
                                            <p class="sub-content">Status board to identify where attention is needed most.</p>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-4">
                                <div class="card profile_card">
                                    <div class="card-body text-center">
                                        <img src="assets/images/Ellipse 18.png" class="profile_card_image" alt="">
                                        <h3 class="profile_title mt-2"><?php echo htmlspecialchars($name); ?> <br> <span class="profile_role"><?php echo htmlspecialchars($job_title); ?></span></h3>
                                        <div class="row mt-3">
                                            <div class="col-sm-6">
                                                <button class="btn btn-green-new col-12">Edit Profile</button>
                                            </div>
                                            <div class="col-sm-6">
                                                <a href="logout.php" class="btn btn-white col-12">Log Out</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card cta_card mt-2">
                                    <div class="card-body">
                                        <h3 class="cta_title">Ran into a problem?</h3>
                                        <p class="cta_subtitle">We have your back</p>
                                        <a href="mailto:info@webp.com.au" class="btn btn-outline-dark col-12 mail-btn"><i class="fa-solid fa-angles-right"></i> info@webp.com.au</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script>
            function updateClock() {
                const now = new Date();

                // Format date
                const day = String(now.getDate()).padStart(2, '0');
                const month = now.toLocaleString('default', { month: 'short' });
                const year = now.getFullYear();

                // Format time
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                // Final string (e.g., 18 Sep 2025 | 14:35:08)
                const dateTimeString = `${day} ${month} ${year} | ${hours}:${minutes}:${seconds}`;

                document.getElementById('clock').textContent = dateTimeString;
            }

            // Update every second
            setInterval(updateClock, 1000);

            // Initial call
            updateClock();

        </script>
    </body>
</html>