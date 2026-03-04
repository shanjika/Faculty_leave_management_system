<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php');
    exit;
}
?>

<?php include 'clientnavi.php'; ?>
</div>


<style>
    .leave-type-title {
        text-align: center;
        font-size: 2.5rem;
        font-weight: bold;
        margin-top: 2rem;
        margin-bottom: 2rem;
    }
    .leave-type-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        height: 60vh;
    }
    .leave-type-box {
        width: 300px;
        height: 300px;
        font-size: 2rem;
        background: #f5f5f5;
        border: 2px solid #b0c4de;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
        font-weight: bold;
    }
    .leave-type-box:disabled,
    .leave-type-box.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .leave-type-box:hover:not(:disabled) {
        transform: scale(1.05);
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        background: #e3f2fd;
    }
</style>

<div class="leave-type-title">Select Leave Type</div>
<?php
if(isset($_GET['err'])){
        echo "<div class='error'>".$_GET['err']."</div>";
}
?>
<form action="leaverequest.php" method="post">
    <div class="leave-type-container">
        <?php
        $sql="SELECT * FROM employees WHERE UserName='".$_SESSION['user']."'";
        $res=$conn->query($sql);
        $row=$res->fetch_assoc();
        if($row['SickLeave']>0)
            echo "<button class='leave-type-box' name='type' value='Sick Leave'>🤒<br>Sick Leave</button>";
        else
            echo "<button class='leave-type-box disabled' disabled>🤒<br>Sick Leave</button>";
        if($row['EarnLeave']>0)
            echo "<button class='leave-type-box' name='type' value='Earn Leave'>💼<br>Earn Leave</button>";
        else
            echo "<button class='leave-type-box disabled' disabled>💼<br>Earn Leave</button>";
        if($row['CasualLeave']>0)
            echo "<button class='leave-type-box' name='type' value='Casual Leave'>🌴<br>Casual Leave</button>";
        else
            echo "<button class='leave-type-box disabled' disabled>🌴<br>Casual Leave</button>";
        ?>
    </div>
</form>

</body>
</html>
