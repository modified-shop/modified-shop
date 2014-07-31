<?php
require_once dirname(__FILE__) . '/../../includes/configure.php';
include_once dirname(__FILE__) . '/../../includes/database_tables.php';
include_once dirname(__FILE__) . '/../../inc/xtc_db_query.inc.php';
include_once dirname(__FILE__) . '/application_idealo.php';
include_once dirname(__FILE__) . '/export_functions_idealo.php';
include_once dirname(__FILE__) . '/idealo_csv_tools.php';

$selection = implode(',', $_POST['select_attribute']);

$sql = "UPDATE `idealo_csv_setting` SET `idealoExportAttributes` = '" . $selection . "';";
xtc_db_query($sql);

echo"    
    <script type='text/javascript'>
        self.close();
    </script>";
?>