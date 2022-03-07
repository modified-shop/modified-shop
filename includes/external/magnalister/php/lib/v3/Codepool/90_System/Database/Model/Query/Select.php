<?php

/**
 * SQL query builder
 */
class ML_Database_Model_Query_Select {

    const JOIN_TYPE_LEFT = 1;
    const JOIN_TYPE_INNER = 2;
    const JOIN_TYPE_OUTER = 3;
    /**
     * prefix of name of the tables could be magnalister by default of could be empty string 
     */
    protected $sMlDbPrefix;
    protected $aResult = null;
    protected $aResultAll = null;
    protected $iResult = null;
    protected $iResultAll = null;
    static protected $aJoinType = array(
        1 => 'LEFT',
        2 => 'INNER',
        3 => 'OUTER'
    );
    protected $sExecutedSql = '';
    protected $aSelect = array();
    protected $aFrom = array();
    protected $aJoin = array();
    protected $aWhere = array();
    protected $aOrderBy = array();
    protected $aLimit = array('from' => 0, 'limit' => null);
    protected $aUpdate = array();
    protected $aUpdateSet = array();
    protected $aDelete = array();

    public function __construct() {
        
    }

    public function setPrefix($sPrifix) {
        $this->sMlDbPrefix = $sPrifix;
        return $this;
    }

    public function init() {
        $oRef = new ReflectionClass($this);
        $aStaticfieldKeys = array_keys($oRef->getStaticProperties());
        foreach ($oRef->getDefaultProperties() as $sKey => $mValue) {
            if (!in_array($sKey, $aStaticfieldKeys)) {
                $this->$sKey = $mValue;
            }
        }
        return $this;
    }
    public function reset(){
        $this->iResult=null;
        $this->iResultAll=null;
        $this->aResult=null;
        $this->aResultAll=null;
    }

    /**
     * Add fields in query selection
     *
     * @param mixed $fields List of fields to concat to other fields
     * @return ML_Database_Model_Query_Select
     */
    public function select($mFields) {
        $this->aResult = null;
        $this->aResultAll = null;
        if (is_array($mFields) && count($mFields) > 0) {
            $this->aSelect = array_merge($this->aSelect, $mFields);
        } else if (!empty($mFields)) {
            $this->aSelect[] = $mFields;
        }
        return $this;
    }

    /**
     * Set table for FROM clause
     *
     * @param string $sTable Table name
     * @return ML_Database_Model_Query_Select
     */
    public function from($sTable, $sAlias = null) {
        $this->aResult = null;
        $this->aResultAll = null;
        $this->iResult=null;
        $this->iResultAll=null;
        if (!empty($sTable)) {
            $this->aFrom[] = '`' . $this->sMlDbPrefix . $sTable . '`' . ($sAlias ? ' ' . $sAlias : '');
        }
        return $this;
    }

    /**
     * 
     * @param string|array $mJoin
     *      if is string like this 'LEFT JOIN '._DB_PREFIX_.'product p ON ...' , 
     *      if is array like this array( tablename , alias , join condition )
     * @param int $iType can be one the const join type
     * @return ML_Database_Model_Query_Select
     */
    public function join($mJoin, $iType = 0) {
        $this->aResult = null;
        $this->aResultAll = null;
        $this->iResult=null;
        $this->iResultAll=null;
        $sJoinPrefix = '';
        if (isset(self::$aJoinType[$iType])) {
            $sJoinPrefix = self::$aJoinType[$iType];
        }
        if (is_array($mJoin)) {
            $this->aJoin[] = " $sJoinPrefix JOIN `" . $this->sMlDbPrefix . $mJoin[0] . '`' . ($mJoin[1] ? ' ' . $mJoin[1] : '') . ($mJoin[2] ? ' ON ' . $mJoin[2] : '');
        } elseif (!empty($mJoin)) {
            $this->aJoin[] = " $sJoinPrefix JOIN " .$mJoin;
        }
        return $this;
    }

    /**
     * see createCondition document
     * @param string|array $mCondition
     * @return ML_Database_Model_Query_Select
     */
    public function where($mCondition) {
        $this->aResult = null;//resultall dont change
        $this->iResult = null;
        $this->aWhere[] = $this->createCondition($mCondition);
        return $this;
    }

    /**
     * if $mCondition =  array( field1 => value1 , field2 => value2 , ... ) add to where clause field1 = value1 AND field2 = value2 AND ...
     * if $mCondition =  array('or' => array( field1 => value1 , field2 => value2 , ... ) )add to where clause field1 = value1 OR field2 = value2 OR ...
     * if $mCondition =  array('prodcuts_id','not in','(100,200,501)' )add to where clause prodcuts_id not in (100,200,501)
     *  
     *  if $mCondition = array('or' => array( 'field1' => 'value1' , "field2 LIKE '%value2%'" , array('or'=>array( array('field4','<>','value4') ,'field5' => 'value5' )  ) ))
     *  WHERE ( ( field1 = 'value1' ) or (field2 LIKE '%value2%') or ( ( field4 <> 'value4' ) or ( field5 = 'value5' ) ) )
     *  
     * if $mCondition = array('or' => array( 'field1' => 'value1' , "field2 LIKE '%value2%'" , array( array('field4','<>','value4') ,'field5' => 'value5' )  ) )
     *  WHERE ( ( field1 = 'value1' ) or (field2 LIKE '%value2%') or ( ( field4 <> 'value4' ) AND ( field5 = 'value5' ) ) )
     * 
     * 
     * if
     * 
     * @param mixed $mCondition
     *     1st form :if is string add to where clause normally
     *     2nd form : if array( field1 => value1 , field2 => value2 , ... ) add to where clause field1 = value1 AND field2 = value2 AND ...
     *     3rd form : if array( array(field1 , '=' , value1) , array(field2, '<>' , value2) , array(field3, 'oparator' , value3) , ... ) 
     *           add to where clause like this field1 = value1 AND field2 <> value2 AND  field3 operator value3 AND ...
     * 
     *     4th form : if array(or => (array(1st or 2nd or 3rd form))  )
     *     5th form : if array(AND => (1st or 2nd or 3rd form) ) == if array(1st or 2nd or 3rd form )
     * @return ML_Database_Model_Query_Select
     */
    protected function createCondition($mCondition, $sBoolOperator = 'AND') {
        $oDB = MLDatabase::getDbInstance();
        $sWhere = '';
        if (is_array($mCondition)) {
            $aWhere = array();
            foreach ($mCondition as $sMixed => $mValue) {
                //fieldname, oprator , value 
                if (gettype($sMixed) === "integer" && gettype($mValue) === "string") {
                    //use prefix N' because of Natinal language http://www.9lessons.info/2011/08/foreign-languages-using-mysql-and-php.html
                    $sStartQoute = strpos($mCondition[1], "in") === FALSE ? "N'" : '';
                    $sQoute = strpos($mCondition[1], "in") === FALSE ? "'" : '';
                    $aWhere[] = " " . $oDB->escape($mCondition[0]) . " " . $oDB->escape($mCondition[1]) . " $sStartQoute" . $oDB->escape($mCondition[2]) . "$sQoute ";
                    break;
                }
                //fieldname ,value           
                else if (gettype($sMixed) === "string" && (gettype($mValue) === "string" || gettype($mValue) === "integer")) {
                    $sQoute = gettype($mValue) === "string" ? "'" : '';
                    $aWhere[] = " " . $oDB->escape($sMixed) . " = $sQoute" . $oDB->escape($mValue) . "$sQoute ";
                } else if (is_array($mValue)) {
                    //or , and
                    if (in_array(strtolower($sMixed), array('or', 'and'))) {
                        $sBoolOperator = $sMixed;
                        foreach ($mValue as $sKey => $mWhereClause) {
                            if (gettype($sKey) === "string") {
                                $aWhere[] = $this->createCondition(array("$sKey" => $mWhereClause), $sBoolOperator);
                            } elseif (gettype($mWhereClause) === "string") {
                                $aWhere[] = $this->createCondition($mWhereClause, $sBoolOperator);
                            } else if (is_array($mWhereClause)) {
                                $aWhere[] = $this->createCondition($mWhereClause, 'AND');
                            }
                        }
                    } else {
                        $aWhere[] = $this->createCondition($mValue, $sBoolOperator);
                    }
                }
            }
            if (count($aWhere) > 1) {
                $sWhere = ' (' . implode(") $sBoolOperator (", $aWhere) . ")\n";
            } elseif (isset($aWhere[0])) {
                $sWhere = $aWhere[0];
            } else {
                // echo "<div  class='noticeBox'>".print_m($aWhere)."</div>";
            }
        } elseif (!empty($mCondition)) {
            $sWhere = $mCondition; //echo "333:<br>".print_m($sWhere)."<br>";
        }

        return $sWhere;
    }

    /**
     * Add an ORDER B restriction
     *
     * @param string $fields List of fields to sort. E.g. $this->order('myField, b.mySecondField DESC')
     * @return ML_Database_Model_Query_Select
     */
    public function orderBy($sFields) {
        $this->aResult = null;
        $this->aResultAll = null;
        if (!empty($sFields)) {
            $this->aOrderBy[] = $sFields;
        }
        return $this;
    }

    /**
     * Limit results in query
     * @param type $iLimit
     * @param type $iFrom 
     * @return ML_Database_Model_Query_Select
     * @todo refactor to natural speach 
     * - limit from, count
     * - limit count
     */
    public function limit( $iFrom ,$iLimit=null) {
        $this->aResult = null;
        $this->iResult=null;
        $this->aLimit = array(
            'from' => $iLimit===null?'0':$iFrom ,
            'limit' =>  $iLimit===null?$iFrom:$iLimit ,
        );
        return $this;
    }
    
    /**
     * Add Table in delete query 
     *
     * @param mixed $mTable List of fields to concat to other $mTable that should be deleted from
     * @return ML_Database_Model_Query_Select
     */
    public function delete($mTable) {
        if (is_array($mTable) && count($mTable) > 0) {
            $this->aDelete = array_merge($this->aDelete, $mTable);
        } else if (!empty($mTable)) {
            $this->aDelete[] = $mTable;
        }
        return $this;
    }
    
    /**
     * Add Table in update query and add SET part
     *
     * @param mixed $mTable List of fields to concat to other $mTable that should be deleted from
     * @return ML_Database_Model_Query_Select
     */
    public function update($mTable , $aSet ) {
        if (is_array($mTable) && count($mTable) > 0) {
            $this->aUpdate = array_merge($this->aUpdate, $mTable);
        } else if (!empty($mTable)) {
            $this->aUpdate[] = $mTable;
        }
        $this->aUpdateSet = $aSet;
        return $this;
    }
    
    protected function buildDelete() {
        return 'DELETE ' . ((count($this->aDelete) > 0) ? implode(",\n", $this->aDelete) : '' ) . "  ";
    }

    protected function buildUpdate() {
        return 'UPDATE ' . ((count($this->aUpdate) > 0) ? implode(",\n", $this->aUpdate) : '' ) . "  ";
    }

    protected function buildUpdateSet() {
        $sUpdateSet = 'SET ';
        foreach($this->aUpdateSet as $sKey => $sValue ){
            $sUpdateSet .= " $sKey =  '$sValue' , ";
        }
        return substr($sUpdateSet, 0,-2);
    }

    protected function buildSelect() {
        return 'SELECT ' . ((count($this->aSelect) > 0) ? implode(",\n", $this->aSelect) : '*' ) . "\n";
    }

    protected function buildFrom() {
        if (count($this->aFrom) > 0) {
            return 'FROM ' . implode(', ', $this->aFrom) . "\n";
        } else {
            throw new Exception('buildFrom() missed tables');
        }
    }

    protected function buildJoin() {
        if (count($this->aJoin) > 0) {
            return implode("\n", $this->aJoin) . "\n";
        } else {
            return '';
        }
    }

    protected function buildWhere() {
        if (count($this->aWhere) > 0) {
            return 'WHERE (' . implode(') AND (', $this->aWhere) . ")\n";
        } else {
            return '';
        }
    }


    protected function buildOrderBy() {
        if (count($this->aOrderBy) > 0) {
            return 'ORDER BY ' . implode(', ', $this->aOrderBy) . "\n";
        } else {
            return '';
        }
    }

    protected function buildLimit() {
        if ($this->aLimit['limit'] || $this->aLimit['from']) {
            $limit = $this->aLimit;
            return 'LIMIT ' . (($limit['from']) ? $limit['from'] . ', ' . $limit['limit'] : $limit['limit']);
        } else {
            return '';
        }
    }
    
    /**
     * Generate and get the query
     * @var type $blCount if true the function return count of rows that is selected
     * @return string
     */
    protected function buildSql($aBuilderFunction ) {
        $sSql = '';
        foreach ($aBuilderFunction as $sBuilder) {
            $sSql .= $this->{"build$sBuilder"}();
        }

        return $sSql;
    }

    /**
     * return array of rows
     * @todo long query log
     * @return array 
     */
    public function getResult() {
        if ($this->aResult === null) {
            $this->sExecutedSql =
                    $this->buildSql(
                    array(
                        'select' => 'Select',
                        'from' => 'From',
                        'join' => 'Join',
                        'where' => 'Where',
                        'orderby' => 'OrderBy',
                        'limit' => 'Limit'
                    )
            );
//            $i=microtime(true);
            $this->aResult = MLDatabase::getDbInstance()->fetchArray($this->sExecutedSql);
//            echo microtime(true)-$i.'<br />';
//            if(microtime(true)-$i>1){
//                echo $this->buildSql();
//            }
        }
        return $this->aResult;
    }

    /**
     * return array of rows
     * @return array 
     */
    public function getAll() {
        if ($this->aResultAll === null) {
            $this->sExecutedSql = $this->buildSql(
                    array(
                        'select' => 'Select',
                        'from' => 'From',
                        'join' => 'Join',
                        'where' => 'Where',
                        'orderby' => 'OrderBy',
                    )
            );
            $this->aResultAll = MLDatabase::getDbInstance()->fetchArray($this->sExecutedSql);
        }
        return $this->aResultAll;
    }

    /**
     * rturn count of selected row ocording to with limit included or excluded
     * @param type $blTotal , if true exclude limit from select and otherwise it will be included
     * @return int
     */
    public function getCount($blTotal = true, $sField ='*' ) {
        if(!$blTotal){
            if($this->iResult===null){
                $this->iResult=count($this->getResult());
            }
            return $this->iResult;
        }else{
            if($this->iResultAll===null){
                $this->sExecutedSql = "SELECT COUNT($sField) as count " . $this->buildSql(
                                array(
                                    'from' => 'From',
                                    'join' => 'Join',
                                    'where' => 'Where',
                                )
                );
                $this->iResultAll = MLDatabase::getDbInstance()->fetchOne($this->sExecutedSql);
            }
            return $this->iResultAll;
        }
    }
    
    /**
     * create delete query and execute this query
     */
    public function doDelete(){
        $this->sExecutedSql = $this->buildSql(
                    array(
                        'delete' => 'Delete',
                        'from' => 'From',
                        'join' => 'Join',
                        'where' => 'Where'
                    )
            );
            MLDatabase::getDbInstance()->query($this->sExecutedSql);
            return MLDatabase::getDbInstance()->getAffectedRows();
    }    
        
    /**
     * create update query and execute this query
     */
    public function doUpdate(){
        $this->sExecutedSql = $this->buildSql(
                    array(
                        'update' => 'Update',
                        'join' => 'Join',
                        'updateset'=>'UpdateSet',
                        'where' => 'Where'
                    )
            );
            MLDatabase::getDbInstance()->query($this->sExecutedSql);
            return MLDatabase::getDbInstance()->getAffectedRows();
    }
    
    /**
     * 
     * @param bool $blExecuted ?executed query:calculated query
     * @return string
     */
    public function getQuery($blExecuted = true){
        if($blExecuted === true){
            return $this->sExecutedSql;
        }else{
            return $this->buildSql( 
                    array(
                        'select' => 'Select',
                        'from' => 'From',
                        'join' => 'Join',
                        'where' => 'Where',
                        'orderby' => 'OrderBy',
                        'limit' => 'Limit'
                    )
           );
        }
    }

}
