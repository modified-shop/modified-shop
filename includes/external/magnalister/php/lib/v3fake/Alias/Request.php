<?php
class MLApiRequest{
    public static function factoryApiRequestClass(){
        require_once DIR_MAGNALISTER_INCLUDES.'lib'.DIRECTORY_SEPARATOR.'v3'.DIRECTORY_SEPARATOR.'Codepool'.DIRECTORY_SEPARATOR.'90_System'.DIRECTORY_SEPARATOR.'Request'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Request.php';
        return new ML_Request_Model_Request;
    }
}

