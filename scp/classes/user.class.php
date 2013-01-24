<?php

class User {
    var $record;
    var $id;
        
    static function get($id) {
        return ($id && is_numeric($id) && ($user= new User($id)) && $user->id==$id)?$user:null;
    }
    
    function User($id) {
        $this->id =0;
        return ($this->load($id));
    }    
    
    function load($var='') {
        if(!$var && !($var=$this->id))
            return false;

        $sql='SELECT users.*, users.creationdate '
            .' FROM '.TBL_USERS.' users ';
 
        $sql.=sprintf(' WHERE %s=%s',is_numeric($var)?'id':'username',db_input($var));

        if(!($res=db_query($sql)) || !db_num_rows($res))
            return NULL;
        
        $this->record=db_fetch_array($res);
        $this->id  = $this->record['id'];
        /*
        $this->teams = $this->ht['teams'] = array();
\        $this->group = $this->dept = null;
        $this->departments = $this->stats = array();
        */
        return ($this->id);
    }

    function getRecord() {
        return $this->record;
    }

}

?>
