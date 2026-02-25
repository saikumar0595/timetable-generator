<?php
session_start();

if (isset($_POST['login'])) {
    $_SESSION['user'] = $_POST['username'];
    header("Location: index.php"); // Redirect to Dashboard
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- ===== CSS ===== -->
        <link rel="stylesheet" href="assets_login/css/styles.css">

        <!-- ===== BOX ICONS ===== -->
        <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>

        <title>Login | ChronoGen Timetable</title>  
    </head>
    <body>
        <div class="l-form">
            <div class="shape1"></div>
            <div class="shape2"></div>

            <div class="form">
                <img src="assets_login/img/authentication.svg" alt="" class="form__img">

                <form method="POST" action="" class="form__content">
                    <h1 class="form__title">Welcome</h1>

                    <div class="form__div form__div-one">
                        <div class="form__icon">
                            <i class='bx bx-user-circle'></i>
                        </div>

                        <div class="form__div-input">
                            <label for="username" class="form__label">Username</label>
                            <input type="text" name="username" id="username" class="form__input" value="Admin" required>
                        </div>
                    </div>

                    <div class="form__div">
                        <div class="form__icon">
                            <i class='bx bx-lock' ></i>
                        </div>

                        <div class="form__div-input">
                            <label for="password" class="form__label">Password</label>
                            <input type="password" name="password" id="password" class="form__input" required>
                        </div>
                    </div>
                    <a href="#" class="form__forgot">Forgot Password?</a>

                    <input type="submit" name="login" class="form__button" value="Login">

                    <div class="form__social">
                        <span class="form__social-text">Our login with</span>

                        <a href="#" class="form__social-icon"><i class='bx bxl-facebook' ></i></a>
                        <a href="#" class="form__social-icon"><i class='bx bxl-google' ></i></a>
                        <a href="#" class="form__social-icon"><i class='bx bxl-instagram' ></i></a>
                    </div>
                </form>
            </div>

        </div>
        
        <!-- ===== MAIN JS ===== -->
        <script src="assets_login/js/main.js"></script>
        <script>
            // Trigger focus for pre-filled Admin username
            window.addEventListener('load', () => {
                const usernameInput = document.getElementById('username');
                if (usernameInput.value !== "") {
                    usernameInput.parentNode.parentNode.classList.add("focus");
                }
            });
        </script>
    </body>
</html>
