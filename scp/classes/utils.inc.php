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

function redirectToURL($url)
{
    header("Location: $url");
    exit;
}



/**
 * Return true if the parameter is an empty string or a string
 * containing only whitespace, false otherwise
 * @param string $p_var string to test
 * @return bool
 * @access public
 */
function is_blank( $p_var ) {
	$p_var = trim( $p_var );
	$str_len = strlen( $p_var );
	if( 0 == $str_len ) {
		return true;
	}
	return false;
}

/**
 * Get the named php ini variable but return it as a bool
 * @param string $p_name
 * @return bool
 * @access public
 */
function ini_get_bool( $p_name ) {
	$result = ini_get( $p_name );

	if( is_string( $result ) ) {
		switch( strtolower( $result ) ) {
			case 'off':
			case 'false':
			case 'no':
			case 'none':
			case '':
			case '0':
				return false;
				break;
			case 'on':
			case 'true':
			case 'yes':
			case '1':
				return true;
				break;
		}
	} else {
		return (bool) $result;
	}
}

/**
 * Get the named php.ini variable but return it as a number after converting
 * the giga (g/G), mega (m/M) and kilo (k/K) postfixes. These postfixes do not
 * adhere to IEEE 1541 in that k=1024, not k=1000. For more information see
 * http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 * @param string $p_name Name of the configuration option to read.
 * @return int Integer value of the configuration option.
 * @access public
 */
function ini_get_number( $p_name ) {
	$t_value = ini_get( $p_name );

	$t_result = 0;
	switch( substr( $t_value, -1 ) ) {
		case 'G':
		case 'g':
			$t_result = (int)$t_value * 1073741824;
			break;
		case 'M':
		case 'm':
			$t_result = (int)$t_value * 1048576;
			break;
		case 'K':
		case 'k':
			$t_result = (int)$t_value * 1024;
			break;
		default:
			$t_result = (int)$t_value;
			break;
	}
	return $t_result;
}

?>
