<?php  
if(basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('Access denied..');

// Stats - for page request time
$g_request_time = microtime(true);

// Set Dir constants
$d = DIRECTORY_SEPARATOR; // "\\" on Win, '/' Linux,...
$rootpath = dirname(__FILE__).$d;

// Include default configuration settings
require_once( $rootpath.'config'.$d.'configdefaults.inc.php' );

// config.inc may not be present if this is a new install
if ( file_exists( $rootpath.'config'.$d.'config.inc.php' ) ) {
    require_once( $rootpath.'config'.$d.'config.inc.php' );
    $config_inc_found = true;
} else {
    $config_inc_found = false;
}    

// Load constants
require_once( $rootpath.'config'.$d.'constants.inc.php' );

// Set Dir constants
define('ROOT_PATH','../'); //Path to the root dir.
// define ( 'BASE_PATH' , realpath( dirname(__FILE__) ) );
define('ROOT_DIR',str_replace('\\\\', '/', realpath(dirname(__FILE__))).'/'); #Get real path for root dir ---linux and windows
define('INCLUDE_DIR',ROOT_DIR.'../include/'); //Change this if include is moved outside the web path.
define('PEAR_DIR',INCLUDE_DIR.'pear/');
define('SETUP_DIR',INCLUDE_DIR.'setup/');
define('UPGRADE_DIR', INCLUDE_DIR.'upgrader/');
define('SQL_DIR', UPGRADE_DIR.'sql/');// realpath(dirname(__FILE__))).'/'); #Get real path for root dir ---linux and windows

// Set include paths
$class_path  = $rootpath.'classes'.$d;
$include_path  = $rootpath.'..'.$d.'include'.$d;
$include_path2 = $rootpath.'include'.$d;
$include_pear  = $rootpath.'..'.$d.'include'.$d.'/pear'.$d;

$path = array($class_path,
              $include_path,
              $include_path2,
              $include_pear,
              get_include_path()
             );
set_include_path( implode( PATH_SEPARATOR, $path ) ); // ';' on Win, ':' on Linux,...

// Unset global variables that are no longer needed.
unset( $d, $rootpath, $class_path, $include_path, $include_path2, $include_pear, $path );
   
//// OLD main.inc.php
//commented
/*
    #Disable Globals if enabled....before loading config info
    if(ini_get('register_globals')) {
       ini_set('register_globals',0);
       foreach($_REQUEST as $key=>$val)
           if(isset($$key))
               unset($$key);
    }

    #Disable url fopen && url include
    ini_set('allow_url_fopen', 0);
    ini_set('allow_url_include', 0);

    #Disable session ids on url.
    ini_set('session.use_trans_sid', 0);
    #No cache
    session_cache_limiter('nocache');
    #Cookies
    //ini_set('session.cookie_path','/osticket/');
*/

    #Error reporting...Good idea to ENABLE error reporting to a file. i.e display_errors should be set to false
    $error_reporting = E_ALL & ~E_NOTICE;
    if (defined('E_STRICT')) # 5.4.0
        $error_reporting &= ~E_STRICT;
    if (defined('E_DEPRECATED')) # 5.3.0
        $error_reporting &= ~(E_DEPRECATED | E_USER_DEPRECATED);
    error_reporting($error_reporting); //Respect whatever is set in php.ini (sysadmin knows better??)
    #Don't display errors
//    ini_set('display_errors',1);
//    ini_set('display_startup_errors',1);

    # Verify if is installed, if not redirect to install.php
    if ( false === $config_inc_found ) {
            header('Location: '.ROOT_PATH.'setup/');
    }
    # Load internationalization functions (needed before database_api, in case database connection fails)
    require_once( 'language.inc.php' );
    if ( !isset( $g_skip_lang_load ) ) {
    //    lang_load( lang_get_default() );
        lang_load( 'english' );   
    }
   
    #include required files
    require('class.osticket.php');
    require('class.ostsession.php');
    #require(INCLUDE_DIR.'class.usersession.php');
    require(INCLUDE_DIR.'class.pagenate.php'); //Pagenate helper!
    require(INCLUDE_DIR.'class.log.php');
    require(INCLUDE_DIR.'class.mcrypt.php');
    require(INCLUDE_DIR.'class.misc.php');
    require(INCLUDE_DIR.'class.timezone.php');
    require(INCLUDE_DIR.'class.http.php');
    require(INCLUDE_DIR.'class.nav.php');
    require(INCLUDE_DIR.'class.format.php'); //format helpers
    require(INCLUDE_DIR.'class.validator.php'); //Class to help with basic form input validation...please help improve it.
    require(INCLUDE_DIR.'class.mailer.php');
    require(INCLUDE_DIR.'mysql.php');

    #CURRENT EXECUTING SCRIPT.
    define('THISPAGE', Misc::currentURL());
    define('THISURI', $_SERVER['REQUEST_URI']);

    # Start anonymous (if active)
    if ( !isset( $g_login_anonymous ) ) {
	$g_login_anonymous = true;
    }
  
    #Connect to the DB && get configuration from database
    $ferror=null;
    if (!db_connect(DBHOST,DBUSER,DBPASS) || !db_select_database(DBNAME)) {
        $ferror='Unable to connect to the database';
    } elseif(!($ost=osTicket::start(1)) || !($cfg = $ost->getConfig())) {
        $ferror='Unable to load config info from DB. Get tech support.';
    }
    if($ferror) { //Fatal error
        //try alerting admin using email in config file
        $msg=$ferror."\n\n".THISPAGE;
        Mailer::sendmail(ADMIN_EMAIL, 'osTicket Fatal Error', $msg, sprintf('"osTicket Alerts"<%s>', ADMIN_EMAIL));
        //Display generic error to the user
        die("<b>Fatal Error:</b> Contact system administrator. ". $msg);
        exit;
    }
    
    //Init
    $session = $ost->getSession();

    //System defaults we might want to make global//
    #pagenation default - user can overwrite it!
    define('DEFAULT_PAGE_LIMIT', $cfg->getPageSize()?$cfg->getPageSize():25);

    #Cleanup magic quotes crap.
/*    if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $_POST=Format::strip_slashes($_POST);
        $_GET=Format::strip_slashes($_GET);
        $_REQUEST=Format::strip_slashes($_REQUEST);
    }*/
//// END Old main.inc

if(!defined('INCLUDE_DIR')) die('Fatal error... invalid setting.');

/*Some more include defines specific to staff only */
define('STAFFINC_DIR',INCLUDE_DIR.'staff/');
define('SCP_DIR',str_replace('//','/',dirname(__FILE__).'/'));

/* Define tag that included files can check */
define('OSTSCPINC',TRUE);
define('OSTSTAFFINC',TRUE);

/* Tables used by staff only */
// define('KB_PREMADE_TABLE',TABLE_PREFIX.'kb_premade');

/* include what is needed on staff control panel */
require_once('user.class.php');
require_once('group.class.php');
require_once(INCLUDE_DIR.'class.nav.php');
require_once(INCLUDE_DIR.'class.csrf.php');

/* First order of the day is see if the user is logged in and with a valid session.
    * User must be valid staff beyond this point 
    * ONLY super admins can access the helpdesk on offline state.
*/


if(!function_exists('LoginPage')) { //Ajax interface can pre-declare the function to  trap expired sessions.
    function LoginPage($msg) {
        $_SESSION['_staff']['auth']['dest']=THISURI;
        $_SESSION['_staff']['auth']['msg']=$msg;
        require(SCP_DIR.'login.php');
        exit;
    }
}

$user = new User($_SESSION['_staff']['userID']); //Set staff object.
#$dologin=0;
#if(!$user || !is_object($user) || !$user->getId() || !$user->isValid()){

//1) is the user Logged in for real && is staff.
//if (user-)
if(!$user || !is_object($user) || !$user->getId() || !$user->isValid()){
    $msg=(!$user || !$user->isValid())?'Authentication Required':'Session timed out due to inactivity';
    LoginPage($msg);
    exit;
}
die("aaaa");

//2) if not super admin..check system status and group status
/*if(!$user->isAdmin()) {
    //Check for disabled staff or group!
    if(!$user->isactive() || !$user->isGroupActive()) {
        LoginPage('Access Denied. Contact Admin');
        exit;
    }

    //Staff are not allowed to login in offline mode!!
    if(!$ost->isSystemOnline() || $ost->isUpgradePending()) {
        staffLoginPage('System Offline');
        exit;
    }
}
 * */

//Keep the session activity alive
$user->refreshSession();

/******* CSRF Protectin *************/
// Enforce CSRF protection for POSTS
if ($_POST  && !$ost->checkCSRFToken()) {
    Http::response(400, 'Valid CSRF Token Required');
    exit;
}

//Add token to the header - used on ajax calls [DO NOT CHANGE THE NAME] 
$ost->addExtraHeader('<meta name="csrf_token" content="'.$ost->getCSRFToken().'" />');

/******* SET STAFF DEFAULTS **********/
//Set staff's timezone offset.
$_SESSION['TZ_OFFSET']=$user->getTZoffset();
$_SESSION['TZ_DST']=$user->observeDaylight();

define('PAGE_LIMIT', $user->getPageLimit()?$user->getPageLimit():DEFAULT_PAGE_LIMIT);

//Clear some vars. we use in all pages.
$errors=array();
$msg=$warn=$sysnotice='';
$tabs=array();
$submenu=array();
$exempt = in_array(basename($_SERVER['SCRIPT_NAME']), array('logout.php', 'ajax.php', 'logs.php', 'upgrade.php'));

if($ost->isUpgradePending() && !$exempt) {
    $errors['err']=$sysnotice='System upgrade is pending <a href="upgrade.php">Upgrade Now</a>';
    require('upgrade.php');
    exit;
} elseif($cfg->isHelpDeskOffline()) {
    $sysnotice='<strong>System is set to offline mode</strong> - Client interface is disabled and ONLY admins can access staff control panel.';
    $sysnotice.=' <a href="settings.php">Enable</a>.';
}

$nav = new StaffNav($user);
//Check for forced password change.
if($user->forcePasswdChange() && !$exempt) {
    # XXX: Call staffLoginPage() for AJAX and API requests _not_ to honor
    #      the request
    $sysnotice = 'Password change required to continue';
    require('profile.php'); //profile.php must request this file as require_once to avoid problems.
    exit;
}
$ost->setWarning($sysnotice);
$ost->setPageTitle('osTicket :: Staff Control Panel');
?>
