<?php
class MLDatabase{
    public static function getDbInstance(){
        return MagnaDB::gi();
    }
	public static function factorySelectClass(){
		require_once DIR_MAGNALISTER_INCLUDES.'lib'.DIRECTORY_SEPARATOR.'v3'.DIRECTORY_SEPARATOR.'Codepool'.DIRECTORY_SEPARATOR.'90_System'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Query'.DIRECTORY_SEPARATOR.'Select.php';
        return new ML_Database_Model_Query_Select;
    }
}

