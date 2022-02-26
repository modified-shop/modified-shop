<?php
class MLFilesystem{
    public static function gi(){
        require_once DIR_MAGNALISTER_INCLUDES.'lib'.DIRECTORY_SEPARATOR.'v3'.DIRECTORY_SEPARATOR.'Codepool'.DIRECTORY_SEPARATOR.'90_System'.DIRECTORY_SEPARATOR.'Filesystem'.DIRECTORY_SEPARATOR.'Filesystem.php';
        return new ML_Filesystem_Filesystem();
    }
}

