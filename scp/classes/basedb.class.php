<?php

class BaseDB {
    var $orderWays;
    var $records;
    // Initializated on parent::load
    var $select;
    var $from;
    var $sort;
    var $order;
    var $orderby;
    // Initializated on Child
    var $table;
    var $tableAlias;
    var $sortOptions;
    var $primaryKeyField;
    var $defaultColumnOrder;

    function BaseDB() {
        $this->orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
        $this->primaryKeyField='id';
        return;
    }   
    //
    function loadAll($sortCol, $sortOrd, $start=0, $limit=999999) {
        // is set
        if ($sortCol) $_sortCol = strtolower($sortCol);
        
        $sort=($_sortCol && $this->sortOptions[$_sortCol])?$_sortCol: $this->defaultColumnOrder;
        // order or default order
        if($sort && $this->sortOptions[$sort]) 
            $ordercolumn=$this->sortOptions[$sort];
        else
            $ordercolumn=$this->defaultColumnOrder;

        // is set
        if ($sortOrd && $this->orderWays[strtoupper($sortOrd)])
            $order=$this->orderWays[strtoupper($sortOrd)];
        else
            $order='ASC';
        // Order first column by Order Direction
        //if($ordercolumn && strpos($ordercolumn,','))
        //    $ordercolumn=str_replace(','," $order,",$ordercolumn);
        
        $this->sort = $sort;
        $this->order = $order;

        $this->select ='SELECT '.$this->tableAlias.'.* ';
        $this->from   ='FROM '.$this->table.' '.$this->tableAlias.' ';
        $this->orderby='ORDER BY '.$ordercolumn.' '.$order.' '
                .'LIMIT '.$start.','.$limit;
    }
    // Get 1 Record
    function loadRecord($id) {
        $sql='SELECT * FROM '.$this->table.' WHERE '.$this->primaryKeyField.'='.db_input($id);

        $this->record = $this->getRecordFromSQL($sql);
        return ($this->record);
    }
    // Execute and Test query
    function queryData($sql) {        
        if(!($r=db_query($sql)) || !db_num_rows($r))
            $r = NULL;
        return ($r);
    }
    function getRecordFromSQL($sql) {        
        $r = $this->queryData($sql);
        $this->record = mysql_fetch_array($r);
        return ($this->record);
    }
    // Get Total Records
    function recordCountTotal() {        
        return db_count('SELECT count(*) FROM '.$this->table);
    }
    // Get Next Order for grid
    function getReverseOrder() {
        return ($this->order=='DESC'?'ASC':'DESC');
    }
    // Get all Records
    function getRecords() {
        return $this->records;
    }
}

?>
