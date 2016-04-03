<?php
if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
}
$_MagnaSession['mpID'] = current(array_keys($magnaConfig['maranon']['Marketplaces']));
$_MagnaSession['currentPlatform'] = magnaGetMarketplaceByID($_MagnaSession['mpID']);
loadDBConfig($_MagnaSession['mpID']);

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');

$o = new MLProductList();
//$o->addAction('MLProductListActionHtml', array('html' => '<input type="hidden" name="fuuuu" value="narf"><input class="mlbtn" type="submit" name="doStuff" value="Zeug">'), 'bottom-right');
$o->injectDependency('MLProductListDependencyHtmlAction', array('actionBottomRightTemplate'=>'<input type="hidden" name="fuuuu" value="narf"><input type="submit" name="doStuff" value="Zeug" class="mlbtn">'));
echo $o;

if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	echo print_m($_POST, '$_POST');
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
exit();