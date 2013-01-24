<?php
session_start();
require('config.inc.php');
require_once('auth.class.php');

$signature= sanitize($_GET['signature']);
if ($signature === $_SESSION['signature']) {
//authenticated user request
    $auth->logout();
echo 'You have successfully logout from the private website! Thank you.<br /><br />';
?>
===============<br />
Navigation Menu<br />
===============<br />
<a href="index.php">Homepage</a><br />
<a href="about.php">About this page</a><br />
<?php
} else {
    $auth->block();
    exit;  
}
?>