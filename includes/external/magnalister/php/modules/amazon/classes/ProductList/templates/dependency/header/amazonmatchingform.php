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
/* @var $this MLProductList */
/* @var $oObject MLProductListDependencyAction */
class_exists('MLProductList') or die();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedItems = 0;
var progressInterval = null;
var percent = 0.0;

var _demo_sub = 0;
function updateProgressDemo() {
	_demo_sub -= 300;
	if (_demo_sub <= 0) {
		_demo_sub = 0;
		window.clearInterval(progressInterval);
		jQuery.unblockUI();
	}
	percent = 100 - ((_demo_sub / selectedItems) * 100);
	myConsole.log('Progress: '+_demo_sub+'/'+selectedItems+' ('+percent+'%)');	
	$('div.progressBarContainer div.progressPercent').html(Math.round(percent)+'%');
	$('div.progressBarContainer div.progressBar').css({'width' : percent+'%'});
}

function demoProgress() {
	jQuery.blockUI(blockUIProgress);
	selectedItems = _demo_sub = 4635;
	progressInterval = window.setInterval("updateProgressDemo()", 500);
}

function updateProgress() {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo $this->getUrl(false, false, false, array('kind' => 'ajax', 'automatching' => 'getProgress'), true); ?>',
		success: function(data) {
			if (!is_object(data)) {
				//selectedItems = 0;
				return;
			}
			percent = 100 - ((data.x / selectedItems) * 100);
			myConsole.log('Progress: '+data.x+'/'+selectedItems+' ('+percent+'%)');
			$('div.progressBarContainer div.progressPercent').html(Math.round(percent)+'%');
			$('div.progressBarContainer div.progressBar').css({'width' : percent+'%'});
		},
		dataType: 'json'
	});
}
function runAutoMatching(matchSetting) {
	jQuery.blockUI(blockUIProgress);
	progressInterval = window.setInterval("updateProgress()", 500);
	jQuery.ajax({
		type: 'post',
		url: '<?php echo $this->getUrl(false, false, false, array('kind' => 'ajax', 'automatching' => 'start'), true); ?>',
		data: {
			'match': matchSetting
		},
		success: function(data) {
			window.clearInterval(progressInterval);
			jQuery.unblockUI();
			myConsole.log(data);
			$('#finalInfo').html(data).jDialog({
				buttons: {
					'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
						window.location.href = '<?php echo str_replace('&amp;', '&', $this->getUrl(false, false, false)); ?>';
					}
				}
			});
		},
		dataType: 'html'
	});
}

function handleAutomatching(matchSetting) {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo $this->getUrl(false, false, false, array('kind' => 'ajax', 'automatching' => 'getProgress')); ?>',
		success: function(data) {
			if (!is_object(data)) {
				selectedItems = 0;
				return;
			}
			selectedItems = data.x;
		},
		dataType: 'json'
	});	
	myConsole.log(selectedItems);
	jQuery.unblockUI();

	if (selectedItems <= 0) {
		$('#noItemsInfo').jDialog();
	} else {
		$('#confirmDiag').jDialog({
			buttons: {
				'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
					$(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
					$(this).dialog('close');
					runAutoMatching(matchSetting);
				}
			}
		});
	}
}

$(document).ready(function() {
	$('#desc_man_match').click(function() {
		$('#manMatchInfo').jDialog();
	});
	$('#desc_auto_match').click(function() {
		$('#autoMatchInfo').jDialog();
	});
	$('#automatching').click(function() {
		//jQuery.blockUI(jQuery.extend(blockUILoading, {onBlock: handleAutomatching()}));
		var blockUILoading2 = jQuery.extend({}, blockUILoading);
		jQuery.blockUI(jQuery.extend(blockUILoading2, {onBlock: function() {
			handleAutomatching($('#match_settings input[type="radio"]:checked').val());
		}}));
		
	});
});
/*]]>*/</script>