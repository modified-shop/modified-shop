<?php

require_once dirname(__FILE__) . '/../../includes/configure.php';
include_once dirname(__FILE__) . '/../../includes/database_tables.php';
include_once dirname(__FILE__) . '/../../inc/xtc_db_query.inc.php';
include_once dirname(__FILE__) . '/application_idealo.php';
include_once dirname(__FILE__) . '/export_functions_idealo.php';
include_once dirname(__FILE__) . '/idealo_csv_tools.php';

$export_query = xtc_db_query("SELECT 
                                    `products_options_id`,
                                    `products_options_name` 
                              FROM 
                                    `products_options` 
                              WHERE 
                                    `language_id` = '" . $_POST [ 'languages_id' ] . "';");

$html = '<form id="closeAttributeSelection" action="idealo_csv_save_attribute_selection.php" method="post">';

$selectionInDB = xtc_db_query("SELECT `idealoExportAttributes` FROM `idealo_csv_setting` LIMIT 1");
$selectionInDB = xtc_db_fetch_array($selectionInDB);
$selectionInDB = explode(',', $selectionInDB['idealoExportAttributes']);

while($att = xtc_db_fetch_array($export_query)){
    $checked = '';
    if(in_array($att['products_options_id'], $selectionInDB)){
        $checked = 'checked';
    }
    $html .= '<input type="checkbox" name="select_attribute[]' . $att['products_options_id'] .'" value="' . $att['products_options_id'] . '" ' . $checked . '>' . htmlentities($att['products_options_name']) . '<br>';
}

$html .= '<br>
            <input id="save" type="submit" name="save" value="save" onSubmit="window.close()" />
        </form>';

echo $html;
?>