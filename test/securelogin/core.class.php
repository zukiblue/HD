<?php
require_once("utils.inc.php");

class Core {
    
    function Core() {
       
    }
    
    function authenticate(){
        //Pre-define validation
        $validationresults=TRUE;
        $registered=TRUE;
        $recaptchavalidation=TRUE;

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
            $loginattempts_total = $row['failedattempts'];

            If ($loginattempts_total>$maxfailedattempt) {
                //too many failed attempts allowed, redirect and give 403 forbidden.
                header(sprintf("Location: %s", $forbidden_url));	
                exit;
            }
        }

        //Check if a user has logged-in
        if (!isset($_SESSION['logged_in'])) {
            $_SESSION['logged_in'] = FALSE;
        }
        
        

    }
}

?>
