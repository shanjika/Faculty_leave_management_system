<?php
function mailer($recipient, $msg, $uname)
{
    /*
     Mail sending is disabled for localhost (XAMPP).
     This prevents Mail.php / SMTP / port 25 errors.
     Project logic will work correctly.
    */

    return true;
}
?>
