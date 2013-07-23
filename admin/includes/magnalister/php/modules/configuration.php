<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: configuration.php 2437 2013-05-06 13:32:58Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/**
 * Global Configuration
 */
function tryToFixAccessToTmpDirOnReallyRetardedServers () {
	if (!function_exists('sys_get_temp_dir')) {
		function sys_get_temp_dir() {
			if ($temp = getenv('TMP'))    return $temp;
			if ($temp = getenv('TEMP'))   return $temp;
			if ($temp = getenv('TMPDIR')) return $temp;
			if ($temp = getenv('TMPDIR')) return $temp;
			ob_start();
			$errLv = error_reporting(-1);
			$temp = tempnam(__FILE__, '');
			$warning = ob_get_contents();
			ob_end_clean();
			error_reporting($errLv);
			if (!empty($warning) && preg_match('/File\(([^\)]*)\) is not within/', $warning, $match)) {
				return $match[1];
			}
			if (file_exists($temp)) {
				unlink($temp);
				return dirname($temp);
			}
		    return false;
		}
	}
	if (!function_exists('is_writable_retarded')) {
		function is_writable_retarded($dir) {
			ob_start();
			$errLv = error_reporting(-1);
			$b = is_writeable($dir);
			$warning = ob_get_contents();
			ob_end_clean();
			error_reporting($errLv);
			if (!empty($warning)) {
				return false;
			}
			return $b;
		}
	}
	
	$tmpDir = sys_get_temp_dir();
	if (is_writable_retarded($tmpDir)) return true;

	ob_start();
	$errLv = error_reporting(-1);		
	putenv('TMPDIR=' . ini_get('upload_tmp_dir'));
	$warning = ob_get_contents();
	ob_end_clean();
	error_reporting($errLv);
	if (!empty($warning)) {
		return false;
	}
	return true;
}

function verifyFTPLogin() {
	if (!tryToFixAccessToTmpDirOnReallyRetardedServers()) {
		return ML_ERROR_FTP_NOT_WORKY_CAUSE_OF_RETARDED_PHPCONFIG;
	}
	$ftpAccess = array(
		'host' => getDBConfigValue('general.ftp.host', '0'),
		'port' => getDBConfigValue('general.ftp.port', '0'),
		'user' => getDBConfigValue('general.ftp.username', '0'),
		'pass' => getDBConfigValue('general.ftp.password', '0'),
	);
	foreach ($ftpAccess as $val) {
		if (empty($val)) return ML_ERROR_FTP_INCOMPLETE_DATA;
	}

	$ftpLayer = new FTPConnect(
		$ftpAccess['host'], $ftpAccess['port'],
		$ftpAccess['user'], $ftpAccess['pass']
	);

	if (!$ftpLayer->isConnected()) {
		return ML_ERROR_FTP_CANNOT_CONNECT;
	}
	$finalFTPPath = '/';
	
	$temp = $ftpLayer->getlist();
	if (empty($temp)) {
		return ML_ERROR_FTP_CANNOT_CONNECT;
	}
	$partFound = false;
	$possiblePaths = array();
	foreach ($temp as $file) {
		if (($file['filename'] == '.') || ($file['filename'] == '..') || ($file['type'] != 'dir')) continue;
		if (($pos = strpos(DIR_FS_DOCUMENT_ROOT, $file['filename'].'/')) !== false) {
			$possiblePaths[] = $pos;
		}
	}

	$finalPathFound = false;
	if (!empty($possiblePaths)) {
		foreach ($possiblePaths as $pos) {
			$tmpPath = $finalFTPPath;
			
			$ftpLayer->cd('/');

			$subPath = explode('/', ltrim(rtrim(substr(DIR_FS_DOCUMENT_ROOT, $pos), '/'), '/'));
			$firstDir = array_shift($subPath);
			$ftpLayer->cd($firstDir);
			$tmpPath .= $firstDir.'/';
		
			if (!empty($subPath)) {
				foreach ($subPath as $pathElem) {
					$temp = $ftpLayer->getlist();
					if (!array_key_exists($pathElem, $temp)) {
						break;
					}
					$tmpPath .= $pathElem.'/';
					$ftpLayer->cd($pathElem);
				}
			}
			$mlTestFile = 'magna_test_file_'.time();
			/* $finalFTPPath should be final by now. Verify it. */
			ob_start();
			$ftpLayer->uploadFileContents('blubb', $tmpPath.$mlTestFile);
			$err = ob_get_contents();
			ob_end_clean();
			if (strpos($err, 'Permission denied')) {
				return ML_ERROR_FTP_PERMISSION_DENIED;
			} else if (!empty($err)) {
				return ML_ERROR_FTP_RW_ERROR;
			}
			if (file_exists(DIR_FS_DOCUMENT_ROOT.$mlTestFile)) {
				$finalPathFound = true;
				$finalFTPPath = $tmpPath;
				$ftpLayer->deleteFile($mlTestFile);
				break;
			}
			$ftpLayer->deleteFile($mlTestFile);
		}
	}
	
	if (!$finalPathFound) {
		$mlTestFile = 'magna_test_file_'.time();
		/* $finalFTPPath should be final by now. Verify it. */
		$ftpLayer->uploadFileContents('blubb', $finalFTPPath.$mlTestFile);
		$finalPathFound = file_exists(DIR_FS_DOCUMENT_ROOT.$mlTestFile);
		$ftpLayer->deleteFile($mlTestFile);
	}
	if (!$finalPathFound) {
		return ML_ERROR_FTP_PATH_DOES_NOT_MATCH;
	}
	return $finalFTPPath;
}

$_MagnaSession['mpID'] = '0';
 
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');

/*
MagnaConnector::gi()->setTimeOutInSeconds(1);
try {
	MagnaConnector::gi()->submitRequest(array(
		'ACTION' => 'Ping',
		'SUBSYSTEM' => 'Core',
	));
} catch (MagnaException $e) {}
MagnaConnector::gi()->resetTimeOut();
*/

$form = json_decode(file_get_contents(DIR_MAGNALISTER.'config/'.$_lang.'/global.form'), true);
if (!MAGNA_SAFE_MODE) {
	unset($form['ftp']);
}

$keysToSubmit = array();

/* {Hook} "GenericConfiguration": Enables you to extend the generic configuration mask<br>
   Variables that can be used: 
   <ul><li>$form: The array that is used to generate the form.</li>
   </ul>
 */
if (($hp = magnaContribVerify('GenericConfiguration', 1)) !== false) {
	require($hp);
}

$cG = new Configurator($form, $_MagnaSession['mpID'], 'conf_general');
$cG->processPOST($keysToSubmit);

/* Passphrase is in DB now. Try to authenticate us */
if (isset($_POST['conf']['general.passphrase'])) {
	MagnaConnector::gi()->updatePassPhrase();
	if (!loadMaranonCacheConfig(true)) {
		echo '<p class="errorBox">'.ML_ERROR_UNAUTHED.'</p>';
	} else {
		if (MagnaDB::gi()->recordExists(TABLE_CONFIGURATION, array (
			'configuration_key' => 'MAGNALISTER_PASSPHRASE'
		))) {
			MagnaDB::gi()->update(TABLE_CONFIGURATION, array (
				'configuration_value' => $_POST['conf']['general.passphrase']
			), array (
				'configuration_key' => 'MAGNALISTER_PASSPHRASE'
			));
		} else {
			MagnaDB::gi()->insert(TABLE_CONFIGURATION, array (
				'configuration_value' => $_POST['conf']['general.passphrase'],
				'configuration_key' => 'MAGNALISTER_PASSPHRASE'
			));
		}
	}
}

if (MAGNA_SAFE_MODE && isset($_POST['conf'])) {
	$result = verifyFTPLogin();
	if ((substr($result, 0, 1) == '/') && (substr($result, -1) == '/')) {
		/* A path */
		setDBConfigValue('general.ftp.path', '0', $result, true);
		echo '<p class="successBox">'.ML_TEXT_FTP_CORRECT.'</p>';
	} else {
		/* Error message */
		removeDBConfigValue('general.ftp.path', '0');
		echo '<p class="errorBox">'.$result.'</p>';
	}
}

$passPhrase = getDBConfigValue('general.passphrase', '0');

if (empty($passPhrase) || isset($_GET['welcome'])) {
	$form = array(
		'general' => $form['general']
	);
	$partner = trim((string)@file_get_contents('magnabundle.dat'));
	if (!empty($partner) && ($partner != 'key')) {
		$partner = 'partner='.$partner;
	} else {
		$partner = '';
	}

	unset($form['general']['headline']);
	/* Hier die bunte Startseite */
	echo '
		<p class="noticeBox bottomSpace">'.sprintf(ML_NOTICE_PLACE_PASSPHRASE, $partner).'</p>
		<div style="padding-bottom: 1em"></div>';
	$comercialText = '
		<div id="pageContent">'.fileGetContents(MAGNA_SERVICE_URL.MAGNA_APIRELATED.'promotion/?shopsystem='.SHOPSYSTEM, $warnings, 10).'</div>';	
	$comercialText = str_replace(
		array('##_PARTNER_##', ),
		array($partner,        ),
		$comercialText
	);
	MagnaDB::gi()->delete(TABLE_CONFIGURATION, array (
		'configuration_key' => 'MAGNALISTER_PASSPHRASE'
	));
} else {
	$cG->setRequiredConfigKeys($requiredConfigKeys);
}

global $forceConfigView;
if (($forceConfigView !== false) && !isset($comercialText)) {
	echo $forceConfigView;
	$q = MagnaDB::gi()->query('
		SELECT products_model, COUNT(products_model) as cnt
		  FROM '.TABLE_PRODUCTS.' 
		 WHERE products_model <> \'\'
      GROUP BY products_model
        HAVING cnt > 1'
	);
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[] = MagnaDB::gi()->escape($row['products_model']);
	}
	$evilProducts = MagnaDB::gi()->fetchArray('
		SELECT p.products_id, p.products_model, pd.products_name
		  FROM '.TABLE_PRODUCTS.' p
	 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON p.products_id=pd.products_id AND pd.language_id = \''.$_SESSION['languages_id'].'\'
		 WHERE products_model=\'\' OR products_model IS NULL '.((!empty($dblProdModel))
		 	? 'OR products_model IN (\''.implode('\', \'', $dblProdModel).'\')'
		 	: ''
		 ).'
      ORDER BY p.products_model ASC, pd.products_name ASC
	');
	if (!empty($evilProducts)) {
		$traitorTable = '
		    <table class="datagrid">
		    	<thead><tr>
		    		<th>'.str_replace(' ', '&nbsp;', ML_LABEL_PRODUCT_ID).'</th>
		    		<th>'.ML_LABEL_ARTICLE_NUMBER.'</th>
		    		<th>'.ML_LABEL_PRODUCTS_WITH_INVALID_MODELNR.'</th>
		    		<th>'.ML_LABEL_EDIT.'</th>
		    	</tr></thead>
		    	<tbody>';
		    $oddEven = true;
			foreach ($evilProducts as $item) {
				$traitorTable .= '
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<td style="width: 1px;">'.$item['products_id'].'</td>
						<td style="width: 1px;">'.(empty($item['products_model']) ? '<i class="grey">'.ML_LABEL_NOT_SET.'</i>' : $item['products_model']).'</td>
						<td>'.(empty($item['products_name']) ? '<i class="grey">'.ML_LABEL_UNKNOWN.'</i>' : $item['products_name']).'</td>
						<td class="textcenter" style="width: 1px;">
							<a class="gfxbutton edit" title="'.ML_LABEL_EDIT.'" target="_blank" href="categories.php?pID='.$item['products_id'].'&action=new_product">&nbsp;</a>
						</td>
					</tr>';
			}
		$traitorTable .= '
				</tbody>
			</table>';
		echo $traitorTable;
	}
}

echo $cG->renderConfigForm();
?>
<style>
body.magna div#content .button {
/*
	background: linear-gradient(center top, rgba(255,255,255, 0.8) 0%, rgba(255,255,255,0) 50%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.4) 100%), linear-gradient(left, red, orange, yellow, green, blue, indigo, violet);
	background: -moz-linear-gradient(center top, rgba(255,255,255, 0.8) 0%, rgba(255,255,255,0) 50%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.4) 100%), -moz-linear-gradient(left, red, orange, yellow, green, blue, indigo, violet);
	background: 
	-webkit-gradient(linear, left top, left bottom, 
		color-stop(0.00, rgba(255,255,255, 0.8)), 
		color-stop(0.49, rgba(255,255,255, 0)), 
		color-stop(0.51, rgba(0,0,0, 0)), 
		color-stop(1.00, rgba(0,0,0,0.4))
	), -webkit-gradient(linear, left top, right top, 
		color-stop(0.00, red), 
		color-stop(16%, orange),
		color-stop(32%, yellow),
		color-stop(48%, green),
		color-stop(60%, blue),
		color-stop(76%, indigo),
		color-stop(1.00, violet)
	);
	text-shadow: 0px 0px 2px rgba(255,255,255, 1);
	background-position: 0px 0px;
*/
}
</style>
<?php
if (isset($comercialText)) echo $comercialText;

if (isset($_POST['conf']['general.callback.importorders'])) {
	$hours = array();
	foreach ($_POST['conf']['general.callback.importorders'] as $hour => $selected) {
		if (!ctype_digit($hour) && !is_int($hour)) {
			continue;
		}
		$hours[(int)$hour] = $selected == 'true';
	}
	$request = array (
		'ACTION' => 'SetCallbackTimers',
		'SUBSYSTEM' => 'Core',
		'DATA' => array (
			'Command' => 'ImportOrders',
			'Hours' => $hours
		),
	);
	try {
		MagnaConnector::gi()->submitRequest($request);
	} catch (MagnaException $e) {}
}

if (isset($_GET['SKU'])) {
	$pID = magnaSKU2pID($_GET['SKU']);
	if ($pID > 0) {
		$pIDh = '<pre>magnaSKU2pID('.$_GET['SKU'].') :: <a style="font:12px monospace;" href="categories.php?pID='.$pID.'&action=new_product">'.var_dump_pre($pID, true).'</a></pre>';
	} else {
		$pIDh = var_dump_pre(magnaSKU2pID($_GET['SKU']), 'magnaSKU2pID('.$_GET['SKU'].')');
	}
	$aID = magnaSKU2aID($_GET['SKU']);
	if ($aID > 0) {
		$aIDh = '<form action="new_attributes.php" method="post">
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="current_product_id" value="'.$pID.'">
			<pre>magnaSKU2aID('.$_GET['SKU'].') ::<input style="background:transparent;border:none;font:12px monospace;" type="submit" value="'.var_dump_pre($aID, true).'"></pre></form>';
	} else {
		$aIDh = var_dump_pre(magnaSKU2aID($_GET['SKU']), 'magnaSKU2aID('.$_GET['SKU'].')');
	}
	echo $pIDh;
	echo $aIDh;
}

echo '<div id="switchSKU" class="dialog2" title="'.ML_TEXT_CONFIRM_SKU_CHANGE_TITLE.'">'.ML_TEXT_CONFIRM_SKU_CHANGE_TEXT.'</div>';
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
    $('input[name="conf[general.keytype]"]').change(function (e) {
        $('#switchSKU').dialog({
            modal: true,
            width: '600px',
            buttons: {
                "<?php echo ML_BUTTON_LABEL_ABORT; ?>": function() {
                    if ($('input[name="conf[general.keytype]"]')[1].checked) {
                        $('input[name="conf[general.keytype]"]')[0].checked = true;
                    } else {
                        $('input[name="conf[general.keytype]"]')[1].checked = true;                
                    }
                    $(this).dialog("close");
                },
                "<?php echo ML_BUTTON_LABEL_OK; ?>": function() { 
                    $(this).dialog("close");    
                }
            }
        });
    });
});
/*]]>*/</script>
<?php

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
