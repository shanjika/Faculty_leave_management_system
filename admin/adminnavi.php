<!DOCTYPE html>
<html>
<head>
<style>
body {
    font-family: "Segoe UI Light","Segoe WPC","Segoe UI", Helvetica, Arial, "Arial Unicode MS", Sans-Serif;
    font-size: 17px;
    font-style: normal;
    font-variant: normal;
    font-weight: 500;
    color: #000000;
    margin: 0;
    padding: 0;
}

button {
    padding: 10px 22px;
    border-radius: 6px;
    border: none;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.back-button {
    background: #e0e0e0;
    color: #333;
}

.logout-button {
    background: #e74c3c;
    color: #fff;
}

.container {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin: 18px 0 30px 0;
}
</style>
</head>
<body>

  <?php
  // Minimal admin nav: just Back and Logout buttons
  $current = basename($_SERVER['PHP_SELF']);
  $back = 'home.php';
  if ($current === 'home.php' || $current === 'index.php') {
      $back = false;
  }
  if ($back) {
      echo '<button class="back-button" onclick="window.location.href=\''.$back.'\'">← Back</button>';
  }
  ?>
<?php
// Minimal admin nav: just Back and Logout buttons
$current = basename($_SERVER['PHP_SELF']);
$back = 'home.php';
if ($current === 'home.php' || $current === 'index.php') {
    $back = false;
}
if ($back) {
    echo '<button class="back-button" onclick="window.location.href=\''.$back.'\'">← Back</button>';
}
?>
<button class="logout-button" style="position:fixed;top:40px;right:80px;z-index:1000;" onclick="window.location.href='logout.php'">Logout</button>

</body>
</html>