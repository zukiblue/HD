<?php
require_once 'basedb.class.php';

class Users extends BaseDB{
        
    static function init() {
        return ( ($users=new Users())?$users:null );
    }
    
    function Users() {
        parent::BaseDB();
        $this->table = TBL_USERS;
        $this->tableAlias = 'users';
        $this->sortOptions=array(
            'name'=>'users.name',
            'username'=>'users.username',
            'status'=>'users.active',
            'created'=>'users.creationdate',
            'login'=>'users.lastlogin');
        $this->defaultColumnOrder = 'name,username';
        return;
    }   

    function load($sortCol, $sortOrd, $start=0, $limit=999999) {
        parent::loadAll($sortCol, $sortOrd, $start, $limit);

// ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();

        $where='WHERE 1 ';
        $groupby = ''; //GROUP BY 
       
        $sql="$this->select $this->from $where $groupby $this->orderby";
        //echo $sql;

        $this->records = parent::queryData($sql);
        
        return ($this->records);
    }
    
    // Add User 
    function add($vars, &$errors) {
        if(($id=self::save(0, $vars, $errors))) {// && $vars['teams'] && ($staff=users::lookup($id)))
            //$staff->updateTeams($vars['teams']);
        }
        return $id;
    }

    // Update User 
    function update($vars, &$errors) {
        if(!$this->save($vars['id'], $vars, $errors))
            return false;
        //$this->updateTeams($vars['teams']);
        //$this->reload();                
        return true;
    }

    function save($id, $vars, &$errors) {
        $vars['username']=Format::striptags($vars['username']);
        $vars['name']=Format::striptags($vars['name']);
        $vars['signature']=Format::striptags($vars['signature']);

        if(!$vars['username'])      
            $errors['username']='Username required';

        if(!$vars['name'])      
            $errors['name']='Real name required';
        
        if(!$vars['email'] || !Validator::is_email($vars['email']))
            $errors['email']='Valid email required';
        //elseif(Email::getIdByEmail($vars['email']))
        //    $errors['email']='Already in-use as system email';
        //elseif(($uid=Staff::getIdByEmail($vars['email'])) && $uid!=$this->getId())
        //    $errors['email']='Email already in-use by another staff member';
        if($errors) return false;
        $sql=' SET changedate=NOW() '
            .' ,username='.db_input($vars['username'])
            .' ,name='.db_input($vars['name'])
            .' ,email='.db_input($vars['email']);
        
        if ($id===0) {
            $sql='INSERT INTO '.TBL_USERS.' '.$sql.', creationdate=NOW()';
            // echo $sql;
            if(db_query($sql) && ($uid=db_insert_id()))
                return $uid;
            $errors['err']='Unable to create user. Internal error';
        } else {
            $sql='UPDATE '.TBL_USERS.' '.$sql.' WHERE id='.db_input($id);            
            // echo $sql;
            if(db_query($sql) && db_affected_rows())
                return true;
            $errors['err']='Unable to update the user. Internal error occurred';
        }              
        return false;
    }


}

?>
