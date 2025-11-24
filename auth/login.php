<?php
session_start();
if(isset($_SESSION['role'])){
    if($_SESSION['role'] === 'admin'){
        header("Location: ../admin/admin_dashboard.php");
    } elseif($_SESSION['role'] === 'user'){
        header("Location: ../user/user_dashboard.php");
    } elseif($_SESSION['role'] === 'company'){
        header("Location: ../company/company_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/login.css" />
</head>

<body>
    <main class="card">
        <section class="left">
            <div class="greeting">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn-register" type="button">
                    <a href="register.php">Register</a>
                </button>
            </div>
            <a href="../Home.html" class="back-btn">
                <i class="fa-solid fa-house"></i>
            </a>
        </section>
        <section class="right">
            <h2>Login</h2>

            <form class="form" action="process_login.php" method="POST">
                <label class="field">
                    <input id="email" name="email" type="email" placeholder="email" />
                    <i class="fa-regular fa-user"></i>
                </label>

                <label class="field">
                    <input id="password" name="password" type="password" placeholder="Password" />
                    <i class="fa-solid fa-lock"></i>
                </label>

                <div class="forgot">Forgot Password?</div>

                <button class="btn-primary" type="submit">Login</button>
            </form>

            <div class="or">or login with social platforms</div>

            <div class="socials">
                <a class="button"><i class="fab fa-google"></i></a>
                <a class="button"><i class="fab fa-facebook-f"></i></a>
                <a class="button"><i class="fab fa-github"></i></a>
                <a class="button"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </section>

    </main>
</body>

</html>