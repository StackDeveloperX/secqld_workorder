<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - SecQld</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        
        <!-- Login Form -->
        <section class="login">
            <div class="container">
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8 login-section mt-5 mb-5 shadow">
                        <div class="row">
                            <div class="col-sm-6 left-section">

                            </div>
                            <div class="col-sm-6 right-section">
                                <h2 class="title text-center">Login</h2>
                                <form id="admin-login-form" class="mt-5 text-center">
                                    <div class="mb-3">
                                        <!-- <label for="email" class="form-label">Email address</label> -->
                                        <input type="text" class="form-control" name="admin_email" id="admin_email" placeholder="Email Address">
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label for="password" class="form-label">Password</label> -->
                                        <input type="password" class="form-control" name="admin_password" id="admin_password" placeholder="Password">
                                    </div>
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-green">Sign In</button>
                                    </div>
                                </form>

                                <div class="sso text-center">
                                    <p>Can't remember your password? <u>Recover it.</u></p>

                                    <p class="mt-4">Or Sign In Using</p>
                                    <a href="includes/google-login.php"><img src="assets/images/google.png" alt=""></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-2"></div>
                </div>
            </div>
        </section>

        <!-- Toast Container -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
            <div id="loginToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastMessage">
                        Error message here.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function () {
                const toastEl = document.getElementById('loginToast');
                const toast = new bootstrap.Toast(toastEl);

                function showToast(message, type = 'danger') {
                    $('#toastMessage').text(message);
                    $('#loginToast')
                        .removeClass('bg-success bg-danger')
                        .addClass(type === 'success' ? 'bg-success' : 'bg-danger');
                    toast.show();
                }

                $('#admin-login-form').on('submit', function (e) {
                    e.preventDefault();

                    let email = $('#admin_email').val().trim();
                    let password = $('#admin_password').val().trim();

                    if (email === '' || password === '') {
                        showToast('Both fields are required.');
                        return;
                    }

                    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
                    if (!emailPattern.test(email)) {
                        showToast('Please enter a valid email address.');
                        return;
                    }

                    $.ajax({
                        url: 'includes/login.php',
                        type: 'POST',
                        data: { email: email, password: password },
                        success: function (response) {
                            if (response === 'success') {
                                showToast('Login successful!', 'success');
                                setTimeout(() => {
                                    window.location.href = 'dashboard.php'; // Redirect
                                }, 1500);
                            } else if (response === 'invalid') {
                                showToast('Invalid email or password.');
                            } else {
                                showToast('Unexpected error. Try again.');
                            }
                        },
                        error: function () {
                            showToast('Server error occurred.');
                        }
                    });
                });
            });
        </script>
    </body>
</html>