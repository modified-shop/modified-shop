<?php
/* magnalister v1.0.0 */
if (defined('MODULE_MAGNALISTER_STATUS') && (MODULE_MAGNALISTER_STATUS == 'True') 
    && !defined('MAGNA_CALLBACK_MODE') && file_exists(DIR_FS_DOCUMENT_ROOT.'magnaCallback.php')
    )
{
  ob_start();
  require_once(DIR_FS_DOCUMENT_ROOT.'magnaCallback.php');
  magnaExecute('magnaCollectStats');
  ob_end_clean();
}
/* END magnalister */
?>