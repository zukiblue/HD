<?php
require_once('core.php');
if(!defined('INCLUDE_DIR')) die('Access Denied.');
global $user;
//require_once('user.class.php');
require_once(INCLUDE_DIR.'class.csrf.php');

$dest = $_SESSION['_staff']['auth']['dest'];
$msg = $_SESSION['_staff']['auth']['msg'];
$msg = $msg?$msg:'Authentication Required';
if($_POST) {
    //$_SESSION['_staff']=array(); #Uncomment to disable login strikes.
    if(($user=User::login($_POST['username'], $_POST['passwd'], $errors))){
            $dest=($dest && (!strstr($dest,'login.php') && !strstr($dest,'ajax.php')))?$dest:'index.php';
        @header("Location: $dest");
        require_once('index.php'); //Just incase header is messed up.
        exit;
    }
die('bbbb');
   
    $msg = $errors['err']?$errors['err']:'Invalid login';
}
define("OSTSCPINC",TRUE); //Make includes happy!
include_once('login.tpl.php');
?>
