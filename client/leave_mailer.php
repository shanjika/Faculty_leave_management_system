<?php
function mailer($recipient, $msg, $uname)
{
    /*
     Localhost (XAMPP) does not have an SMTP server.
     So actual email sending is disabled.
     This function simply returns true so that
     the Leave Management System works without errors.
    */

    return true;
}
?>


