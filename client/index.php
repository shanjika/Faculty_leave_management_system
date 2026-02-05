<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Client Login | Leave Management System</title>

<!-- MAIN CLIENT CSS -->
<link rel="stylesheet" type="text/css" href="style.css">

<style>
/* ===== MODERN LOGIN UI ===== */

body {
    margin: 0;
    min-height: 100vh;
    font-family: "Segoe UI", Helvetica, Arial, sans-serif;
    background: linear-gradient(120deg, #3b4c98, #43438d);
}

/* CENTER CARD */
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 90vh;
}

/* LOGIN CARD */
.login-card {
    background: #ffffff;
    width: 420px;
    padding: 30px 35px;
    border-radius: 14px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.25);
    text-align: center;
}

/* HEADINGS */
.login-card h1 {
    margin: 0;
    color: #1e3a8a;
    font-size: 26px;
}

.login-card h2 {
    margin: 10px 0 25px;
    font-size: 18px;
    color: #475569;
}

/* FORM */
.login-card table {
    width: 100%;
}

.login-card td {
    padding: 8px 0;
    font-size: 14px;
    color: #334155;
}

/* INPUTS */
.textbox {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #cbd5f5;
    font-size: 14px;
}

.textbox:focus {
    outline: none;
    border-color: #8299cb;
}

/* BUTTON */
.login-button {
    background: #603ac0;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.login-button:hover {
    background: #1e40af;
}

/* LINKS */
.login-card a {
    color: #2563eb;
    text-decoration: none;
    font-size: 13px;
}

.login-card a:hover {
    text-decoration: underline;
}

/* ERROR */
.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}

/* INFO */
.info-text {
    margin-top: 15px;
    font-size: 12px;
    color: #728db2;
}
</style>

<script>
function valid() {
    var user = document.login.uname.value.trim();
    var pass = document.login.pass.value;

    if (user === "") {
        alert("Please Enter Username!");
        return false;
    }
    if (pass === "") {
        alert("Please Enter Password!");
        return false;
    }
    return true;
}
</script>
</head>

<body>

<?php include 'navi.php'; ?>

<div class="login-wrapper">
    <div class="login-card">

        <h1>Leave Management System</h1>
        <h2>Client Login</h2>

        <?php
        if (isset($_GET['err'])) {
            echo "<div class='error'><b>" . htmlspecialchars($_GET['err']) . "</b></div>";
        }
        ?>

        <form name="login" action="validate.php" method="post" onsubmit="return valid();">
            <table>
                <tr>
                    <td>Username</td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="uname" class="textbox" placeholder="Enter your username">
                    </td>
                </tr>

                <tr>
                    <td>Password</td>
                </tr>
                <tr>
                    <td>
                        <input type="password" name="pass" class="textbox" placeholder="Enter your password">
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 15px;">
                        <input type="submit" value="Login" class="login-button">
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 10px;">
                        <a href="passrecovery.php">Forgot your password?</a>
                    </td>
                </tr>
            </table>
        </form>

        <p class="info-text">
            Your password is your date of birth (dd-mm-yyyy)
        </p>

    </div>
</div>

</body>
</html>
