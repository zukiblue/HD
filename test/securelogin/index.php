<?php     
require_once('auth.class.php');
$auth->requireAuthentication(10);
?>
Hi this is a protected content! You are logged-in. Happy viewing.
<br />
<br />
===============<br />
Navigation Menu<br />
===============<br />
<a href="index.php">Homepage</a><br />
<a href="about.php">About this page</a><br />
<?php if (isset($_SESSION['loggedin'])) { ?><a href="logout.php?signature=<?php echo $_SESSION['signature']; ?>">Logout</a><br /><?php } ?>