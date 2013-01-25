<?php

class Users {
    var $records;
    var $sort;
    var $order;
        
    static function init() {
        return ( ($users=new Users())?$users:null );
    }
    
    function Users() {
        //$this->id =0;
        //$this->load($id)
        return;
    }   

    function load($sortCol, $sortOrd) {
        // ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();

        $sortOptions=array(
            'name'=>'users.name',
            'username'=>'users.username',
            'status'=>'users.active',
            'created'=>'users.creationdate',
            'login'=>'users.lastlogin');
        $orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
        
        // is set
        if ($sortCol) $_sortCol = strtolower($sortCol);
        $sort=($_sortCol && $sortOptions[$_sortCol])?$_sortCol:'name';
        //Sorting options...
        if($sort && $sortOptions[$sort]) 
            $ordercolumn=$sortOptions[$sort];
        // order or default order
        $ordercolumn=$ordercolumn?$ordercolumn:'name,username';

        // is set
        if ($sortOrd && $orderWays[strtoupper($sortOrd)]);
            $order=$orderWays[strtoupper($sortOrd)];
        
        $order=$order?$order:'ASC';
        if($ordercolumn && strpos($ordercolumn,','))
            $ordercolumn=str_replace(','," $order,",$ordercolumn);
        
        $orderby="$ordercolumn $order ";  
        $this->sort = $sort;
        $this->order = $order;
        
        $select='SELECT users.* ';
        $from='FROM '.TBL_USERS.' users ';
        $where='WHERE 1 ';
        $groupby = ''; //GROUP BY 
        $limit1 ='';
        $limit2 ='';

        $sql="$select $from $where $groupby ORDER BY $orderby";
        //echo $sql;

        if(!($this->records=db_query($sql)) || !db_num_rows($this->records))
            return NULL;

        return ($this->records);// ($this->id);
    }
    // Get all Records
    function getRecords() {
        return $this->records;
    }
    // Get Total Records
    function recordCount() {
        $from='FROM '.TBL_USERS.' users ';
        $where='WHERE 1 ';
        return db_count('SELECT count(users.id) '.$from.' '.$where);
    }
    // Get Next Order for grid
    function getReverseOrder() {
        return ($this->order=='DESC'?'ASC':'DESC');
    }
    // Get 1 Record
    function get($id) {
        $sql='SELECT * FROM '.TBL_USERS.' WHERE id='.$id;

        if(!($this->records=db_query($sql)) || !db_num_rows($this->records))
            return NULL;
        $this->record = mysql_fetch_array($this->records);
        return ($this->record);
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
