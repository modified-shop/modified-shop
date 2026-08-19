<?php

require_once DIR_FS_CATALOG . 'includes/classes/storefront/bootstrap.php';

class EvaledFileResource extends \Smarty\Resource\FilePlugin
{
    public $recompiled = true;

    public function supportsCompiledTemplates(): bool
    {
        return false;
    }

    public function checkTimestamps(): bool
    {
        return false;
    }
}

function create_installer_smarty(): Smarty
{
    $smarty = new Smarty();
    $smarty->setTemplateDir(DIR_FS_INSTALLER . 'templates')
           ->registerResource('file', new EvaledFileResource())
           ->setConfigDir(DIR_FS_INSTALLER . 'lang')
           ->setCaching(Smarty::CACHING_OFF);

    $script_name = basename($_SERVER['PHP_SELF'] ?? '');
    $installer_modes = [
        'autoupdate.php' => 'autoupdate',
        'update.php' => 'update',
        'install_step1.php' => 'install',
        'install_step2.php' => 'install',
        'install_finished.php' => 'install',
    ];
    if (isset($installer_modes[$script_name])) {
        $smarty->assign('INSTALLER_MODE', $installer_modes[$script_name]);
    }

    return $smarty;
}
