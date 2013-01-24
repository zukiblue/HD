<?php
require_once('core.php');
require_once 'user.class.php';
$user=null;

if($_REQUEST['id'] && !($user=User::get($_REQUEST['id'])))
    $errors['err']='Unknown or invalid user ID.';

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$user){
                $errors['err']=lang($users_err_invalid);
            }elseif($user->update($_POST,$errors)){
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
        /*case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = 'You must select at least one user.';
            } elseif(in_array($user->getId(),$_POST['ids'])) {
                $errors['err'] = 'You can not disable/delete yourself - you could be the only admin!';
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected user activated';
                            else
                                $warn = "$num of $count selected user activated";
                        } else {
                            $errors['err'] = 'Unable to activate selected user';
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0 '
                            .' WHERE staff_id IN ('.implode(',',$_POST['ids']).') AND staff_id!='.db_input($thisstaff->getId());

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected user disabled';
                            else
                                $warn = "$num of $count selected user disabled";
                        } else {
                            $errors['err'] = 'Unable to disable selected user';
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$thisstaff->getId() && ($s=Staff::lookup($v)) && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = 'Selected user deleted successfully';
                        elseif($i>0)
                            $warn = "$i of $count selected user deleted";
                        elseif(!$errors['err'])
                            $errors['err'] = 'Unable to delete selected user.';
                        break;
                    default:
                        $errors['err'] = 'Unknown action. Get technical help!';
                }
                    
            }
            break;*/
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

/*if(!defined('OSTADMININC') || !$user|| !$user->isAdmin())
    echo 'Access Denied'; 
else {    */
require($page);
//}
include(STAFFINC_DIR.'footer.inc.php');
?>
