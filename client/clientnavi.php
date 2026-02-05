<!DOCTYPE html>
<html>
<head>
    <style>
/* ===== CLIENT NAVBAR ENHANCED UI ===== */

.client-nav {
    background: linear-gradient(90deg, #79b9f4, #6e9cce);
    padding: 0 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    font-family: "Segoe UI", Helvetica, Arial, sans-serif;
}

.client-nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
}

.client-nav li {
    position: relative;
}

.client-nav a {
    display: block;
    padding: 14px 18px;
    color: #ffffff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
}

.client-nav a:hover {
    background-color: rgba(255,255,255,0.15);
    border-radius: 6px;
}

/* DROPDOWN */
.client-nav .dropdown-content {
    display: none;
    position: absolute;
    top: 48px;
    left: 0;
    background-color: #bad2e1;
    min-width: 220px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    overflow: hidden;
    z-index: 1000;
}

.client-nav .dropdown-content a {
    color: #1f2937;
    padding: 12px 16px;
    font-size: 15px;
}

.client-nav .dropdown-content a:hover {
    background-color: #f1f5f9;
}

/* SHOW DROPDOWN */
.client-nav li:hover .dropdown-content {
    display: block;
}
</style>

<nav class="client-nav">
    <ul>
        <li><a href="index.php">Client Home</a></li>
        <li><a href="request_leave.php">Request Leave</a></li>
        <li>
            <a href="#">Me</a>
            <div class="dropdown-content">
                <a href="my_leaves.php">My All Leave Requests</a>
                <a href="changepass.php">Change Password</a>
                <a href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</nav>
</html>