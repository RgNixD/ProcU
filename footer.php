        </div>
        <footer class="text-center mt-4">
            <small>
                <span class="text-uppercase"><?= $clientName ?> © <?= date("Y") ?></span>
            </small>
        </footer>
    </div>
</div>

<!-- jQuery -->
<script src="vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert -->
<script src="assets/js/sweetalert2.all.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/alerts.js"></script>
<script src="assets/js/script.js"></script>
<!-- Custom Theme Scripts -->
<script src="build/js/custom.min.js"></script>
<script>
    const togglePassword = document.querySelector("#togglePassword");
    const passwordField = document.querySelector("#password");

    if (togglePassword && passwordField) {
        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);

            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    }

    $(document).ready(function () {

        $('#loginForm').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'php/processes.php',
                data: {
                    action: 'login',
                    username: $('#username').val(),
                    password: $('#password').val()
                },
                success: function (response) {
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Invalid JSON response:', e);
                            return;
                        }
                    }

                    if (response.force_password_change) {
                        window.location.href = response.redirect;
                        return;
                    }

                    if (response.success) {
                        showSweetAlert("Sign in successful", response.message, "success");

                        setTimeout(function () {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        }, 2000);
                    } else {
                        showSweetAlert("Login Error", response.message, "error");
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error: ', xhr.responseText);
                }
            });
        });

        $('#forgotPasswordForm').submit(function (e) {
            e.preventDefault();

            var email = $('#email').val();

            $.ajax({
                type: 'POST',
                url: 'php/processes.php',
                data: {
                    action: 'searchEmail',
                    email: email
                }, 
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.exists) {
                        var id = response.id;
                        window.location.href = 'send-code.php?email=' + encodeURIComponent(email) + '&id=' + id;
                    } else {
                        showSweetAlert("Not Found", "Email does not exist", "error", response.redirect); //FORMAT: TITLE, TEXT, ICON, URL
                    }
                }, error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('#sendCodeForm').submit(function (e) {
            e.preventDefault();
            showSweetAlert("Please wait", "An email message will be sent to your email shortly.", "info"); //FORMAT: TITLE, TEXT, ICON
            var formData = new FormData($(this)[0]);
            formData.append('action', 'sendVerificationCode');
            $.ajax({
                type: 'POST',
                url: 'php/processes.php',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        showSweetAlert("Successfully sent", response.message, "success", response.redirect); //FORMAT: TITLE, TEXT, ICON, URL
                    } else {
                        showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
                    }
                }, error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('#verifyCodeForm').submit(function (e) {
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            formData.append('action', 'verifyCode');
            $.ajax({
                type: 'POST',
                url: 'php/processes.php',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
                    }
                }, error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('#changePasswordForm').submit(function (e) {
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            formData.append('action', 'changePassword');
            $.ajax({
                type: 'POST',
                url: 'php/processes.php',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        showSweetAlert("Success", response.message, "success", response.redirect); //FORMAT: TITLE, TEXT, ICON, URL
                    } else {
                        showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
                    }
                }, error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

    });
</script>

</body>

</html>