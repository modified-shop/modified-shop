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
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/* {Hook} "ExtendAdminViewTopPHP": Enables you to extend the admin_view_top.php.<br>
 */
if (($hp = magnaContribVerify('ExtendAdminViewTopPHP', 1)) !== false) {
	require($hp);
}

function renderTabs($pages, $kind, $selected, $baseURL) {
	$html = '
		<div class="magnaTabs2">
			<ul>';
	if (empty($selected)) {
		reset($pages);
		$selected = key($pages);
	}
	foreach ($pages as $url => $page) {	
		if (is_array($page)) {
			$title = $page['title'];
		} else {
			$title = $page;
		}
		$html .= '
				<li'.(($url == $selected) ? ' class="selected"' : '').'>
					<a href="'.toURL($baseURL, array($kind => $url, 'isTab' => 'true')).'" title="'.$title.'">'.$title.'</a>
		 		</li>';
	}
	$html .= '
			</ul>
		</div>';
	return $html;
}

global $magnaConfig, $_executionTime, $_js, $_mainTitle, $_url,
	   $_MagnaSession, $_MagnaShopSession, $_magnaQuery, $_modules, 
	   $_updatedSuccessfully, $_pageCSS;

if (in_array(SHOPSYSTEM, array('oscommerce', 'xonsoft'))) {
	global $PHP_SELF;
	if (!isset($cfgModules)) {
		global $cfgModules;
	}
}

if (SHOPSYSTEM == 'xtcmodified') {
        global $menu_access, $PHP_SELF;
}

if (array_key_exists('module', $_GET) && array_key_exists($_GET['module'], $_modules)) {
	$_mainTitle .= ' - '.$_modules[$_GET['module']]['title'];
}

$renderengine = MLBrowserDetect::gi()->get('Engine');
$os = MLBrowserDetect::gi()->get('Platform');
if ($os === 'win') {
	$os = 'windows';
}
if (MLBrowserDetect::gi()->compare('Browser', 'msie', '==')) {
	$msieVersion = floatval(MLBrowserDetect::gi()->get('BVersion'));
	if ($msieVersion < 10) {
		$renderengine = 'ielt10';
	} else if ($msieVersion < 9) {
		$renderengine = 'ielt9';
	} else if ($msieVersion < 8) {
		$renderengine = 'ielt8';
	}
}

if (!isset($_SESSION['language_charset'])) {
	if (defined('CHARSET')) {
		$_SESSION['language_charset'] = CHARSET;
	} else {
		$_SESSION['language_charset'] = '';
	}
}

if ((SHOPSYSTEM == 'xtcmodified') && defined('RUN_MODE_ADMIN') && file_exists(DIR_WS_INCLUDES.'head.php')) {
	require (DIR_WS_INCLUDES.'head.php');
} else {
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">
<html '.HTML_PARAMS.'>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset='.$_SESSION['language_charset'].'">
		<title>'.(defined('TITLE') ? TITLE.' :: ' : '').'magnalister'.$_mainTitle.'</title>
		'.((file_exists(DIR_WS_INCLUDES.'stylesheet.css')) ? '<link rel="stylesheet" type="text/css" href="includes/stylesheet.css" />' : '').''."\n";
}
/* Force IE into Standards Mode */
if (MLBrowserDetect::gi()->compare('Browser', 'msie', '==')) {
	echo '		<meta http-equiv="x-ua-compatible" content="IE='.((int)MLBrowserDetect::gi()->get('BVersion')).'">'."\n";
}
if (!isset($_GET['module']) || ($_GET['module'] != 'nojs')) {
	echo '		<noscript><meta http-equiv="refresh" content="0;URL='.toURL(array('module' => 'nojs')).'"></noscript>'."\n";
}
?>
		<link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/jqueryui/jquery-ui-1.9.1.custom.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/magnalister.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/select2.min.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/fix-select2.css?<?php echo CLIENT_BUILD_VERSION?>" />
<?php
    if (SHOPSYSTEM == 'commerceseo') {
        echo '<link rel="stylesheet" type="text/css" href="'.DIR_MAGNALISTER_WS.'css/commerceSEOV3.css?'.CLIENT_BUILD_VERSION.'" />';
    }
			if (isset($_pageCSS) && ($_pageCSS = trim($_pageCSS)) && !empty($_pageCSS)) {
				echo '
		<style type="text/css">
'.$_pageCSS.'
		</style>'."\n";
			}
?>
		<style>
		@-moz-keyframes ml-css-spin {
			0% {-moz-transform: rotate(0deg);}
			100% {-moz-transform: rotate(360deg);}
		}
		@-webkit-keyframes ml-css-spin {
			0% {-webkit-transform: rotate(0deg);}
			100% {-webkit-transform: rotate(360deg);}
		}
		@keyframes ml-css-spin {
			0% {transform: rotate(0deg);}
			100% {transform: rotate(360deg);}
		}

		.ml-css-loading {
			-o-box-sizing: border-box;
			-ie-box-sizing: border-box;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			-moz-animation: ml-css-spin .8s infinite linear;
			-webkit-animation: ml-css-spin .8s infinite linear;
			animation: ml-css-spin .8s infinite linear;
		}
		</style>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/debugFunctions.js"></script>
		<script type="text/javascript">/*<![CDATA[*/
			var debugging = true;/*<?php echo (MAGNA_DEBUG) ? 'true' : 'false'; ?>;*/
			if ((debugging === true) && window.console) {
				var myConsole = console;
			} else {
				var myConsole = {
					log: function(){},
					debug: function(){},
					info: function(){},
					warn: function(){},
					error: function(){},
					assert: function(){},
					dir: function(){},
					dirxml: function(){},
					trace: function(){},
					table: function(){},
					group: function(){},
					groupEnd: function(){},
					time: function(){},
					timeEnd: function(){},
					profile: function(){},
					profileEnd: function(){},
					count: function(){},
					table: function(){}
				}
			}
			
			var blockUICSS = {
				'border': 'none',
				'padding': '15px',
				'background-color': '#fff',
				'border-radius': '10px',
				'-moz-border-radius': '10px',
				'-webkit-border-radius': '10px',
				'opacity': '0.8',
				'color': '#000',
				'font-size': '15px',
				'font-weight': 'bold'
			};
			var blockUIMessage = '<span><?php echo ML_TEXT_PLEASE_WAIT; ?></span>';
			
			var blockUILoading = {
				overlayCSS: { 
					backgroundColor: '#fff',
					'opacity': '0.8',
					'z-index': '9000'
				},
				css: {
					'width': '32px',
					'height': '32px',
					'border-width': '4px',
					'border-style': 'solid',
					'border-color': 'rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 0.25) rgba(199, 53, 47, 1)',
					'border-radius': '32px',
					'padding': '0',
					'left': '50%',
					'margin': '0 0 0 -16px',
					'padding': '0',
					'top': '300px',
					'z-index': '9999',
					'background': 'transparent'
				},
				blockMsgClass: 'ml-css-loading',
				message: '<div></div>',
				onBlock: function() {
						jQuery('.blockUI.ml-css-loading.blockPage').bind('dblclick', function() {
								jQuery.unblockUI();
						});
				}
			};
			var blockUIProgress = {
				overlayCSS: { 
					'background': '#000',
					'opacity': '0.1',
					'z-index': '9000'
				},
				css: {
					'background': '#fff',
					'width': '200px',
					'margin-left': '-100px',
					'height': '16px',
					'left': '50%',
					'padding': '10px',
					'border': 'none',
					'border-radius': '10px',
					'box-shadow': '0 0 20px #000000',
					'z-index': '9001',
					'box-sizing': 'content-box'
				},
				message: '<div class="progressBarContainer"><div class="progressBar"></div><div class="progressPercent">0%</div></div>'
			};
			
			/* Preload Loading Animation */
			progressbarImage = new Image(); 
			progressbarImage.src = "<?php echo DIR_MAGNALISTER_WS; ?>images/progressbar.png";
		/*]]>*/</script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery-1.8.3.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery.timers-1.2.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery-ui-1.9.1.custom.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery-ui-i18n.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery.ba-throttle-debounce.js"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/select2/select2.min.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/select2/i18n/<?php echo $_SESSION['language_code'] ?>.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/magnalister_general.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/classes/JSClass.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/loading-timer.js"></script>

<?php if (defined('MERCARI_INSTALLED')) { 
		global $tage, $monate;
?>
		<link rel="stylesheet" type="text/css" href="includes/javascript/menu/menu.css" charset="utf-8" />
		<script src="includes/javascript/menu/jquery.menu.js" type="text/javascript"></script>
<?php } ?>
<?php if (file_exists('includes/javascript/cluetip/jquery.cluetip.css') && file_exists('includes/javascript/cluetip/jquery.cluetip.js')) { ?>
		<link rel="stylesheet" type="text/css" href="includes/javascript/cluetip/jquery.cluetip.css" charset="utf-8" />
		<script src="includes/javascript/cluetip/jquery.cluetip.js" type="text/javascript"></script>
<?php } ?>
<?php
		if (!empty($_js)) {
			foreach ($_js as $js) {
echo '		<script type="text/javascript" src="'.$js.'"></script>'."\n";
			}
		}
?>
		<script type="text/javascript">/*<![CDATA[*/
			(function(jQuery) {
				jQuery.fn.jDialog = function(parameters, okFunction) {
					if (okFunction == undefined) {
						okFunction = function() {
						};
					}
					if (parameters == undefined) {
						parameters = {};
					}
					parameters = jQuery.extend({
						modal: true,
						width: 500,
						minHeight: 100,
						buttons: {
							OK: function() {
								jQuery(this).dialog('close');
								okFunction();
							}
						}
					}, parameters);
					jQuery(this).dialog(parameters);
				}
			})(jQuery);

			jQuery(window).load(function() {
				jQuery("body").everyTime('960s', 'keepAlive', function(i) {
					jQuery.get(
						"<?php echo FILENAME_MAGNALISTER; ?>", {
							'module':'ajax',
							'request':'keepAlive'
						},
						function(data) {
							//myConsole.log(data);
						}
					);
				});
			});

			jQuery(document).ready(function() {
				var bgC = jQuery('#content').css('background-color');
				if (bgC.length > 1) {
					jQuery('td.boxCenter').css({'background-color': bgC});
				}
				$('#globalButtonBox span').on('click', (function() {
					// prevent multiple clicks on the button.
					var executed = false;
					return function (e) {
                        if ($(e.target).data('disabled') == 'yes' && $(e.target).hasClass('update')) {
                            $('<div title="<?php echo ML_MESSAGE_BEFORE_UPDATE_TITLE; ?>"><?php echo ML_MESSAGE_UPDATE_DISABLED_GAMBIOCLOUD; ?></div>').jDialog();
                            return;
                        }
                        if ($(e.target).data('disabled') == 'yes' && !$(e.target).hasClass('update')) {
							$('<div title="<?php echo ML_LABEL_NOTE; ?>"><?php echo ML_POPUP_NOT_AVAILABLE; ?></div>').jDialog();
							return;
						}
						if (executed) {
							e.preventDefault();
							return;
						}
						executed = true;
						var popupTitle = '';
						var popupText  = '';
						if ($(e.target).data('href').search('update') > 0) {
							popupTitle = '<?php echo ML_MESSAGE_BEFORE_UPDATE_TITLE ?>';
							popupText  = '<?php echo ML_MESSAGE_BEFORE_UPDATE_TEXT ?>';
							
						} else if($(e.target).data('href').search('ImportOrders') > 0) {
							popupTitle = '<?php echo ML_MESSAGE_BEFORE_IMPORT_ORDERS_TITLE ?>';
							popupText  = '<?php echo ML_MESSAGE_BEFORE_IMPORT_ORDERS_TEXT ?>';
						} else if($(e.target).data('href').search('SyncOrderStatus') > 0) {
							popupTitle = '<?php echo ML_MESSAGE_BEFORE_SYNC_ORDERSTATUS_TITLE ?>';
							popupText  = '<?php echo ML_MESSAGE_BEFORE_SYNC_ORDERSTATUS_TEXT ?>';
						} else if($(e.target).data('href').search('SyncInventory') > 0) {
							popupTitle = '<?php echo ML_MESSAGE_BEFORE_SYNC_INVENTORY_TITLE ?>';
							popupText  = '<?php echo ML_MESSAGE_BEFORE_SYNC_INVENTORY_TEXT ?>';
						} else if($(e.target).data('href').search('SyncEbayListingDetails') > 0) {
							popupTitle = '<?php echo ML_MESSAGE_BEFORE_SYNC_EBAY_DETAILS_TITLE ?>';
							popupText  = '<?php echo ML_MESSAGE_BEFORE_SYNC_EBAY_DETAILS_TEXT ?>';
						} else {
						// Fallback for whichever new button: "Note: Synchronizing data with the magnalister Server"
							popupTitle = '<?php echo ML_LABEL_NOTE ?>';
							popupText  = '<?php echo ML_STATUS_FILTER_SYNC_ITEM ?>';
						}
						$('<div></div>').html(popupText).jDialog({
							title: popupTitle,
							buttons: {
								'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
									window.location.href = '<?php $sUrl1 = strpos($_SERVER['REQUEST_URI'], 'update=true') ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'update=true') -1) : $_SERVER['REQUEST_URI']; echo strpos($sUrl1, 'do=') ? substr($sUrl1, 0, strpos($sUrl1, 'do=') -1) : $sUrl1 ?>';
									jQuery(this).dialog('close');
								},
								'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
									$.blockUI(blockUILoading);
									window.location.href = $(e.target).data('href');
									jQuery(this).dialog('close');
								}
							}
						});
						//} else {
						//	$.blockUI(blockUILoading);
						//	window.location.href = $(e.target).data('href');
						//}
					}
				})());
				/*.on('dblclick', function (e) {
					e.preventDefault();
					$('<div title="Duh...">Wirklich? Ein Doppelklick?</div>').jDialog({
						buttons: {
							Nein: function() {
								jQuery(this).dialog('close');
							},
							Ja: function() {
								$('<div title="Internetverbot">Falsche Antwort. Zur Strafe gibt es einen Tag Internetverbot!</div>').jDialog();
								jQuery(this).dialog('close');
							}
						}
					});
				});*/
			});
			try {
				$.cookie('device_pixel_ratio', window.devicePixelRatio, { expires: 7, path: '/' });
			} catch (e) {
				myConsole.log(e);
			}
			console.log('$_COOKIE[device_pixel_ratio]:', <?php echo json_encode(isset($_COOKIE['device_pixel_ratio']) ? $_COOKIE['device_pixel_ratio'] : null); ?>);
			console.log('retina enabled:', <?php echo json_encode(ML_RETINA_DISPLY); ?>);
		/*]]>*/</script>
		<!--[if lt IE 9]><script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('div.magnamain').each(function() {
					$(this).css({height: this.scrollHeight < 181 ? "180px" : "auto"});
				});
			});
		/*]]>*/</script><![endif]-->
	</head>
	<body class="magna <?php echo SHOPSYSTEM.(isset($renderengine) ? ' '.$renderengine : '').' '.$os; ?> jqueryui">
		<!-- header //-->
		<?php 
		/* Wenn es ein gambio oder xtcModified shop ist, sollten wir die alte Version von jquery und jqueryui loswerden. */
		$hasHeadNav = (strpos(file_get_contents(DIR_WS_INCLUDES . 'header.php'), 'magnalister') !== false) || defined('MERCARI_INSTALLED');
		ob_start();
		if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) & ~E_NOTICE & ~E_STRICT);
		$current_page = basename($_SERVER["PHP_SELF"]);
		if (!defined('ML_GAMBIO_USE_IFRAME') || ML_GAMBIO_USE_IFRAME !== true) {
			require(DIR_WS_INCLUDES . 'header.php');
		}
		if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE | E_STRICT);
		$out = ob_get_contents();
		ob_end_clean();
		echo preg_replace('/(<script (type="text\/javascript")*.*jquery.[^tooltip|^colorbox].*(type="text\/javascript")* *><\/script>)/', '', $out);

		/* Prepare Navigation */
		$nav = '';
		$tclass = '';
		if (!$hasHeadNav && file_exists(DIR_WS_INCLUDES . 'column_left.php') && (!defined('ML_GAMBIO_USE_IFRAME') || ML_GAMBIO_USE_IFRAME !== true)) {
			$tnav = file_get_contents(DIR_WS_INCLUDES . 'column_left.php');
			if (in_array(SHOPSYSTEM, array('oscommerce', 'xonsoft'))) {
				if (strpos($tnav, '$(\'#adminAppMenu\').accordion({') === false) {
					$tclass = 'columnLeft';
				} else {
					$tclass = 'columnLeftOSC2010';
				}
			}
			ob_start();
			if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL)  & ~E_NOTICE & ~E_STRICT);
			require(DIR_WS_INCLUDES . 'column_left.php');
			if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE | E_STRICT);
			$nav = ob_get_contents();
			ob_end_clean();
			unset($tnav);
		}
		
		?>
		<!-- header_eof //-->
		<!-- body //-->
		<table border="0" width="100%" cellspacing="2" cellpadding="2"><tbody>
			<tr><?php if (!empty($nav)) { ?>
				<td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
					<table class="<?php echo $tclass; ?>" border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="0"><tbody><tr><td>
						<!-- left_navigation //-->
						<?php echo $nav; unset($nav); ?>
						<!-- left_navigation_eof //-->
					</td></tr></tbody></table>
				</td>
				<?php } /* if (!empty($nav)) */ ?>
				<!-- body_text //-->
				<td class="boxCenter" width="100%" valign="top" align="left">
<?php
if (version_compare(CURRENT_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>') && !version_compare(MINIMUM_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>')) {
	if (!MAGNA_SAFE_MODE) {
		preg_match('/#([^#]*)#/', ML_TEXT_NEW_VERSION, $matches);
		echo '
			<p class="successBox">
				'.sprintf(str_replace(
					$matches[0],
					'<a href="'.toUrl(array('update' => 'true')).'" title="Update">'.$matches[1].'</a>',
					ML_TEXT_NEW_VERSION
				), CURRENT_CLIENT_VERSION).'
			</p>';
	} else {
		echo '
			<p class="successBox">
				'.sprintf(ML_TEXT_NEW_VERSION_SAFE_MODE, CURRENT_CLIENT_VERSION).'
			</p>';
	}
	
}
if (MAGNA_SAFE_MODE && isset($_GET['update']) && ($_GET['update'] == 'true') && !$_updatedSuccessfully) {
	echo '
		<p class="noticeBox">'.str_replace(
			'#LINK_GLOBAL_CONF#', 
			'<a href="'.toURL(array('module' => 'configuration')).'">'.ML_MODULE_GLOBAL_CONFIG.'</a>',
			ML_TEXT_GENERIC_SAFE_MODE
		).'</p>';
}
if ($_updatedSuccessfully) {
	echo '<p class="successBox">'.ML_TEXT_UPDATE_SUCCESS.'</p>';
	if (MAGNA_SHOP_CHANGES) {
		echo '
			<div id="infoshopchangesdiag" class="dialog2" title="'.ML_LABEL_ATTENTION.'">'.ML_TEXT_UPDATE_SHOP_CHANGES.'</div>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#infoshopchangesdiag\').jDialog();
				});
			/*]]>*/</script>
		';
	}
}
if (LOCAL_CLIENT_VERSION == '0') {
	echo '<p class="errorBox">'.ML_ERROR_COULD_NOT_LOAD_LOCAL_CLIENTVERSION.'</p>';	
}
$globalButtons = array (
	array (
		'title' => ML_LABEL_IMPORT_ORDERS,
		'icon' => 'cart',
		'link' => array('do' => 'ImportOrders'),
        'disabled' => $_MagnaSession['currentPlatform'] == 'googleshopping' ? 'yes' : 'no',
	),
	array (
		'title' => ML_LABEL_SYNC_ORDERSTATUS,
		'icon' => 'upload',
		'link' => array('do' => 'SyncOrderStatus'),
	),
	array (
		'title' => ML_LABEL_SYNC_INVENTORY,
		'icon' => 'sync',
		'link' => array('do' => 'SyncInventory', 'MLDEBUG' => 'true'),
	),
    array (
        'title' => ML_LABEL_UPDATE,
        'icon' => 'update'.((defined('SHOPSYSTEM_GAMBIO_CLOUD') && SHOPSYSTEM_GAMBIO_CLOUD === true) ? ' inactive' : ''), // hier war ' '
        'link' => array('update' => 'true'),
        'disabled' => (defined('SHOPSYSTEM_GAMBIO_CLOUD') && SHOPSYSTEM_GAMBIO_CLOUD === true) ? 'yes' : 'no',
    ),
);

if ($_MagnaSession['currentPlatform'] == 'ebay') {
	$bSyncEnabled = (getDBConfigValue('ebay.listingdetails.sync', $_MagnaSession['mpID'], 'false') == 'true');
	$globalButtons = array_merge(array(array(
		'title' => ML_LABEL_SYNC_EBAY_LISTING_DETAILS,
		'icon' => 'sync'.($bSyncEnabled ? '' : ' inactive'),
		'link' => array('do' => 'SyncEbayListingDetails', 'MLDEBUG' => 'true'),
		'disabled' => ($bSyncEnabled) ? 'no' : 'yes',
	)), $globalButtons);
}

if (getDBConfigValue(array('general.maintenance', 'val'), 0, 'false') == 'true') {
        foreach ($globalButtons as $bNo => &$button) {
            if ('update' == $button['icon']) continue;
            $globalButtons[$bNo]['icon'] .= ' inactive';
            $globalButtons[$bNo]['disabled'] = 'yes';
        }
}

?>
					<table border="0" width="100%" cellspacing="0" cellpadding="2" style="<?php echo ((defined('ML_GAMBIO_USE_IFRAME') && ML_GAMBIO_USE_IFRAME === true)) ? '' : 'padding: 0 10px;'?>"><tbody>
						<tr>
							<td width="100%">
								<h1 id="magnalogo"><a href="<?php echo toURL(); ?>" title="<?php echo ML_HEADLINE_MAIN; ?>">
									<img src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>magnalister_logo.png" alt="<?php echo ML_HEADLINE_MAIN; ?>" width="165" height="42"/>
								</a></h1>
								<?php if (isset($_SESSION['magna_UPDATE_PATH']) && (strpos($_SESSION['magna_UPDATE_PATH'], 'debug') !== false)) { ?>
									<span style="display: inline-block; padding-left: 3px; padding-top: 36px;"> :: Debug &#4314;(&#3232;&#30410;&#3232;&#4314;</span>
								<?php } ?>
								<div id="globalButtonBox"><?php
									foreach ($globalButtons as $blargh) {
										echo '<span class="gfxbutton border '.$blargh['icon'].'" data-disabled="'.(isset($blargh['disabled']) ? $blargh['disabled'] : 'no').'" data-href="'.toURL($_url, $blargh['link']).'" title="'.$blargh['title'].'"></span> ';
									}
								?></div>
								<div class="visualClear">&nbsp;</div>
								<div id="magnaErrors"><p class="errorBox"><?php echo ML_ERROR_API; ?></p><div></div></div>
<?php
$structure = magnaGenerateNavStructure();

echo '
	<div class="magnaTabs2">
		<ul>';
foreach ($structure as $item) {	
	if (empty($item['label'])) {
		$labelhtml = '';
		$labelattr = $item['title'];
	} else {
		$labelhtml = str_replace(
			array('&lt;', '&gt;', '&quot;'),
			array('<', '>', '"'),
			fixHTMLUTF8Entities($item['label'])
		);
		$labelattr = $item['title'].' :: '.str_replace(
			array('<', '>', '"'),
			array('&lt;', '&gt;', '&quot;'),
			strip_tags($labelhtml)
		);
		$labelhtml = ' &nbsp;'.$labelhtml;
	}
	if ($item['title'] == ML_LABEL_MORE_MODULES) {
		$item['title'] = '&hellip;';
	}
	echo '
			<li class="'.$item['class'].'">
				<a href="'.$item['url'].'" title="'.$labelattr.'">'.(
					!empty($item['image'])
						? ('<img src="'.$item['image'].'" alt="'.$labelattr.'"/>')
						: $item['title']
				).$labelhtml.'</a>
			</li>';
}
echo '
		</ul>
	</div>';
echo "\n";
?>
								<div id="content" class="magnamain">
<?php
$_additionalDivs = 0;
$tmpMagnaQuery = $_magnaQuery;
if (array_key_exists($_MagnaSession['currentPlatform'], $_modules)) {
	$module = $_modules[$_MagnaSession['currentPlatform']];
	if (isset($_GET['mode']) && array_key_exists($_GET['mode'], $module['pages'])) {
		$tmpMagnaQuery['mode'] = $_GET['mode'];
	}
	if (!isset($tmpMagnaQuery['mode'])) {
		$tmpMagnaQuery['mode'] = '';
	}
	if (array_key_exists('pages', $module)) {
		echo renderTabs(
			$module['pages'],
			'mode',
			$tmpMagnaQuery['mode'],
			$_url
		);
	}
	echo '<div class="magnamain">';
	++$_additionalDivs;
	
	if (isset($module['pages'][$tmpMagnaQuery['mode']]) && is_array($module['pages'][$tmpMagnaQuery['mode']])) {
		echo renderTabs(
			$module['pages'][$tmpMagnaQuery['mode']]['views'],
			'view',
			$_magnaQuery['view'],
			array_merge($_url, array('mode' => $tmpMagnaQuery['mode']))
		);
		echo '<div class="magnamain">';
		++$_additionalDivs;
	}
}
