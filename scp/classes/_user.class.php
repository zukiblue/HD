<?php
include_once(INCLUDE_DIR.'class.ticket.php');
include_once(INCLUDE_DIR.'class.dept.php');
include_once(INCLUDE_DIR.'class.team.php');
include_once('group.class.php');
include_once(INCLUDE_DIR.'class.passwd.php');

$g_script_login_cookie = null;
$g_cache_anonymous_user_cookie_string = null;
$g_cache_cookie_valid = null;
#$g_cache_current_user_id = null;

/**
 * Return true if there is a currently logged in and authenticated user, false otherwise
 *
 * @param boolean auto-login anonymous user
 * @return bool
 * @access public
 */
function auth_is_user_authenticated() {
	global $g_cache_cookie_valid, $g_login_anonymous;
	if( $g_cache_cookie_valid == true ) {
		return $g_cache_cookie_valid;
	}
//	$g_cache_cookie_valid = auth_is_cookie_valid( auth_get_current_user_cookie( $g_login_anonymous ) );
	$g_cache_cookie_valid = auth_get_current_user_cookie( $g_login_anonymous );
	return $g_cache_cookie_valid;
}

/**
 * Return the current user login cookie string,
 * note that the cookie cached by a script login superceeds the cookie provided by
 *  the browser. This shouldn't normally matter, except that the password verification uses
 *  this routine to bypass the normal authentication, and can get confused when a normal user
 *  logs in, then runs the verify script. the act of fetching config variables may get the wrong
 *  userid.
 * if no user is logged in and anonymous login is enabled, returns cookie for anonymous user
 * otherwise returns '' (an empty string)
 *
 * @param boolean auto-login anonymous user
 * @return string current user login cookie string
 * @access public
 */
function auth_get_current_user_cookie( $p_login_anonymous=true ) {
	global $g_script_login_cookie, $g_cache_anonymous_user_cookie_string;

	# if logging in via a script, return that cookie
	if( $g_script_login_cookie !== null ) {
		return $g_script_login_cookie;
	}

	# fetch user cookie
	//$t_cookie_name = config_get( 'string_cookie' );
	//$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	# if cookie not found, and anonymous login enabled, use cookie of anonymous account.
	if( is_blank( $t_cookie ) ) {
		if( $p_login_anonymous && ON == config_get( 'allow_anonymous_login' ) ) {
			if( $g_cache_anonymous_user_cookie_string === null ) {
				if( function_exists( 'db_is_connected' ) && db_is_connected() ) {

					# get anonymous information if database is available
					$query = 'SELECT id, cookie_string FROM ' . db_get_table( 'mantis_user_table' ) . ' WHERE username = ' . db_param();
					$result = db_query_bound( $query, Array( config_get( 'anonymous_account' ) ) );

					if( 1 == db_num_rows( $result ) ) {
						$row = db_fetch_array( $result );
						$t_cookie = $row['cookie_string'];

						$g_cache_anonymous_user_cookie_string = $t_cookie;
						$g_cache_current_user_id = $row['id'];
					}
				}
			} else {
				$t_cookie = $g_cache_anonymous_user_cookie_string;
			}
		}
	}

	return $t_cookie;
}

function get_current_user_cookie( $p_login_anonymous=true ) {
	global $g_script_login_cookie, $g_cache_anonymous_user_cookie_string;

	# if logging in via a script, return that cookie
	if( $g_script_login_cookie !== null ) {
		return $g_script_login_cookie;
	}

	# fetch user cookie
	#$t_cookie_name = config_get( 'string_cookie' );
	#$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

	# if cookie not found, and anonymous login enabled, use cookie of anonymous account.
	if( is_blank( $t_cookie ) ) {
		if( $p_login_anonymous && ON == getvar( 'allowanonymouslogin' ) ) {
			if( $g_cache_anonymous_user_cookie_string === null ) {
				if( function_exists( 'db_is_connected' ) && db_is_connected() ) {

					# get anonymous information if database is available
					$query = 'SELECT id, cookie_string FROM ' . db_get_table( 'mantis_user_table' ) . ' WHERE username = ' . db_param();
					$result = db_query_bound( $query, Array( getvar( 'anonymousaccount' ) ) );

					if( 1 == db_num_rows( $result ) ) {
						$row = db_fetch_array( $result );
						$t_cookie = $row['cookie_string'];

						$g_cache_anonymous_user_cookie_string = $t_cookie;
						$g_cache_current_user_id = $row['id'];
					}
				}
			} else {
				$t_cookie = $g_cache_anonymous_user_cookie_string;
			}
		}
	}

	return $t_cookie;
}

/**
 * Generate a string to use as the identifier for the login cookie
 * It is not guaranteed to be unique and should be checked
 * The string returned should be 64 characters in length
 * @return string 64 character cookie string
 * @access public
 */
function generate_cookie_string() {
	$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
	$t_val = md5( $t_val ) . md5( time() );
	return $t_val;
}

function isCookieValid( $cookie_string ) {
	global $g_cache_current_user_id;

	// fail if DB isn't accessible
	if( !db_is_connected() ) {
		return false;
	}

	// fail if cookie is blank
	if( '' === $cookie_string ) {
		return false;
	}

	// succeeed if user has already been authenticated
	/*if( null !== $g_cache_current_user_id ) {
		return true;
	}*/

	# look up cookie in the database to see if it is valid
	$query = 'SELECT *  FROM '.STAFF_TABLE.'
		  WHERE cookie_string=' . db_input($cookie_string);
	$result = db_query();

	# return true if a matching cookie was found
	if( 1 == db_num_rows( $result ) ) {
		//user_cache_database_result( db_fetch_array( $result ) );
		return true;
	} else {
		return false;
	}
}

class User {
    var $tm;
    var $ht;
    var $id;

    var $dept;
    var $departments;
    var $group;
    var $teams;
    var $timezone;
    var $stats;
    // from StaffSession  parent::Staff, now User
    var $session;

    function User($var) {
        $this->tm = microtime(true);
        $this->id = null;            
        // from StaffSession  parent::Staff, now User
        $this->session= new User_Session($var);
	
        global $g_login_anonymous, $g_anonymousaccount;
/*        if ($g_login_anonymous==1) {
        }
        else {
        $this->load($var)
  */
        return ( $this->load($var) );
    }

    function load($var='') {

        if(!$var && !($var=$this->getId()))
            return false;

        $sql='SELECT staff.*, staff.created as added, grp.* '
            .' FROM '.STAFF_TABLE.' staff '
            .' LEFT JOIN '.GROUP_TABLE.' grp ON(grp.group_id=staff.group_id) ';

        $sql.=sprintf(' WHERE %s=%s',is_numeric($var)?'staff_id':'username',db_input($var));

        if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;

        
        $this->ht=db_fetch_array($res);
        $this->id  = $this->ht['staff_id'];
        $this->teams = $this->ht['teams'] = array();
        $this->group = $this->dept = null;
        $this->departments = $this->stats = array();

        //WE have to patch info here to support upgrading from old versions.
  /*      if(($time=strtotime($this->ht['passwdreset']?$this->ht['passwdreset']:$this->ht['added'])))
            $this->ht['passwd_change'] = time()-$time; //XXX: check timezone issues.

        if($this->ht['timezone_id'])
            $this->ht['tz_offset'] = Timezone::getOffsetById($this->ht['timezone_id']);
        elseif($this->ht['timezone_offset'])
            $this->ht['tz_offset'] = $this->ht['timezone_offset'];
*/
        return ($this->id);
    }

    function reload() {
        return $this->load();
    }
    
    function isAuthenticated() {
        return false;
    }
    
    function asVar() {
        return $this->getName();
    }

    function getHastable() {
        return $this->ht;
    }

    function getInfo() {
        return $this->getHastable();
    }

    /*compares user password*/
    function check_passwd($password, $autoupdate=true) {

        /*bcrypt based password match*/
        if(Passwd::cmp($password, $this->getPasswd()))
            return true;

        //Fall back to MD5
        if(!$password || strcmp($this->getPasswd(), MD5($password)))
            return false;

        //Password is a MD5 hash: rehash it (if enabled) otherwise force passwd change.
        $sql='UPDATE '.STAFF_TABLE.' SET passwd='.db_input(Passwd::hash($password))
            .' WHERE staff_id='.db_input($this->getId());

        if(!$autoupdate || !db_query($sql))
            $this->forcePasswdRest();

        return true;
    }

    function cmp_passwd($password) {
        return $this->check_passwd($password, false);
    }

    function forcePasswdRest() {
        return db_query('UPDATE '.STAFF_TABLE.' SET change_passwd=1 WHERE staff_id='.db_input($this->getId()));
    }

    /* check if passwd reset is due. */
    function isPasswdResetDue() {
        global $cfg;
        return ($cfg && $cfg->getPasswdResetPeriod() 
                    && $this->ht['passwd_change']>($cfg->getPasswdResetPeriod()*30*24*60*60));
    }

    function isPasswdChangeDue() {
        return $this->isPasswdResetDue();
    }

    function getTZoffset() {
        return $this->ht['tz_offset'];
    }

    function observeDaylight() {
        return $this->ht['daylight_saving']?true:false;
    }

    function getRefreshRate() {
        return $this->ht['auto_refresh_rate'];
    }

    function getPageLimit() {
        return $this->ht['max_page_size'];
    }

    function getId() {
      //  echo 'ID: '.$this->id.'....';
        return $this->id;
    }

    function getEmail() {
        return $this->ht['email'];
    }

    function getUserName() {
        return $this->ht['username'];
    }

    function getPasswd() {
        return $this->ht['passwd'];
    }

    function getName() {
        return ucfirst($this->ht['firstname'].' '.$this->ht['lastname']);
    }
        
    function getFirstName() {
        return $this->ht['firstname'];
    }
        
    function getLastName() {
        return $this->ht['lastname'];
    }
    
    function getSignature() {
        return $this->ht['signature'];
    }

    function getDefaultSignatureType() {
        return $this->ht['default_signature_type'];
    }

    function getDefaultPaperSize() {
        return $this->ht['default_paper_size'];
    }

    function forcePasswdChange() {
        return ($this->ht['change_passwd']);
    }

    function getDepartments() {

        if($this->departments)
            return $this->departments;

        //Departments the staff is "allowed" to access...
        // based on the group they belong to + user's primary dept + user's managed depts.
        $sql='SELECT DISTINCT d.dept_id FROM '.STAFF_TABLE.' s '
            .' LEFT JOIN '.GROUP_DEPT_TABLE.' g ON(s.group_id=g.group_id) '
            .' INNER JOIN '.DEPT_TABLE.' d ON(d.dept_id=s.dept_id OR d.manager_id=s.staff_id OR d.dept_id=g.dept_id) '
            .' WHERE s.staff_id='.db_input($this->getId());

        $depts = array();
        if(($res=db_query($sql)) && db_num_rows($res)) {
            while(list($id)=db_fetch_row($res))
                $depts[] = $id;
        } else { //Neptune help us! (fallback)
            $depts = array_merge($this->getGroup()->getDepartments(), array($this->getDeptId()));
        }

        $this->departments = array_filter(array_unique($depts));


        return $this->departments;
    }

    function getDepts() {
        return $this->getDepartments();
    }

    function getManagedDepartments() {

        return ($depts=Dept::getDepartments(
                    array('manager' => $this->getId())
                    ))?array_keys($depts):array();
    }
     
    function getGroupId() {
        return $this->ht['group_id'];
    }

    function getGroup() {
     
        if(!$this->group && $this->getGroupId())
            $this->group = Group::lookup($this->getGroupId());

        return $this->group;
    }

    function getDeptId() {
        return $this->ht['dept_id'];
    }

    function getDept() {

        if(!$this->dept && $this->getDeptId())
            $this->dept= Dept::lookup($this->getDeptId());

        return $this->dept;
    }


    function isManager() {
        return (($dept=$this->getDept()) && $dept->getManagerId()==$this->getId());
    }

    function isStaff() {
        return TRUE;
    }

    function isGroupActive() {
        return ($this->ht['group_enabled']);
    }

    function isactive() {
        return ($this->ht['isactive']);
    }

    function isVisible() {
         return ($this->ht['isvisible']);
    }
        
    function onVacation() {
        return ($this->ht['onvacation']);
    }

    function isAvailable() {
        return ($this->isactive() && $this->isGroupActive() && !$this->onVacation());
    }

    function showAssignedOnly() {
        return ($this->ht['assigned_only']);
    }

    function isAccessLimited() {
        return $this->showAssignedOnly();
    }
  
    function isAdmin() {
        return ($this->ht['isadmin']);
    }

    function isTeamMember($teamId) {
        return ($teamId && in_array($teamId, $this->getTeams()));
    }

    function canAccessDept($deptId) {
        return ($deptId && in_array($deptId, $this->getDepts()) && !$this->isAccessLimited());
    }

    function canCreateTickets() {
        return ($this->ht['can_create_tickets']);
    }

    function canEditTickets() {
        return ($this->ht['can_edit_tickets']);
    }

    function canDeleteTickets() {
        return ($this->ht['can_delete_tickets']);
    }
   
    function canCloseTickets() {
        return ($this->ht['can_close_tickets']);
    }

    function canPostReply() {
        return ($this->ht['can_post_ticket_reply']);
    }

    function canViewStaffStats() {
        return ($this->ht['can_view_staff_stats']);
    }

    function canAssignTickets() {
        return ($this->ht['can_assign_tickets']);
    }

    function canTransferTickets() {
        return ($this->ht['can_transfer_tickets']);
    }

    function canBanEmails() {
        return ($this->ht['can_ban_emails']);
    }
  
    function canManageTickets() {
        return ($this->isAdmin() 
                 || $this->canDeleteTickets() 
                    || $this->canCloseTickets());
    }

    function canManagePremade() {
        return ($this->ht['can_manage_premade']);
    }

    function canManageCannedResponses() {
        return $this->canManagePremade();
    }

    function canManageFAQ() {
        return ($this->ht['can_manage_faq']);
    }

    function canManageFAQs() {
        return $this->canManageFAQ();
    }

    function showAssignedTickets() {
        return ($this->ht['show_assigned_tickets']
                && ($this->isAdmin() || $this->isManager()));
    }

    function getTeams() {
        
        if(!$this->teams) {
            $sql='SELECT team_id FROM '.TEAM_MEMBER_TABLE
                .' WHERE staff_id='.db_input($this->getId());
            if(($res=db_query($sql)) && db_num_rows($res))
                while(list($id)=db_fetch_row($res))
                    $this->teams[] = $id;
        }

        return $this->teams;
    }
    /* stats */

    function resetStats() {
        $this->stats = array();
    }

    /* returns staff's quick stats - used on nav menu...etc && warnings */
    function getTicketsStats() {

        if(!$this->stats['tickets'])
            $this->stats['tickets'] = Ticket::getStaffStats($this);

        return  $this->stats['tickets'];
    }

    function getNumAssignedTickets() {
        return ($stats=$this->getTicketsStats())?$stats['assigned']:0;
    }

    function getNumClosedTickets() {
        return ($stats=$this->getTicketsStats())?$stats['closed']:0;
    }

    //Staff profile update...unfortunately we have to separate it from admin update to avoid potential issues
    function updateProfile($vars, &$errors) {

        $vars['firstname']=Format::striptags($vars['firstname']);
        $vars['lastname']=Format::striptags($vars['lastname']);
        $vars['signature']=Format::striptags($vars['signature']);

        if($this->getId()!=$vars['id'])
            $errors['err']='Internal Error';

        if(!$vars['firstname'])
            $errors['firstname']='First name required';
        
        if(!$vars['lastname'])
            $errors['lastname']='Last name required';

        if(!$vars['email'] || !Validator::is_email($vars['email']))
            $errors['email']='Valid email required';
        elseif(Email::getIdByEmail($vars['email']))
            $errors['email']='Already in-use as system email';
        elseif(($uid=Staff::getIdByEmail($vars['email'])) && $uid!=$this->getId())
            $errors['email']='Email already in-use by another staff member';

        if($vars['phone'] && !Validator::is_phone($vars['phone']))
            $errors['phone']='Valid number required';

        if($vars['mobile'] && !Validator::is_phone($vars['mobile']))
            $errors['mobile']='Valid number required';

        if($vars['passwd1'] || $vars['passwd2'] || $vars['cpasswd']) {

            if(!$vars['passwd1'])
                $errors['passwd1']='New password required';
            elseif($vars['passwd1'] && strlen($vars['passwd1'])<6)
                $errors['passwd1']='Must be at least 6 characters';
            elseif($vars['passwd1'] && strcmp($vars['passwd1'], $vars['passwd2']))
                $errors['passwd2']='Password(s) do not match';
            
            if(!$vars['cpasswd'])
                $errors['cpasswd']='Current password required';
            elseif(!$this->cmp_passwd($vars['cpasswd']))
                $errors['cpasswd']='Invalid current password!';
            elseif(!strcasecmp($vars['passwd1'], $vars['cpasswd']))
                $errors['passwd1']='New password MUST be different from the current password!';
        }

        if(!$vars['timezone_id'])
            $errors['timezone_id']='Time zone required';

        if($vars['default_signature_type']=='mine' && !$vars['signature'])
            $errors['default_signature_type'] = "You don't have a signature";

        if($errors) return false;

        $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '
            .' ,firstname='.db_input($vars['firstname'])
            .' ,lastname='.db_input($vars['lastname'])
            .' ,email='.db_input($vars['email'])
            .' ,phone="'.db_input(Format::phone($vars['phone']),false).'"'
            .' ,phone_ext='.db_input($vars['phone_ext'])
            .' ,mobile="'.db_input(Format::phone($vars['mobile']),false).'"'
            .' ,signature='.db_input($vars['signature'])
            .' ,timezone_id='.db_input($vars['timezone_id'])
            .' ,daylight_saving='.db_input(isset($vars['daylight_saving'])?1:0)
            .' ,show_assigned_tickets='.db_input(isset($vars['show_assigned_tickets'])?1:0)
            .' ,max_page_size='.db_input($vars['max_page_size'])
            .' ,auto_refresh_rate='.db_input($vars['auto_refresh_rate'])
            .' ,default_signature_type='.db_input($vars['default_signature_type'])
            .' ,default_paper_size='.db_input($vars['default_paper_size']);


        if($vars['passwd1'])
            $sql.=' ,change_passwd=0, passwdreset=NOW(), passwd='.db_input(Passwd::hash($vars['passwd1']));

        $sql.=' WHERE staff_id='.db_input($this->getId());

        //echo $sql;

        return (db_query($sql));
    }


    function updateTeams($teams) {

        if($teams) {
            foreach($teams as $k=>$id) {
                $sql='INSERT IGNORE INTO '.TEAM_MEMBER_TABLE.' SET updated=NOW() '
                    .' ,staff_id='.db_input($this->getId()).', team_id='.db_input($id);
                db_query($sql);
            }
        }
        $sql='DELETE FROM '.TEAM_MEMBER_TABLE.' WHERE staff_id='.db_input($this->getId());
        if($teams)
            $sql.=' AND team_id NOT IN('.implode(',', $teams).')';
        
        db_query($sql);

        return true;
    }

    function update($vars, &$errors) {
        if(!$this->save($this->getId(), $vars, $errors))
            return false;

        $this->updateTeams($vars['teams']);
        $this->reload();
        
        return true;
    }

    function delete() {
        global $thisstaff;

        if(!$thisstaff || !($id=$this->getId()) || $id==$thisstaff->getId())
            return 0;

        $sql='DELETE FROM '.STAFF_TABLE.' WHERE staff_id='.db_input($id).' LIMIT 1';
        if(db_query($sql) && ($num=db_affected_rows())) {
            // DO SOME HOUSE CLEANING
            //Move remove any ticket assignments...TODO: send alert to Dept. manager?
            db_query('UPDATE '.TICKET_TABLE.' SET staff_id=0 WHERE status=\'open\' AND staff_id='.db_input($id));
            //Cleanup Team membership table.
            db_query('DELETE FROM '.TEAM_MEMBER_TABLE.' WHERE staff_id='.db_input($id));
        }

        return $num;
    }

    /**** Stati functions ********/
    function getStaffMembers($availableonly=false) {

        $sql='SELECT s.staff_id,CONCAT_WS(", ",s.lastname, s.firstname) as name '
            .' FROM '.STAFF_TABLE.' s ';

        if($availableonly) {
            $sql.=' INNER JOIN '.GROUP_TABLE.' g ON(g.group_id=s.group_id AND g.group_enabled=1) '
                 .' WHERE s.isactive=1 AND s.onvacation=0';
        }

        $sql.='  ORDER BY s.lastname, s.firstname';
        $users=array();
        if(($res=db_query($sql)) && db_num_rows($res)) {
            while(list($id, $name) = db_fetch_row($res))
                $users[$id] = $name;
        }

        return $users;
    }

    function getAvailableStaffMembers() {
        return self::getStaffMembers(true);
    }

    function getIdByUsername($username) {

        $sql='SELECT staff_id FROM '.STAFF_TABLE.' WHERE username='.db_input($username);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id) = db_fetch_row($res);

        return $id;
    }
    function getIdByEmail($email) {
                    
        $sql='SELECT staff_id FROM '.STAFF_TABLE.' WHERE email='.db_input($email);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id) = db_fetch_row($res);

        return $id;
    }

    function lookup($id) {
        return ($id && is_numeric($id) && ($user= new User($id)) && $user->getId()==$id)?$user:null;
    }

    function login($username, $passwd, &$errors, $strike=true) {
        global $ost, $cfg;


        if($_SESSION['_staff']['laststrike']) {
            if((time()-$_SESSION['_staff']['laststrike'])<$cfg->getStaffLoginTimeout()) {
                $errors['err']='Max. failed login attempts reached';
                $_SESSION['_staff']['laststrike'] = time(); //reset timer.
            } else { //Timeout is over.
                //Reset the counter for next round of attempts after the timeout.
                $_SESSION['_staff']['laststrike']=null;
                $_SESSION['_staff']['strikes']=0;
            }
        }

        if(!$username || !$passwd)
            $errors['err'] = 'Username and password required';

        if($errors) return false;
       
         die('now'.print_r($this->getId()));
        if(($session=new User_Session(trim($username))) && (!$this->getId()==0) /*&& $this->check_passwd($passwd)*/) {
            //update last login && password reset stuff.
            $sql='UPDATE '.STAFF_TABLE.' SET lastlogin=NOW() ';
            if($this->isPasswdResetDue() && !$this->isAdmin())
                $sql.=',change_passwd=1';
            $sql.=' WHERE staff_id='.db_input($this->getId());
            db_query($sql);
            //Now set session crap and lets roll baby!
            $_SESSION['_staff'] = array(); //clear.
            $_SESSION['_staff']['userID'] = $username;
            $this->refreshSession(); //set the hash.
            $_SESSION['TZ_OFFSET'] = $this->getTZoffset();
            $_SESSION['TZ_DST'] = $this->observeDaylight();

            //Log debug info.
            $ost->logDebug('User login', 
                    sprintf("%s logged in [%s]", $this->getUserName(), $_SERVER['REMOTE_ADDR'])); //Debug.

            //Regenerate session id.
            $sid=session_id(); //Current id
            session_regenerate_id(TRUE);
            //Destroy old session ID - needed for PHP version < 5.1.0 TODO: remove when we move to php 5.3 as min. requirement.
            if(($session=$ost->getSession()) && is_object($session) && $sid!=session_id())
                $session->destroy($sid);
        
            return $this;
        }
       
        //If we get to this point we know the login failed.
        $_SESSION['_staff']['strikes']+=1;
        if(!$errors && $_SESSION['_staff']['strikes']>$cfg->getStaffMaxLogins()) {
            $errors['err']='Forgot your login info? Contact Admin.';
            $_SESSION['_staff']['laststrike']=time();
            $alert='Excessive login attempts by a staff member?'."\n".
                   'Username: '.$username."\n".'IP: '.$_SERVER['REMOTE_ADDR']."\n".'TIME: '.date('M j, Y, g:i a T')."\n\n".
                   'Attempts #'.$_SESSION['_staff']['strikes']."\n".'Timeout: '.($cfg->getStaffLoginTimeout()/60)." minutes \n\n";
            $ost->logWarning('Excessive login attempts ('.$username.')', $alert, ($cfg->alertONLoginError()));
    
        } elseif($_SESSION['_staff']['strikes']%2==0) { //Log every other failed login attempt as a warning.
            $alert='Username: '.$username."\n".'IP: '.$_SERVER['REMOTE_ADDR'].
                   "\n".'TIME: '.date('M j, Y, g:i a T')."\n\n".'Attempts #'.$_SESSION['_staff']['strikes'];
            $ost->logWarning('Failed staff login attempt ('.$username.')', $alert, false);
        }

        return false;
    }

    function create($vars, &$errors) {
        if(($id=self::save(0, $vars, $errors)) && $vars['teams'] && ($staff=Staff::lookup($id)))
            $staff->updateTeams($vars['teams']);

        return $id;
    }

    function save($id, $vars, &$errors) {
            
        $vars['username']=Format::striptags($vars['username']);
        $vars['firstname']=Format::striptags($vars['firstname']);
        $vars['lastname']=Format::striptags($vars['lastname']);
        $vars['signature']=Format::striptags($vars['signature']);

        if($id && $id!=$vars['id'])
            $errors['err']='Internal Error';
            
        if(!$vars['firstname'])
            $errors['firstname']='First name required';
        if(!$vars['lastname'])
            $errors['lastname']='Last name required';
            
        if(!$vars['username'] || strlen($vars['username'])<2)
            $errors['username']='Username required';
        elseif(($uid=Staff::getIdByUsername($vars['username'])) && $uid!=$id)
            $errors['username']='Username already in-use';
        
        if(!$vars['email'] || !Validator::is_email($vars['email']))
            $errors['email']='Valid email required';
        elseif(Email::getIdByEmail($vars['email']))
            $errors['email']='Already in-use system email';
        elseif(($uid=Staff::getIdByEmail($vars['email'])) && $uid!=$id)
            $errors['email']='Email already in-use by another staff member';

        if($vars['phone'] && !Validator::is_phone($vars['phone']))
            $errors['phone']='Valid number required';
        
        if($vars['mobile'] && !Validator::is_phone($vars['mobile']))
            $errors['mobile']='Valid number required';

        if($vars['passwd1'] || $vars['passwd2'] || !$id) {
            if(!$vars['passwd1'] && !$id) {
                $errors['passwd1']='Temp. password required';
                $errors['temppasswd']='Required';
            } elseif($vars['passwd1'] && strlen($vars['passwd1'])<6) {
                $errors['passwd1']='Must be at least 6 characters';
            } elseif($vars['passwd1'] && strcmp($vars['passwd1'], $vars['passwd2'])) {
                $errors['passwd2']='Password(s) do not match';
            }
        }
        
        if(!$vars['dept_id'])
            $errors['dept_id']='Department required';
            
        if(!$vars['group_id'])
            $errors['group_id']='Group required';

        if(!$vars['timezone_id'])
            $errors['timezone_id']='Time zone required';

        if($errors) return false;

            
        $sql='SET updated=NOW() '
            .' ,isadmin='.db_input($vars['isadmin'])
            .' ,isactive='.db_input($vars['isactive'])
            .' ,isvisible='.db_input(isset($vars['isvisible'])?1:0)
            .' ,onvacation='.db_input(isset($vars['onvacation'])?1:0)
            .' ,assigned_only='.db_input(isset($vars['assigned_only'])?1:0)
            .' ,dept_id='.db_input($vars['dept_id'])
            .' ,group_id='.db_input($vars['group_id'])
            .' ,timezone_id='.db_input($vars['timezone_id'])
            .' ,daylight_saving='.db_input(isset($vars['daylight_saving'])?1:0)
            .' ,username='.db_input($vars['username'])
            .' ,firstname='.db_input($vars['firstname'])
            .' ,lastname='.db_input($vars['lastname'])
            .' ,email='.db_input($vars['email'])
            .' ,phone="'.db_input(Format::phone($vars['phone']),false).'"'
            .' ,phone_ext='.db_input($vars['phone_ext'])
            .' ,mobile="'.db_input(Format::phone($vars['mobile']),false).'"'
            .' ,signature='.db_input($vars['signature'])
            .' ,notes='.db_input($vars['notes'])
            .' ,cookie="'.generate_cookie_string().'"';
            
        if($vars['passwd1'])
            $sql.=' ,passwd='.db_input(Passwd::hash($vars['passwd1']));
                
        if(isset($vars['change_passwd']))
            $sql.=' ,change_passwd=1';
            
        if($id) {
            $sql='UPDATE '.STAFF_TABLE.' '.$sql.' WHERE staff_id='.db_input($id);
            if(db_query($sql) && db_affected_rows())
                return true;
                
            $errors['err']='Unable to update the user. Internal error occurred';
        } else {
            $sql='INSERT INTO '.STAFF_TABLE.' '.$sql.', created=NOW()';
            if(db_query($sql) && ($uid=db_insert_id()))
                return $uid;
                
            $errors['err']='Unable to create user. Internal error';
        }

        return false;
    }

    
    // from StaffSession  parent::Staff, now User
    function isValid(){
        global $_SESSION,$cfg;
        if(!$this->getId() || $this->session->getSessionId()!=session_id())
            return false;
        
        return $this->session->isvalidSession($_SESSION['_staff']['token'],$cfg->getStaffTimeout(),$cfg->enableStaffIPBinding())?true:false;
    }

    function refreshSession(){
        global $_SESSION;
        $_SESSION['_staff']['token']=$this->getSessionToken();
    }
    
    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }
    
    function getIP(){
        return $this->session->getIP();
    }

}
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class User_Session {
   var $session_id = '';
   var $userID='';
   var $browser = '';
   var $ip = '';
   var $validated=FALSE;

   function User_Session($userid){
      $this->browser=(!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
      $this->ip=(!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
      $this->session_id=session_id();
      $this->userID=$userid;
   }

   function isStaff(){
       return FALSE;
   }

   function isClient() {
       return FALSE;
   }


   function getSessionId(){
       return $this->session_id;
   }

   function getIP(){
        return  $this->ip;
   }

   function getBrowser(){
       return $this->browser;
   }
   function refreshSession(){
       //nothing to do...clients need to worry about it.
   }

   function sessionToken(){

      $time  = time();
      $hash  = md5($time.SESSION_SECRET.$this->userID);
      $token = "$hash:$time:".MD5($this->ip);

      return($token);
   }

   function isvalidSession($htoken,$maxidletime=0,$checkip=false){
        global $cfg;
       
        $token = rawurldecode($htoken);
        
        #check if we got what we expected....
        if($token && !strstr($token,":"))
            return FALSE;
        
        #get the goodies
        list($hash,$expire,$ip)=explode(":",$token);
        
        #Make sure the session hash is valid
        if((md5($expire . SESSION_SECRET . $this->userID)!=$hash)){
            return FALSE;
        }
        #is it expired??
        
        
        if($maxidletime && ((time()-$expire)>$maxidletime)){
            return FALSE;
        }
        #Make sure IP is still same ( proxy access??????)
        if($checkip && strcmp($ip, MD5($this->ip)))
            return FALSE;

        $this->validated=TRUE;

        return TRUE;
   }

   function isValid() {
        return FALSE;
   }

}?>
