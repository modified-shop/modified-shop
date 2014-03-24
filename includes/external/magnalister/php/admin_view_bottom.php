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
 * $Id: admin_view_bottom.php 3304 2013-11-24 17:27:37Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
global $_additionalDivs, $_updaterTime, $_executionTime;

if (!isset($_MagnaSession)) {
	global $_MagnaSession, $_MagnaShopSession, $_magnaQuery, $magnaConfig;
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
?>
								</div>
								<table id="magnafooter" class="magnaframe small center"><tbody><tr><td>
<?php
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
									<div class="bold">
										<span class="customerinfo">'.ML_LABEL_CUSTOMERSID.': '.((isset($magnaConfig['maranon']['CustomerID'])) ? $magnaConfig['maranon']['CustomerID'] : ML_LABEL_UNKNOWN).' :: Shop ID: '.((isset($magnaConfig['maranon']['ShopID'])) ? $magnaConfig['maranon']['ShopID'] : ML_LABEL_UNKNOWN).'</span>
										<span class="magnatext">m</span>agnalister Version <span class="version">'.LOCAL_CLIENT_VERSION.'
										<span class="build">Build: '.((defined('CLIENT_BUILD_VERSION')) ? CLIENT_BUILD_VERSION : ML_LABEL_UNKNOWN).' :: Current: '.((defined('CURRENT_BUILD_VERSION')) ? CURRENT_BUILD_VERSION : ML_LABEL_UNKNOWN).'</span>
									</span></div>
									<div class="copyleft">'.ML_LABEL_COPYLEFT.'</div>';
?>
								</td></tr></tbody></table>
<?php
if (MAGNA_DEBUG && class_exists('MagnaConnector')) {
	$tpR = MagnaConnector::gi()->getTimePerRequest();
	if (!empty($tpR)) {
		echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
		foreach ($tpR as $item) {
			echo print_m(json_indent($item['request']), microtime2human($item['time']).' ['.$item['status'].']', true)."\n";
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
		<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
		<!-- footer_eof //-->
		<script type="text/javascript">
			var magnaErrors = <?php echo MagnaError::gi()->exceptionsToHTML(); ?>;
			$('#magnaErrors div').append(magnaErrors);
			if (magnaErrors.length >= 1) {
				$('#magnaErrors').css({'display':'block'});
			}
		</script>
	</body>
</html>