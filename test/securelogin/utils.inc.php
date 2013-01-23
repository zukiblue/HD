<?php

function HashPassword($input)
{
    //http://crackstation.net/hashing-security.html
    $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); 
    $hash = hash("sha256", $salt . $input); 
    $final = $salt . $hash; 
    return $final;
}

function sanitize($str){
    $retstr=trim($str);
    $retstr=htmlspecialchars($retstr);
    return $retstr;
}

function sanitizeForSQL($str)
{
    $retstr = sanitize($str);
    if( function_exists( "mysql_real_escape_string" ) )
    {
          $retstr = mysql_real_escape_string( $retstr );
    }
    else
    {
          $retstr = addslashes( $retstr );
    }
    return $retstr;
}

?>
