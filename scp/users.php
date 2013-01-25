<?php
require_once('core.php');
require_once('users.class.php');
$users = Users::init();
$user = '';
//$user=null;
echo var_dump($_REQUEST); 
if($_REQUEST['id'] && !($user = $users->get($_REQUEST['id'])))
    $errors['err']='Unknown or invalid user ID.';

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$users){
                $errors['err']=lang($users_err_invalid);
            }elseif($users->update($_POST,$errors)){
                $msg=lang($users_msg_updok);
            }elseif(!$errors['err']){
                $errors['err']=lang($users_msg_upderror);
            }
            break;
        case 'create':
            if(($id=User::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).lang($users_msg_addok);
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=lang($users_msg_adderror);
            }
            break;
        default:
            $errors['err']=lang($users_msg_badaction);
            break;
    }
}

if($user || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='user.inc.php';
else
    $page='users.inc.php';

$nav->setTabActive('staff');

require(STAFFINC_DIR.'header.inc.php');

//$auth->requireAuthentication(0);
require($page);

include(STAFFINC_DIR.'footer.inc.php');
?>
