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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
/* @var $this MLOrderlistAmazonAbstract */
/* @var $oObject MLOrderlistAmazonAbstractDependencyAction */
class_exists('MLOrderlistAmazonAbstract') or die();
?>
<a id="ml-confirm-shipping" title="<?php echo ML_AMAZON_SHIPPINGLABEL_CONFIRM; ?>"  href="<?php echo $this->getUrl(false, false, false, array('view' => 'upload', 'subview'=>'form')); ?>" class="ml-js-noBlockUi ml-button mlbtn-action"><?php echo ML_AMAZON_SHIPPINGLABEL_CONFIRM; ?></a> 
   <?php ob_start();?>
<div id="checkinSubmit">
	<h1 id="threeDots">
		<span id="headline"><?php echo ML_HEADLINE_SUBMIT_PRODUCTS ?></span><span class="alldots">
		    <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>&nbsp;
		</span>
	</h1>
	<!--<hr/>
	<p><?php echo ML_NOTICE_SUBMIT_PRODUCTS ?></p>-->
	<div id="ml-html-content"></div>
	<div id="apiException" style="display:none;"><p class="errorBox"><?php echo ML_ERROR_SUBMIT_PRODUCTS ?></p></div>
	<div id="uploadprogress" class="progressBarContainer">
		<div class="progressBar"></div>
		<div class="progressPercent"></div>
	</div>
	<br>
	<div id="checkinSubmitStatus" class="paddingBottom"></div>

</div>
<?php 
$popup = ob_get_clean();
$popup = str_replace(array("\n","\r"), "", $popup);
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#ml-confirm-shipping').click(function(e){
		e.preventDefault();
		$('<?php echo $popup ?>').jDialog({
			title: '<?php echo ML_AMAZON_SHIPPINGLABEL_CONFIRM; ?>',
			buttons: [
			{
				text: <?php echo json_encode(ML_BUTTON_LABEL_CLOSE ) ?>,
				click: function() {
					window.location.href = '<?php echo $this->getUrl(false, false, false, array('view' => 'overview'),true); ?>';
				}
			},
			{
				text: "Download",
				click: function() {
					if($('#downloadshippinglabel').length) {
						$('#downloadshippinglabel')[0].click();
					}
				}
			}
		]
		});
		runConfirmShippingAjax();
	});
	
});
/*]]>*/</script>	

<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/classes/CheckinSubmit.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
<script type="text/javascript">/*<![CDATA[*/
//$(document).ready(function() {
	function runConfirmShippingAjax() {
		var csaj = new GenericCheckinSubmitAjaxController();
		csaj.setTriggerURL('<?php echo $this->getUrl(false, false, false, array('view' => 'upload', 'subview' => 'summary', 'kind' => 'ajax')); ?>');
		csaj.addLocalizedMessages({
			'TitleInformation' : <?php echo json_encode(ML_LABEL_INFORMATION); ?>,
			'TitleAjaxError': 'Ajax '+<?php echo json_encode(ML_ERROR_LABEL); ?>,
			'LabelStatus': <?php echo json_encode(ML_GENERIC_STATUS); ?>,
			'LabelError': <?php echo json_encode(ML_ERROR_LABEL); ?>,
			'MessageUploadFinal': <?php echo json_encode(ML_AMAZON_SHIPPINGLABEL_SUMMARY_STATISTIC); ?>,
			'MessageUploadStatus': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS); ?>,
			'MessageUploadFatalError': <?php echo json_encode(ML_STATUS_SUBMIT_PHP_ERROR); ?> 
		});
		csaj.setInitialUploadStatus('<?php echo count($this->getOrders()); ?>');
		csaj.doAbort(<?php echo isset($_GET['abort']) ? 'true' : 'false'; ?>);
		csaj.runSubmitBatch();
	};
//});
/*]]>*/</script>