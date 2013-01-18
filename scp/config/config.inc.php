<?php

#Install flag
define('OSTINSTALLED',TRUE);
if(OSTINSTALLED!=TRUE){
    if(!file_exists(ROOT_PATH.'setup/install.php')) die('Error: Contact system admin.'); //Something is really wrong!
    //Invoke the installer.
    header('Location: '.ROOT_PATH.'setup/install.php');
    exit;
}

# Encrypt/Decrypt secret key - randomly generated during installation.
define('SECRET_SALT','8DF2B0BB3087B80');

#Default admin email. Used only on db connection issues and related alerts.
define('ADMIN_EMAIL','bbb@bbb.bbb');

#Mysql Login info
define('DBTYPE','mysql');
define('DBHOST','192.168.1.222'); 
define('DBNAME','hd');
define('DBUSER','bt');
define('DBPASS','bt');

#Table prefix
define('TABLE_PREFIX','hd_');

# Allow Anonymous Access
#$g_allowanonymouslogin = 0;
#$g_anonymousaccount = 'guest';


?>
