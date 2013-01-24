<?php
require_once("utils.inc.php");

if(!isset($_SESSION))
   session_start();
        
if (!isset($_SESSION['loggedin'])) {
   $_SESSION['loggedin'] = FALSE;
}       
$auth = new Auth ();
$auth-> checkBasicAuthentication();

$_SESSION['logindestination']=$_SERVER['REQUEST_URI'];

class Auth {
    var $loginattempts_username;
    var $loginattempts_ip;
    var $maxfailedattempt;
    var $validationresults;
    var $userexist;
    var $recaptchavalidation;
    var $signature;    

    //var $maxfailedattempt;
    
    function getUserName() {
      return 'UserName';    
    }
    
    function Auth() {
        $this->loginattempts_username = 0;
        $this->loginattempts_ip = 0;
        $this->maxfailedattempt = 5;
        $this->validationresults = TRUE;
        $this->userexist = TRUE;
        $this->recaptchavalidation = TRUE;
        $this->signature = '';
    }
    
    function checkBasicAuthentication(){
        global $loginpage_url;
        global $forbidden_url;
        // primary checks
        // wait for call @ requireAuthentication($level)
        if ( ($_SESSION['loggedin'])==TRUE ) { //valid user has logged-in to the website
            //Check for unauthorized use of user sessions
            if ( $this->checkUnautorizedAccess() ) {
                //This is unauthorized access, Logout
                $this->logout();
                exit;    
            }        
            //Session Lifetime control for inactivity
            
            if ( $this->checkSessionTimeout() ) {            
                //redirect the user back to login page for re-authentication
                $this->logout();
                exit;   
            }             
            
        }

        if ( $this->checkAttack() ) {
            $this->block();
            exit;    
        }
    }
    
    function requireAuthentication($requiredlevel){               
        global $loginpage_url;
        global $forbidden_url;
        $this->checkBasicAuthentication();
        $userLevel = 1000;
        //echo (var_dump($_SESSION['loggedin']));

        if ( !($_SESSION['loggedin']) ) {
            //redirect the user back to login page for re-authentication
            $this->logout();
            exit;   
        }
        if ( $userLevel < $requiredlevel ) {
            //This is unauthorized access, Block it
            $this->block();
            exit;              
        }
    }
    
    function checkUnautorizedAccess(){
        global $length_salt;
        $iprecreate= $_SERVER['REMOTE_ADDR'];
        $useragentrecreate=$_SERVER["HTTP_USER_AGENT"];
        $signaturerecreate=$_SESSION['signature'];
        //Extract original salt from authorized signature
        $saltrecreate = substr($signaturerecreate, 0, $length_salt);
        //Extract original hash from authorized signature
        $originalhash = substr($signaturerecreate, $length_salt, 40);
        //Re-create the hash based on the user IP and user agent
        //then check if it is authorized or not
        $hashrecreate= sha1($saltrecreate.$iprecreate.$useragentrecreate);
//    echo ('......'.var_dump($_SESSION['signature']));
//    echo (var_dump($_SESSION['loggedin']));
//    echo ('$hash..:'.$saltrecreate.$iprecreate.$useragentrecreate.'<br />');
//    echo ('$hash..:'.$_SESSION['signature'].'<br />');
//    echo ('$hash..:'.$hashrecreate.' '.$originalhash.'<br />');
    
//    echo ('$hash..:'.$hashrecreate);
//        die('____');
                
        if (!($hashrecreate==$originalhash)) {
            //Signature submitted by the user does not matched with the
            //authorized signature
            //This is unauthorized access, Block it
            return true;
        }
        return false;
    }

    function checkSessionTimeout(){
        global $sessiontimeout;
        //Session Lifetime control for inactivity
        if ((isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessiontimeout)))  {
            $this->logout();
            return true;
        }
        return false;
    }

    function checkAttack(){
                   
        // Trapped brute force attackers and give them more hard work by 
        // providing a captcha-protected page
        $iptocheck= $_SERVER['REMOTE_ADDR'];
        $iptocheck= sanitizeForSQL($iptocheck);

        $qry = mysql_query("SELECT `loggedip` FROM `ipcheck` WHERE `loggedip`='$iptocheck'");
        if ($fetch = mysql_fetch_array( $qry )) {
            //Already has some IP address records in the database
            //Get the total failed login attempts associated with this IP address
            $qry = mysql_query("SELECT `failedattempts` FROM `ipcheck` WHERE `loggedip`='$iptocheck'");
            $row = mysql_fetch_array($qry);
            $this->loginattempts_ip = $row['failedattempts'];
        
            If ($this->loginattempts_ip > $this->maxfailedattempt) {
                //too many failed attempts allowed, redirect and give 403 forbidden.
                return true;
            }
        }
        return false;
    }
    
    function login($username,$password, $recaptchachallenge='', $recaptcharesponse='')
    {
        global $loginattempts_username;
        $iptocheck= $_SERVER['REMOTE_ADDR'];

        if(empty($username))
        {
            $this->HandleError("UserName is empty!");
            return false;
        }        
        if(empty($password))
        {
            $this->HandleError("Password is empty!");
            return false;
        }        
        //Sanitize information
        $user = sanitize($username);
        $pass = sanitize($password);
        
        if(!isset($_SESSION))
            session_start();
        
        
        //validate username
        if (!($fetch = mysql_fetch_array( mysql_query("SELECT `username` FROM `users` WHERE `username`='$user'")))) {
            //no records of username in database, user is not yet registered
            $this->userexist=FALSE;
        }
           
        //Possible brute force attacker is targeting randomly
        if ($this->userexist==FALSE) {
            if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `ipcheck` WHERE `loggedip`='$iptocheck'")))) {
                //no records, insert failed attempts

                $this->loginattempts_ip=1;
                $this->loginattempts_ip=intval($this->loginattempts_ip);
                mysql_query("INSERT INTO `ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$this->loginattempts_ip')");	
            } else {
                //has some records, increment attempts
                $this->loginattempts_ip= $this->loginattempts_ip + 1;
       //  $this->HandleError($this->loginattempts_ip);
                mysql_query("UPDATE `ipcheck` SET `failedattempts` = '$this->loginattempts_ip' WHERE `loggedip` = '$iptocheck'");
            }
        }
        if ($this->userexist==TRUE) {
            //Grab login attempts from MySQL database for a corresponding username
            $result = mysql_query("SELECT `loginattempt`,`password` FROM `users` WHERE `username`='$user'");
            $row = mysql_fetch_array($result);
            $this->loginattempts_username = $row['loginattempt'];

            //username is registered in database, now get the hashed password
            //Get correct hashed password based on given username stored in MySQL database
            $correctpassword = $row['password'];
            $salt = substr($correctpassword, 0, 64);
            $correcthash = substr($correctpassword, 64, 64);
            $userhash = hash("sha256", $salt . $pass);
        }
        
        global $recaptcha_showafter;            
        if(($this->loginattempts_username>$recaptcha_showafter) || ($this->userexist==FALSE) || ($this->loginattempts_ip>$recaptcha_showafter)) {
            //Require captcha and validate recaptcha
            require_once('recaptchalib.php');
            global $recaptcha_privatekey;
            $resp = recaptcha_check_answer ($recaptcha_privatekey, $_SERVER["REMOTE_ADDR"],$recaptchachallenge, $recaptcharesponse);
            if (!$resp->is_valid) {
                //captcha validation fails
                $this->recaptchavalidation=FALSE;
            } else {
                $this->recaptchavalidation=TRUE;	
            }
        }
        
        //Get correct hashed password based on given username stored in MySQL database
        if ((!($userhash == $correcthash)) || ($this->userexist==FALSE) || ($this->recaptchavalidation==FALSE)) {
            //user login validation fails
            $this->validationresults=FALSE;
            //log login failed attempts to database
            if ($this->userexist==TRUE) {
                $this->loginattempts_username= $this->loginattempts_username + 1;
                $this->loginattempts_username=intval($this->loginattempts_username);
                //update login attempt records
                mysql_query("UPDATE `users` SET `loginattempt` = '$this->loginattempts_username' WHERE `username` = '$user'");
                //Possible brute force attacker is targeting registered usernames
                //check if has some IP address records
                if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `ipcheck` WHERE `loggedip`='$iptocheck'")))) {
                    // insert failed attempts
                    $this->loginattempts_ip=1;
                    $this->loginattempts_ip=intval($this->loginattempts_ip);
                    mysql_query("INSERT INTO `ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$this->loginattempts_ip')");	
                } else {
                    // increment failed attempts
                    $this->loginattempts_ip=$this->loginattempts_ip + 1;
                    mysql_query("UPDATE `ipcheck` SET `failedattempts` = '$this->loginattempts_ip' WHERE `loggedip` = '$iptocheck'");
                }
            }
            return false;
       
        } else {

            //user successfully authenticates with the provided username and password

            //Reset login attempts for a specific username to 0 as well as the ip address

            $this->loginattempts_username=0;
            $this->loginattempts_ip=0;
            $this->loginattempts_username=intval($this->loginattempts_username);
            $this->loginattempts_ip=intval($this->loginattempts_ip);
            mysql_query("UPDATE `users` SET `loginattempt` = '$this->loginattempts_username' WHERE `username` = '$user'");
            mysql_query("UPDATE `ipcheck` SET `failedattempts` = '$this->loginattempts_ip' WHERE `loggedip` = '$iptocheck'");
            
            //Generate unique signature of the user based on IP address
            //and the browser then append it to session
            //This will be used to authenticate the user session 
            //To make sure it belongs to an authorized user and not to anyone else.
            //generate random salt
            function genRandomString() {
            //credits: http://bit.ly/a9rDYd
                $length = 50;
                $characters = "0123456789abcdef";      
                for ($p = 0; $p < $length ; $p++) {
                    $string .= $characters[mt_rand(0, strlen($characters))];
                }

                return $string;
            }
            $random=genRandomString();
            global $length_salt;
            $salt_ip= substr($random, 0, $length_salt);

            //hash the ip address, user-agent and the salt
            $useragent=$_SERVER["HTTP_USER_AGENT"];
            $hash_user= sha1($salt_ip.$iptocheck.$useragent);
    //echo ('$hash.save.:'.$salt_ip.$iptocheck.$useragent);
     //$hash.save.:127.0.0.1Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.01111
     //$hash..    :127.0.0.1Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.01111
            //concatenate the salt and the hash to form a signature
            $this->signature= $salt_ip.$hash_user;

            //Regenerate session id prior to setting any session variable
            //to mitigate session fixation attacks
            session_regenerate_id();

            //Finally store user unique signature in the session
            //and set loggedin to TRUE as well as start activity time            
            $_SESSION['signature'] = $this->signature;
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['LAST_ACTIVITY'] = time(); 
            return true;
        }
/*        
        if(!$this->CheckLoginInDB($username,$password))
        {
            return false;
        }
        
        $_SESSION[$this->GetLoginSessionVar()] = $username;
        
        return true;
 */
    }
    
    function logout(){
        global $loginpage_url;
        $_SESSION['logged_in'] = False;
        session_destroy();   
        session_unset();  
        //redirect the user back to login page for re-authentication
        //echo 'logout @'.basename($_SERVER['SCRIPT_NAME']. ' goto: '.$loginpage_url);
        header(sprintf("Location: %s", $loginpage_url));
        exit;
    }                   
    function block(){
        global $forbidden_url;
        $_SESSION['logged_in'] = False;
        session_destroy();   
        session_unset();  
        //This is unauthorized access, Logout & Block it
        header(sprintf("Location: %s", $forbidden_url));	
        //echo 'block';
        exit;
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
        echo $this->error_message;
        die;
    }
    
    function HandleDBError($err)
    {
        $this->HandleError($err."\r\n mysqlerror:".mysql_error());
    }

}

?>
