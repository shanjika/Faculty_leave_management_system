<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leave Management System</title>

<!-- Existing CSS -->
<link rel="stylesheet" href="style.css">

<style>
/* ===== MODERN LANDING PAGE UI ===== */

body {
    margin: 0;
    font-family: "Segoe UI", Helvetica, Arial, sans-serif;
    background: linear-gradient(135deg, #3b4c98, #43438d);
    color: #ffffff;
}

/* HERO SECTION */
.hero {
    min-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px;
}

/* HEADINGS */
.hero h1 {
    font-size: 42px;
    margin-bottom: 10px;
}

.hero h2 {
    font-size: 22px;
    font-weight: 400;
    margin-bottom: 25px;
    color: #e0e7ff;
}

/* TEXT */
.hero p {
    font-size: 16px;
    max-width: 600px;
    line-height: 1.6;
    color: #dbeafe;
}

/* BUTTONS */
.hero .btn {
    margin-top: 25px;
    padding: 12px 26px;
    background-color: #ffffff;
    color: #1e3a8a;
    border-radius: 30px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.hero .btn:hover {
    background-color: #dbeafe;
}

/* FOOTER */
footer {
    background-color: rgba(0,0,0,0.25);
    padding: 12px;
    text-align: center;
    font-size: 14px;
    position: fixed;
    bottom: 0;
    width: 100%;
}
</style>
</head>

<body>

<?php include 'navi.php'; ?>

<div class="hero">
    <h1>Faculty Leave Management System</h1>
    <h2>Smart • Simple • Efficient</h2>

    <p>
        A simple and efficient Leave Management System developed using PHP.
        It helps organizations manage employee leave requests digitally
        with ease and accuracy.
    </p>
   <div style="margin-top:30px; display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
    
    <a href="client/index.php"
       style="padding:12px 28px;
              border-radius:30px;
              background-color:#ffffff;
              color:#1e3a8a;
              text-decoration:none;
              font-size:15px;
              font-weight:600;
              min-width:160px;
              text-align:center;">
        Client Login
    </a>

    <a href="admin/index.php"
       style="padding:12px 28px;
              border-radius:30px;
              background-color:transparent;
              color:#ffffff;
              border:2px solid #ffffff;
              text-decoration:none;
              font-size:15px;
              font-weight:600;
              min-width:160px;
              text-align:center;">
        Admin Login
    </a>

</div>

</div>

<footer>
    &copy; 2025– <?php echo date('Y'); ?> Shanjika
</footer>

</body>
</html>
