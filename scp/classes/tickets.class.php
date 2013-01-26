<?php
require_once 'basedb.class.php';

class Tickets extends BaseDB{
        
    static function init() {
        return ( ($tickets=new Tickets())?$tickets:null );
    }
    
    function Tickets() {
        parent::BaseDB();
        $this->table = TBL_TICKETS;
        $this->tableAlias = 'ticket';
        $this->sortOptions=array(
            'name'=>'users.name',
            'username'=>'users.username',
            'status'=>'users.active',
            'created'=>'users.creationdate',
            'login'=>'users.lastlogin');
        $this->defaultColumnOrder = 'name,username';
        return;
    }   

    function load($sortCol, $sortOrd) {
        parent::load($sortCol, $sortOrd);
// ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();

        $where='WHERE 1 ';
        $groupby = ''; //GROUP BY 
        $limit1 ='';
        $limit2 ='';

        //$sql="$this->select $this->from $where $groupby $this->orderby";
        //echo $sql;
        $sql=$this->select.', lock_id, dept_name, priority_desc '
            .' ,count(attach.attach_id) as attachments '
            .' ,count(DISTINCT message.id) as messages '
            .' ,count(DISTINCT response.id) as responses '
            .' ,count(DISTINCT note.id) as notes '
            .$this->from               
            .' LEFT JOIN '.DEPT_TABLE.' dept ON (ticket.dept_id=dept.dept_id) '
            .' LEFT JOIN '.TICKET_PRIORITY_TABLE.' pri ON ('
                .'ticket.priority_id=pri.priority_id) '
            .' LEFT JOIN '.TICKET_LOCK_TABLE.' tlock ON ('
                .'ticket.ticket_id=tlock.ticket_id AND tlock.expire>NOW()) '
            .' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON ('
                .'ticket.ticket_id=attach.ticket_id) '
            .' LEFT JOIN '.TICKET_THREAD_TABLE.' message ON ('
                ."ticket.ticket_id=message.ticket_id AND message.thread_type = 'M') "
            .' LEFT JOIN '.TICKET_THREAD_TABLE.' response ON ('
                ."ticket.ticket_id=response.ticket_id AND response.thread_type = 'R') "
            .' LEFT JOIN '.TICKET_THREAD_TABLE.' note ON ( '
                ."ticket.ticket_id=note.ticket_id AND note.thread_type = 'N') "
          //  .' WHERE ticket.ticket_id='.db_input($id)
            .' GROUP BY ticket.ticket_id';

        
        parent::queryData($sql);
        //echo $sql;

        return ($this->records);
    }
}
?>
