<?php
require_once("utils.inc.php");

class User {
    var $loginattempts_username='';
    var $loginattempts_total=0;
    
    //-----Initialization -------
    function User() {
        $this->loginattempts_username = '';
        $this->loginattempts_total = 0;
    }
    
    function Login()
    {
        if(empty($_POST['username']))
        {
            $this->HandleError("UserName is empty!");
            return false;
        }
        
        if(empty($_POST['password']))
        {
            $this->HandleError("Password is empty!");
            return false;
        }
        
        $username = sanitize($_POST['username']);
        $password = sanitize($_POST['password']);
        
        if(!isset($_SESSION))
            session_start();
        
        if(!$this->CheckLoginInDB($username,$password))
        {
            return false;
        }
        
        $_SESSION[$this->GetLoginSessionVar()] = $username;
        
        return true;
    }
        
    function CheckLoginInDB($username,$password)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }          
        
        $username = sanitizeForSQL($username);

        //Get correct hashed password based on given username stored in MySQL database
        $result = mysql_query("SELECT `password` FROM `users` WHERE `username`='$username'");
        $row = mysql_fetch_array($result);
        $correctpassword = $row['password'];
        $salt = substr($correctpassword, 0, 64);
        $correcthash = substr($correctpassword, 64, 64);
        $userhash = hash("sha256", $salt . $password);
        
        
        if ((!($userhash == $correcthash)) )//|| ($registered==FALSE) || ($recaptchavalidation==FALSE)) 
        {
            //user login validation fails
            $validationresults=FALSE;

            //log login failed attempts to database
            if ($registered==TRUE) {
                $loginattempts_username= $loginattempts_username + 1;
                $loginattempts_username=intval($loginattempts_username);
                //update login attempt records
                mysql_query("UPDATE `users` SET `loginattempt` = '$loginattempts_username' WHERE `username` = '$user'");

 /*
        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Error logging in. The username or password does not match");
            return false;
        }
        $row = mysql_fetch_assoc($result);
        
        
        $_SESSION['name_of_user']  = $row['name'];
        $_SESSION['email_of_user'] = $row['email'];
*/        
            if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `ipcheck` WHERE `loggedip`='$iptocheck'")))) {
                //no records
                //insert failed attempts
                $loginattempts_total=1;
                $loginattempts_total=intval($loginattempts_total);
                mysql_query("INSERT INTO `ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$loginattempts_total')");	
            } else {	
                //has some records, increment attempts
                $loginattempts_total= $loginattempts_total + 1;
                mysql_query("UPDATE `ipcheck` SET `failedattempts` = '$loginattempts_total' WHERE `loggedip` = '$iptocheck'");
            }
        }
        //Possible brute force attacker is targeting randomly

if ($registered==FALSE) {
if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `ipcheck` WHERE `loggedip`='$iptocheck'")))) {
	
//no records
//insert failed attempts

$loginattempts_total=1;
$loginattempts_total=intval($loginattempts_total);
mysql_query("INSERT INTO `ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$loginattempts_total')");	
} else {
	
//has some records, increment attempts

$loginattempts_total= $loginattempts_total + 1;
mysql_query("UPDATE `ipcheck` SET `failedattempts` = '$loginattempts_total' WHERE `loggedip` = '$iptocheck'");
}
}

        return true;
    }
    
    function DBLogin()
    {
        $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

        if(!$this->connection)
        {   
            $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
            return false;
        }
        if(!mysql_select_db($this->database, $this->connection))
        {
            $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
            return false;
        }
        if(!mysql_query("SET NAMES 'UTF8'",$this->connection))
        {
            $this->HandleDBError('Error setting utf8 encoding');
            return false;
        }
        return true;
    }    

    //-------Private Helper functions-----------
    
    function HandleError($err)
    {
        $this->error_message .= $err."\r\n";
    }
    
    function HandleDBError($err)
    {
        $this->HandleError($err."\r\n mysqlerror:".mysql_error());
    }
}

?>
