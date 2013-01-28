<?php
// Protect from direct request
if(basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('Access denied @'.basename(__FILE__));
?>
<h2><?php lang('users_title'); ?></h2>
<div style="width:700; float:left;">
    <form action="users.php" method="GET" name="filter">
     <input type="hidden" name="a" value="filter" >
        <select name="did" id="did">
             <option value="0">&mdash; All Department &mdash;</option>
             <?php
             $sql='SELECT dept.dept_id, dept.dept_name,count(staff.staff_id) as users  '.
                  'FROM '.DEPT_TABLE.' dept '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.dept_id=dept.dept_id) '.
                  'GROUP By dept.dept_id HAVING users>0 ORDER BY dept_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name, $userss)=db_fetch_row($res)){
                     $sel=($_REQUEST['did'] && $_REQUEST['did']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$userss);
                 }
             }
             ?>
        </select>
        <select name="gid" id="gid">
            <option value="0">&mdash; All Groups &mdash;</option>
             <?php
             $sql='SELECT grp.group_id, group_name,count(staff.staff_id) as users '.
                  'FROM '.GROUP_TABLE.' grp '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.group_id=grp.group_id) '.
                  'GROUP BY grp.group_id ORDER BY group_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$userss)=db_fetch_row($res)){
                     $sel=($_REQUEST['gid'] && $_REQUEST['gid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$userss);
                 }
             }
             ?>
        </select>
        <select name="tid" id="tid">
            <option value="0">&mdash; All Teams &mdash;</option>
             <?php
             $sql='SELECT team.team_id, team.name, count(member.staff_id) as users FROM '.TEAM_TABLE.' team '.
                  'INNER JOIN '.TEAM_MEMBER_TABLE.' member ON(member.team_id=team.team_id) '.
                  'GROUP BY team.team_id ORDER BY team.name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$userss)=db_fetch_row($res)){
                     $sel=($_REQUEST['tid'] && $_REQUEST['tid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$userss);
                 }
             }
             ?>
        </select>
        &nbsp;&nbsp;
        <input type="submit" name="submit" value="Apply"/>
    </form>
 </div>
<div style="float:right;text-align:right;padding-right:5px;"><b><a href="users.php?a=add" class="Icon newstaff">Add New Staff</a></b></div>
<div class="clear"></div>
<?php

$qstr='';
//$users=Users::init();
$res = $users->load($_REQUEST['sort'], $_REQUEST['order']);
// CSS & Qry string
$qstr.='&order='.$users->getReverseOrder();
$x=$users->sort.'_sort';
$$x=' class="'.strtolower($users->order).'" ';

// PAGING
/*
$total=$users->recordCount();
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$pageNav->setURL('users.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
if($res && ($num=db_num_rows($res)))        
    $showing=$pageNav->showing();
else
    $showing='No user found!';
*/
?>
<form action="users.php" method="POST" name="users" >
 <?php csrf_token(); ?>
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>        
            <th width="200"><a <?php echo $name_sort; ?> href="users.php?<?php echo $qstr; ?>&sort=name">Name</a></th>
            <th width="100"><a <?php echo $username_sort; ?> href="users.php?<?php echo $qstr; ?>&sort=username">UserName</a></th>
            <th width="100"><a  <?php echo $status_sort; ?> href="users.php?<?php echo $qstr; ?>&sort=status">Status</a></th>
            <th width="120"><a  <?php echo $group_sort; ?>href="users.php?<?php echo $qstr; ?>&sort=group">Group</a></th>
            <th width="150"><a  <?php echo $dept_sort; ?>href="users.php?<?php echo $qstr; ?>&sort=dept">Department</a></th>
            <th width="100"><a <?php echo $created_sort; ?> href="users.php?<?php echo $qstr; ?>&sort=created">Created</a></th>
            <th width="145"><a <?php echo $login_sort; ?> href="users.php?<?php echo $qstr; ?>&sort=login">Last Login</a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($res && db_num_rows($res)):
            $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['id'],$ids))
                    $sel=true;
                ?>
               <tr id="<?php echo $row['id']; ?>">
                <td width=7px>  
                    <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['id']; ?>" <?php echo $sel?'checked="checked"':''; ?> >
       
                <td><a href="users.php?id=<?php echo $row['id']; ?>"><?php echo Format::htmlchars($row['name']); ?></a>&nbsp;</td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['active']?'Active':'<b>Inactive</b>'; ?>&nbsp;<?php echo $row['onvacation']?'<small>(<i>vacation</i>)</small>':''; ?></td>
                <td></td>
                <td></td>
                <td><?php echo Format::db_date($row['creationdate']); ?></td>
                <td><?php echo Format::db_datetime($row['lastlogin']); ?>&nbsp;</td>
               </tr>
            <?php
            } //end of while.
        endif; 
        ?>
    <tfoot>
     <tr>
        <td colspan="8">
            <?php if($res && $num){ ?>
            Select:&nbsp;
            <a id="selectAll" href="#ckb">All</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">None</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Toggle</a>&nbsp;&nbsp;
            <?php }else{
                echo 'No staff members found!';
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if($res && $num): //Show options..
    echo '<div>&nbsp;Page:'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="enable" value="Enable" >
    &nbsp;&nbsp;
    <input class="button" type="submit" name="disable" value="Lock" >
    &nbsp;&nbsp;
    <input class="button" type="submit" name="delete" value="Delete">
</p>
<?php
endif;
  mysql_free_result($res);
?>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Please Confirm</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        Are you sure want to <b>enable</b> (unlock) selected staff?
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        Are you sure want to <b>disable</b> (lock) selected staff?
        <br><br>Locked staff won't be able to login to Staff Control Panel.
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Are you sure you want to DELETE selected staff?</strong></font>
        <br><br>Deleted staff CANNOT be recovered.
    </p>
    <div>Please confirm to continue.</div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="No, Cancel" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="button" value="Yes, Do it!" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

