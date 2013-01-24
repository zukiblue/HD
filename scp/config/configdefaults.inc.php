<?php
/*
$g_show_timer = ON;
 
$g_top_include_page = '';
$g_logo_image = 'images/logo.jpg';
$g_logo_url = '';

  */

$g_allowanonymouslogin = 1;
$g_anonymousaccount = 'guest';


//Define your canonical domain including trailing slash!, example:
$domain= "http://127.0.0.1/hd/scp/";

//Define sending email notification to webmaster

$email='youremail@example.com';
$subject='New user registration notification';
$from='From: www.example.com';

//Define Recaptcha parameters
$recaptcha_privatekey ="6LeQ7tsSAAAAAJXqNJlBTkdr06_iZY1qiw9lOWAQ";
$recaptcha_publickey = "6LeQ7tsSAAAAAGwXqfjA6_G5CjqLl64LCkII0aDw";
$recaptcha_showafter = 2; // failed attempts
        
//Define length of salt,minimum=10, maximum=35
$length_salt=15;

//maximum number of failed attempts to ban brute force attackers
$maxfailedattempt=5;

//session timeout in seconds
$sessiontimeout=1800;

////////////////////////////////////
//END OF USER CONFIGURATION/////////
////////////////////////////////////

$loginpage_url= $domain.'login.php';
$forbidden_url= $domain.'403forbidden.php';
#die($loginpage_url);

?>