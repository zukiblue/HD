<?php
require_once('core.php');
require_once('users.class.php');
$users = Users::init();

$user = null;
if ( isset($_GET['id']) )
    $mode = 'edit';
elseif ( isset($_GET['a']) && strcasecmp($_GET['a'], 'add')===0 )
    $mode = 'add';
else
    $mode = 'browse';

if ( $mode==='edit' ) {
    if ( !($user = $users->get($_GET['id'])) ) {
        $mode = 'browse';
        $errors['err'] = 'Unknown or invalid ID.';
    }
    
} elseif ( $mode==='add' ) {
    // Do Nothing
    
} elseif ($_POST){ //save  && !$errors
    switch(strtolower($_POST['a'])){
        case 'upd':
            if(!$users){
                $errors['err']=lang(users_err_invalid);
            }elseif($users->update($_POST,$errors)){
                $msg=lang(users_msg_updok);
            }elseif(!$errors['err']){
                $errors['err']=lang(users_msg_upderror);
            }            
            if($errors['err']) {            
                $mode = 'edit';
                if ( !($user = $users->get($_POST['id'])) ) {
                    $errors['err'] = 'Unknown or invalid ID.';
                }
            }
            break;
        case 'add':
            if(($id=$users->add($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).lang(users_msg_addok);
            }elseif(!$errors['err']){
                $errors['err']=lang('users_msg_adderror');
            }
            if($errors['err']) {
                $mode = 'edit';            }
            break;
        default:
            $errors['err']=lang('users_msg_badaction');
            break;
    }
}

if ($mode==='edit' || $mode==='add') // || $user || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='user.inc.php';
else
    $page='users.inc.php';

$nav->setTabActive('staff');

require('header.inc.php');

//$auth->requireAuthentication(0);
require($page);

include('footer.inc.php');
?>
