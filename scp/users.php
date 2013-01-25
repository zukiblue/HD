<?php
require_once('core.php');
require_once('users.class.php');
$users = Users::init();
//echo var_dump($_REQUEST); 

$user = null;
if ( isset($_GET['id']) )
    $mode = 'edit';
elseif ( isset($_GET['a']) && strcasecmp($_GET['a'], 'add') )
    $mode = 'add';
else
    $mode = 'browse';

if ( $mode==='edit' ) {
    if ( !($user = $users->get($_GET['id'])) ) {
        $editmode = FALSE;
        $errors['err'] = 'Unknown or invalid ID.';
    }
} elseif ( $mode==='add' ) {
    
} elseif ($_POST){ //save
    switch(strtolower($_POST['a'])){
        case 'upd':
            if(!$users){
                $errors['err']=lang(users_err_invalid);
            }elseif($users->update($_POST,$errors)){
                $msg=lang(users_msg_updok);
            }elseif(!$errors['err']){
                $errors['err']=lang(users_msg_upderror);
            }            
            break;
        case 'add':
            if(($id=User::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).lang(users_msg_addok);
 //               $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang('users_msg_adderror');
            }
            break;
        default:
            $errors['err']=lang('users_msg_badaction');
            break;
    }
}

if ($mode==='edit') // || $user || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='user.inc.php';
else
    $page='users.inc.php';

$nav->setTabActive('staff');

require(STAFFINC_DIR.'header.inc.php');

//$auth->requireAuthentication(0);
require($page);

include(STAFFINC_DIR.'footer.inc.php');
?>
