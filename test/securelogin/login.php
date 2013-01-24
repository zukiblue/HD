<?php
require_once('utils.inc.php');
require_once('auth.class.php');

if ((isset($_POST["pass"])) && (isset($_POST["user"])) ) {//&& ($_SESSION['LAST_ACTIVITY']==FALSE)) {
    //echo '$_POST["user"]'.$_POST["user"];
    //echo '$_POST["pass"]'.$_POST["pass"];
    if( $auth->login( $_POST["user"], $_POST["pass"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]) ){          
        redirectToURL("index.php");
    }  
}

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
.invalid {
border: 1px solid #000000;
background: #FF00FF;
}
</style>
</head>
<body >
<h2>Restricted Access</h2>
<br />
Hi! This private website is restricted to public access. Please enter username and password to proceed.
<br /><br />
<!-- START OF LOGIN FORM -->
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">

Username:  <input type="text" class="<?php if ($auth->validationresults==FALSE) echo "invalid"; ?>" id="user" name="user">
Password: <input name="pass" type="password" class="<?php if ($auth->validationresults==FALSE) echo "invalid"; ?>" id="pass" >
<br /><br />
<?php 
//echo 'auth-loginattempts_username:'.$auth->loginattempts_username;
//echo '  auth-loginattempts_total:'.$auth->loginattempts_ip;
    global $recaptcha_showafter;
global $recaptcha_publickey;
if (($auth->loginattempts_username > 2) || ($auth->loginattempts_ip > $recaptcha_showafter ) || ($auth->loginattempts_ip>2)) { ?>
Type the captcha below:
<br /> <br />
<?php
require_once('recaptchalib.php');
echo recaptcha_get_html($recaptcha_publickey);
?>
<br />
<?php } 

//echo '  $auth->validationresults:'.$auth->validationresults;
?>
<?php if ($auth->validationresults==FALSE) echo '<font color="red">Please enter valid username, password or captcha (if required).</font>'; ?><br />
<input type="submit" value="Login">                   
</form>
<!-- END OF LOGIN FORM -->
<br />
<br />
If you are not registered. You can register by clicking <a href="register.php">here</a>.
</body>
</html>
<?php
exit();
?>