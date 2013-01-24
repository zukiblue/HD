<?php
//require user configuration and database connection parameters

///////////////////////////////////////
//START OF USER CONFIGURATION/////////
/////////////////////////////////////

//Define MySQL database parameters

$username = "bt";
$password = "bt";
$hostname = "192.168.1.222";
$database = "hd";

//Define your canonical domain including trailing slash!, example:
$domain= "http://127.0.0.1/hd/test/";

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

//DO NOT EDIT ANYTHING BELOW!

$dbhandle = mysql_connect($hostname, $username, $password)
 or die("Unable to connect to MySQL");
$selected = mysql_select_db($database,$dbhandle)
or die("Could not select $database");
$loginpage_url= $domain.'securelogin/login.php';
$forbidden_url= $domain.'securelogin/403forbidden.php';
#die($loginpage_url);
?>