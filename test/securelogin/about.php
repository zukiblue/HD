<?php
include('SMTPconfig.php');
include('SMTPClass.php');
require_once('auth.class.php');
$auth->requireAuthentication(0);
?>
This is a private website that utilizes some form of secure login for selected visitors.
<br />
<br />
===============<br />
Navigation Menu<br />
===============<br />
<a href="index.php">Homepage</a><br />
<a href="about.php">About this page</a><br />
<?php if (isset($_SESSION['logged_in'])) { ?>
<a href="logout.php?signature=<?php echo $_SESSION['signature']; ?>">Logout</a><br /><?php } 


$to = 'zukibluexxx@yahoo.com';
$from = "admin@adultonlinepages.com";
$subject = "Your reset password request at "."sitename";
$link = "www.www.com".
                '/resetpwd.php?email='.
                urlencode($email).'&code='.
                urlencode('aaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
$body = "Hello ".'zk'."\r\n\r\n".
        "There was a request to reset your password at ".'$this->sitename'."\r\n".
        "Please click the link below to complete the request: \r\n".$link."\r\n".
        "Regards,\r\n".
        "Webmaster\r\n".
        '$this->sitename';
$SMTPMail = new SMTPClient ($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $subject, $body);
$SMTPChat = $SMTPMail->SendMail();
//die($SMTPChat);
?>