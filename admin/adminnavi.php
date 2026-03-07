<!DOCTYPE html>
<html>
<head>
<style>
.admin-nav {
    background: linear-gradient(90deg, #79b9f4, #6e9cce);
    padding: 0 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    font-family: "Segoe UI", Helvetica, Arial, sans-serif;
    display: flex; align-items: center;
    justify-content: space-between;
    min-height: 56px;
}
.nav-left { display:flex; align-items:center; gap:8px; }
.nav-right { display:flex; align-items:center; gap:10px; }
.admin-nav-btn {
    padding: 9px 22px; border-radius: 8px; border: none;
    font-size: 15px; font-weight: 600; cursor: pointer;
    background: #e3eafc; color: #1976d2;
    transition: background .18s, color .18s;
    display: flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.admin-nav-btn:hover { background: #1976d2; color: #fff; }
.admin-nav-btn.logout { background: #e74c3c; color: #fff; }
.admin-nav-btn.logout:hover { background: #b71c1c; }
.role-tag {
    background: rgba(255,255,255,0.25);
    color: #fff; font-weight: 700;
    padding: 4px 14px; border-radius: 99px;
    font-size: .8rem; letter-spacing: .5px;
}
</style>
</head>
<body>
<nav class="admin-nav">
    <div class="nav-left">
        <?php
        // DO NOT call session_start() here — already called in parent page
        $role = $_SESSION['role'] ?? 'HOD';
        echo "<span class='role-tag'>$role</span>";
        ?>
    </div>
    <div class="nav-right">
        <?php
        $current = basename($_SERVER['PHP_SELF']);
        if($current !== 'home.php' && $current !== 'index.php'){
            echo '<a class="admin-nav-btn" href="home.php">🏠 Home</a>';
        }
        ?>
        <button class="admin-nav-btn logout" onclick="window.location.href='logout.php'">
             Logout
        </button>
    </div>
</nav>
</body>
</html>