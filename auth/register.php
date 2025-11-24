<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/register.css" />
</head>

<body>
    <main class="card">
        <section class="left">
            <div class="left-contant">
                <h1>Welcome!</h1>
                <p>Already have an account?</p>
                <button class="btn-register"><a href="login.php">Login</a></button>
            </div>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
            <div class="socials">
                <a class="button"><i class="fab fa-google"></i></a>
                <a class="button"><i class="fab fa-facebook-f"></i></a>
                <a class="button"><i class="fab fa-github"></i></a>
                <a class="button"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </section>

        <section class="right">
            <h2>Register</h2>
            <div class="role-section">
                <input type="radio" name="roleRadio" id="user" checked />
                <label for="user">User</label>
                <input type="radio" name="roleRadio" id="company" />
                <label for="company">Company</label>
            </div>

            <form class="form" id="reForm" action="process_register.php" method="POST">
                <input type="hidden" name="role" id="roleInput" value="user" />

                <label class="field">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" id="name" name="name" placeholder="Name" required />
                </label>

                <label class="field">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Email" required />
                </label>

                <label class="field company-field">
                    <i class="fa-regular fa-building"></i>
                    <input type="text" id="companyName" name="companyName" placeholder="Motto" disabled />
                </label>

                <label class="field">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="address" name="address" placeholder="Address" />
                </label>

                <label class="field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required />
                </label>
                <button type="submit" class="submit-btn">Register</button>
            </form>
            <?php
            session_start();
            if(isset($_SESSION['reg_success'])){
                echo '<p class="success-msg">'.$_SESSION['reg_success'].'</p>';
                unset($_SESSION['reg_success']);
            }
            if(isset($_SESSION['reg_error'])){
                echo '<p class="error-msg">'.$_SESSION['reg_error'].'</p>';
                unset($_SESSION['reg_error']);
            }
        ?>
        </section>
    </main>
    <script src="../assets/js/register.js"></script>
</body>

</html>