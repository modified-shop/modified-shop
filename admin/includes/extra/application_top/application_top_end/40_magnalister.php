<?php
/* magnalister v1.0.1 */
if (defined('MODULE_MAGNALISTER_STATUS') && (MODULE_MAGNALISTER_STATUS == 'True') 
    && !defined('MAGNALISTER_PLUGIN') && file_exists(DIR_FS_DOCUMENT_ROOT.'magnaCallback.php')
    )
{
  ob_start();
  require_once (DIR_FS_DOCUMENT_ROOT.'magnaCallback.php');
  ob_end_clean();
}
/* END magnalister */
?>