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
global $_additionalDivs, $_updaterTime, $_executionTime;

if (!isset($_MagnaSession)) {
	global $_MagnaSession, $_MagnaShopSession;
}
if (!isset($_magnaQuery)) {
	global $_magnaQuery;
}
if (!isset($magnaConfig)) {
	global $magnaConfig;
}

if (function_exists('magnaDumpSqlErrorlog')) {
	magnaDumpSqlErrorlog();
}

echo str_repeat('</div>', $_additionalDivs);

if (MAGNA_DEBUG && MAGNA_DEBUG_TF && !MLBrowserDetect::gi()->is(array ('Browser' => 'msie', 'BVersion' => '>= 6.0'))) {
	echo '<textarea id="debugBox" wrap="off" readonly="readonly" spellcheck="false">';
	echo '$_magnaQuery :: '.print_r($_magnaQuery, true)."\n";
	echo '$_MagnaShopSession :: '.print_r($_MagnaShopSession, true)."\n";
	echo '$_MagnaSession :: '.print_r($_MagnaSession, true)."\n";
	echo '$_GET :: '.print_r($_GET, true)."\n";
	echo '$_POST :: '.print_r($_POST, true)."\n";
	echo '$magnaConfig :: '.print_r($magnaConfig, true)."\n";
	echo '$_SESSION :: '.print_r($_SESSION, true);
	echo '</textarea>';
}

echo '
								</div>
								<div id="magnafooter">
									<table class="magnaframe small center"><tbody>
										<tr>
											<td rowspan="2" class="ml-td-left">';

if (class_exists('MagnaDB') && class_exists('MagnaConnector')) {
	$_executionTime = microtime(true) -  $_executionTime;
	$memory = memory_usage();
	echo (MAGNA_DEBUG ? '<div class="debug">' : '<!--').'
		Entire page served in <b>'.microtime2human($_executionTime).'.</b><br/><hr/>
		Updater Time: '.microtime2human($_updaterTime).'. <br/>
		API-Request Time: '.microtime2human(MagnaConnector::gi()->getRequestTime()).'. <br/>
		Processing Time: '.microtime2human($_executionTime - $_updaterTime - MagnaConnector::gi()->getRequestTime()).'. <br/><hr/>
		'.(($memory !== false) ? 'Max. Memory used: <b>'.$memory.'</b>. <br/><hr/>' : '').'
		DB-Stats: <br/>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Queries used: <b>'.MagnaDB::gi()->getQueryCount().'</b><br/>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Query time: '.microtime2human(MagnaDB::gi()->getRealQueryTime()).'
		'.(MAGNA_DEBUG ? '</div>' : '-->');
}
echo '
												<span class="customerinfo">
													'.ML_LABEL_CUSTOMERSID.': '.((isset($magnaConfig['maranon']['CustomerID'])) ? $magnaConfig['maranon']['CustomerID'] : ML_LABEL_UNKNOWN).' ::
													Shop ID: '.((isset($magnaConfig['maranon']['ShopID'])) ? $magnaConfig['maranon']['ShopID'] : ML_LABEL_UNKNOWN).'
												</span>
											</td>
											<td class="ml-td-center">
												<div class="bold">
													<span class="version-text">magnalister Version</span> <span class="version">'.LOCAL_CLIENT_VERSION.'</span>
												</div>
											</td>
											<td rowspan="2" class="ml-td-right">
												<span class="build">
													Build: '.((defined('CLIENT_BUILD_VERSION')) ? CLIENT_BUILD_VERSION : ML_LABEL_UNKNOWN).' ::
													<a href="'.toURL(array('module' => 'viewchangelog')).'" title="Changelog">Latest: '.((defined('CURRENT_BUILD_VERSION')) ? CURRENT_BUILD_VERSION : ML_LABEL_UNKNOWN).'</a>
												</span>
											</td>
										</tr>
										<tr>
											<td class="ml-td-center">
												<div class="copyleft">'.ML_LABEL_COPYLEFT.'</div>
											</td>
										</tr>
									</tbody></table>
								</div>';

if (MAGNA_DEBUG && class_exists('MagnaConnector')) {
	$tpR = MagnaConnector::gi()->getTimePerRequest();
	if (!empty($tpR)) {
		echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
		foreach ($tpR as $item) {
			echo print_m(json_indent($item['request']), microtime2human($item['time']).' ['.$item['status'].'] <-- '.(isset($item['apiurl']) ? $item['apiurl'] : 'url unknown').'', true)."\n";
		}
		echo '</textarea>';
	}
}
if (MAGNA_DEBUG && class_exists('MagnaDB')) {
	$tpR = MagnaDB::gi()->getTimePerQuery();
	if (!empty($tpR)) {
		echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
		foreach ($tpR as $item) {
			echo print_m(ltrim(rtrim($item['query'], "\n"), "\n"), microtime2human($item['time']), true)."\n";
		}
		echo '</textarea>';
	}
}
//echo print_m($_SESSION);
?>
							</td>
						</tr>
					</tbody></table>
				</td>
				<!-- body_text_eof //-->
			</tr>
		</tbody></table>
		<!-- body_eof //-->
		<!-- footer //-->
		<?php
		if (!defined('ML_GAMBIO_USE_IFRAME') || ML_GAMBIO_USE_IFRAME !== true) {
			require(DIR_WS_INCLUDES . 'footer.php');
		}
		?>
		<!-- footer_eof //-->
		<script type="text/javascript">
			var magnaErrors = '<?php echo '';//echo MagnaError::gi()->exceptionsToHTML(); ?>';
			$('#magnaErrors div').append(magnaErrors);
			if (magnaErrors.length >= 1) {
				$('#magnaErrors').css({'display':'block'});
			}
			<?php if (array_key_exists('CSRFName', $_SESSION) && array_key_exists('CSRFToken', $_SESSION)) { ?>
				(function($) {
					var oCsrfConfig = {"<?php echo $_SESSION['CSRFName']; ?>": "<?php echo  $_SESSION['CSRFToken']; ?>"};
					$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
						if(
							(typeof options.type === 'string' && options.type.toLowerCase() === 'post')
							||
							(typeof originalOptions.type === 'string' && originalOptions.type.toLowerCase() === 'post')
						) { // adding CSRF-token to each ajax-post-request
							if (typeof originalOptions.data == 'string') {//serialized, we add as string
								for (var s in oCsrfConfig) {
									options.data = options.data + '&' + s + '=' + oCsrfConfig[s];
								}
							} else {
								options.data = $.param($.extend(typeof originalOptions.data === 'object' ? originalOptions.data : {}, oCsrfConfig));
							}
						}
					});
					$(document).ready(function() { // adding CSRF-token to each post-form
						for (var sCsrfName in oCsrfConfig) {
							$('#content.magnamain form[method="post"]').prepend('<input type="hidden" name="'+sCsrfName+'" value="'+oCsrfConfig[sCsrfName]+'" >');
						}
					});
				})(jQuery);
			<?php } ?>
		</script>
        <?php
        global $_magnaLanguage;
        switch (strtolower($_magnaLanguage)) {
            case 'german': {
                // German
                $sUrl = 'https://embed.tawk.to/5b73efaaafc2c34e96e7976e/default';
                break;
            }
            case 'french': {
                // French
                $sUrl = 'https://embed.tawk.to/5b73f645f31d0f771d83cf15/default';
                break;
            }
            default: {
                // English
                $sUrl = 'https://embed.tawk.to/5b73f63aafc2c34e96e79794/default';
                break;
            }
        }
        ?>
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                s1.src='<?php echo $sUrl; ?>';
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
	</body>
</html>
