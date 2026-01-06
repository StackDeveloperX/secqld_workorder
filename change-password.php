<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - SecQld</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link rel="stylesheet" href="assets/css/style.css">
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
                                <h2 class="title text-center">Change Password</h2>
                                <form id="change-password-form" class="mt-5 text-center">
                                    <div class="mb-3">
                                        <!-- <label for="password" class="form-label">Password</label> -->
                                        <input type="password" class="form-control" name="new-password" id="new-password" placeholder="Password">
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label for="password" class="form-label">Password</label> -->
                                        <input type="password" class="form-control" name="confirm-password" id="confirm-password" placeholder="Confirm Password">
                                    </div>
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-green">Change Password</button>
                                    </div>
                                </form>

                                
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function () {

                $('#change-password-form').on('submit', function (e) {
                    e.preventDefault();

                    let newPassword     = $('#new-password').val().trim();
                    let confirmPassword = $('#confirm-password').val().trim();
                    let submitBtn       = $('#change-password-form button[type="submit"]');

                    if (newPassword === '' || confirmPassword === '') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Both fields are required.'
                        });
                        return;
                    }

                    if (newPassword.length < 8) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Weak Password',
                            text: 'Password must be at least 8 characters long.'
                        });
                        return;
                    }

                    if (newPassword !== confirmPassword) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Mismatch',
                            text: 'Passwords do not match.'
                        });
                        return;
                    }

                    // 🔒 Disable button
                    submitBtn.prop('disabled', true).text('Updating...');

                    $.ajax({
                        url: 'includes/change-password.php',
                        type: 'POST',
                        data: { new_password: newPassword },
                        success: function (response) {

                            response = response.trim();

                            if (response === 'success') {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Password Updated',
                                    text: 'Your password has been changed successfully.'
                                }).then(() => {
                                    window.location.href = 'dashboard.php'; // or dashboard.php
                                });

                            } 
                            else if (response === 'weak') {

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Weak Password',
                                    text: 'Please choose a stronger password.'
                                });

                            } 
                            else if (response === 'unauthorized') {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Session Expired',
                                    text: 'Please login again.'
                                }).then(() => {
                                    window.location.href = 'login.php';
                                });

                            } 
                            else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Something went wrong. Please try again.'
                                });
                            }

                            submitBtn.prop('disabled', false).text('Change Password');
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: 'Unable to process request.'
                            });
                            submitBtn.prop('disabled', false).text('Change Password');
                        }
                    });
                });

            });
        </script>
    </body>
</html>