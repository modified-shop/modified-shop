<?php

$shopDirectory = dirname(__DIR__, 4);

define('DIR_FS_CATALOG', $shopDirectory . '/');
define('DIR_FS_EXTERNAL', $shopDirectory . '/includes/external/');
define('RUN_MODE_INSTALLER', true);

function xtc_href_link(): string
{
    return '';
}

require $shopDirectory . '/includes/external/smarty/smarty_4/Smarty.class.php';

$smarty = new Smarty();
$templateDirectories = array_values($smarty->getTemplateDir());
$expectedTemplateDirectory = $shopDirectory . '/templates/tpl_modified/';

if (!in_array($expectedTemplateDirectory, $templateDirectories, true)) {
    throw new RuntimeException(sprintf(
        'Der isolierte Smarty-Fallback enthält "%s" nicht: %s',
        $expectedTemplateDirectory,
        implode(', ', $templateDirectories)
    ));
}

echo "Smarty-Fallback auf MY_CURRENT_TEMPLATE erfolgreich.\n";
