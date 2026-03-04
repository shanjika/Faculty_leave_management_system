
<!DOCTYPE html>
<html>
<head>
<style>
.admin-nav {
    background: linear-gradient(90deg, #79b9f4, #6e9cce);
    padding: 0 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    font-family: "Segoe UI", Helvetica, Arial, sans-serif;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 18px;
    min-height: 56px;
}
.admin-nav-btn {
    padding: 10px 28px;
    border-radius: 8px;
    border: none;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    background: #e3eafc;
    color: #1976d2;
    margin-left: 0;
    transition: background 0.18s, color 0.18s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.admin-nav-btn:hover {
    background: #1976d2;
    color: #fff;
}
.admin-nav-btn.logout {
    background: #e74c3c;
    color: #fff;
    margin-left: 8px;
}
.admin-nav-btn.logout:hover {
    background: #b71c1c;
    color: #fff;
}
</style>
</head>
<body>
<nav class="admin-nav">
    <?php
    $current = basename($_SERVER['PHP_SELF']);
    $back = 'home.php';
    if ($current !== 'home.php' && $current !== 'index.php') {
        echo '<button class="admin-nav-btn" onclick="window.location.href=\''.$back.'\'">← Back</button>';
    }
    ?>
    <button class="admin-nav-btn logout" onclick="window.location.href='logout.php'">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path fill="#fff" d="M16 13v-2H7V8l-5 4 5 4v-3h9z"/><path fill="#fff" d="M20 3h-8a2 2 0 0 0-2 2v4h2V5h8v14h-8v-4h-2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
        Logout
    </button>
</nav>
</body>
</html>