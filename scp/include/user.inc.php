<?php
// Protect from direct request
if(basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('Access denied @'.basename(__FILE__));

$info=array();
if($rec && $_REQUEST['a']!='add'){
    $title=lang(user_upd_title);
    $action='upd';
    $submit_text=lang(user_upd_submit);
    $passwd_text=lang(user_upd_passtext);
    $info=$rec;
    //$info['id']=$user->id;
    //$info['teams'] = $user->getTeams();
}else {
    $title=lang(user_add_title);
    $action='add';
    $submit_text=lang(user_add_submit);
    $passwd_text=lang(user_add_passtext).'&nbsp;<span class="error">&nbsp;*</span>';
    //defaults for new user
    $info['change_passwd']=1;
    $info['isactive']=1;
    $info['isvisible']=1;
    $info['isadmin']=0; 
}
//die(var_dump($_POST));
//$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
   
?>

<form action="users.php" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="a" value="<?php echo $action; ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo lang('user_title');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong><?php echo lang('user_subtitle1');?></strong></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
               <?php echo lang('user_form_username');?>
            </td>
            <td>
                <input type="text" size="30" name="username" value="<?php echo $info['username']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['username']; ?></span>
            </td>
        </tr>

        <tr>
            <td width="180" class="required">
                <?php echo lang('user_form_realname');?>
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang(user_form_email);?>
            </td>
            <td>
                <input type="text" size="30" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Account Status & Settings</strong></em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang(user_form_level);?>
            </td>
            <td>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo lang(user_form_status);?>
            </td>
            <td>
                <input type="radio" name="active" value="1" <?php echo $info['active']?'checked="checked"':''; ?>><strong>Active</strong>
                <input type="radio" name="active" value="0" <?php echo !$info['active']?'checked="checked"':''; ?>><strong>Inactive</strong>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['active']; ?></span>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="users.php"'>
</p>
</form>
