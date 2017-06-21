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
/* @var $oObject MLOrderlistAmazonAbstractDependency */
class_exists('MLOrderlistAmazonAbstract') or die();

$filtersByType = $oObject->getFiltersByType();
$filtersNeedingSync = array();
foreach ($filtersByType as $filterKey => $values) {
	if ($oObject->filterNeedsSync($filterKey)) {
		$filtersNeedingSync[] = array (
			'key' => $filterKey,
			'values' => $values,
		);
	}
}
?>

	<div style="width:100px" id="<?php echo get_class($oObject); ?>Dialog" class="dialog2" title="<?php echo ML_STATUS_FILTER_SYNC_ITEM; ?>">
		<p class="successBoxBlue"><?php echo ML_STATUS_FILTER_SYNC_CONTENT; ?></p>
		<p class="successBox" style="display:none"><?php echo STATUS_FILTER_SYNC_SUCCESS; ?></p>
		<div class="progressStep stepdeleted" style="display:none">
			<div><?php echo ML_GENERIC_FILTER_MP_SYNC_DELETED; ?></div>
			<div class="progressBarContainer" data-step="deleted" style="margin-bottom:1em">
				<div class="progressBar" style="width: 0;"></div>
				<div class="progressPercent">0%</div>
			</div>
		</div>
		<div class="progressStep stepdefault" style="display:none">
			<div><?php echo ML_GENERIC_FILTER_MP_SYNC_INVENTORY; ?></div>
			<div class="progressBarContainer" data-step="default" style="margin-bottom:1em">
				<div class="progressBar" style="width: 0;"></div>
				<div class="progressPercent">0%</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">/*<![CDATA[*/
		function mlUpdateFilterData(theform, selectElem) {
			var fDebug = function() {
				myConsole.log('fDebug', arguments);
			};

			var iLimit = <?php echo $oObject->getConfig('limit'); ?>;
			var iStep = 0;
			var iOffset = 0;
			var blNext = true;
			var eForm = theform;
			var iInterval = 500;
			var eDialog = $("#<?php echo get_class($oObject); ?>Dialog");

			var steps = [];
			var stepsCount = 0;

			var filtersNeedingSync = <?php echo json_encode($filtersNeedingSync);?>;

			if (filtersNeedingSync.length == 0) {
				eForm.submit();
				fDebug('No filter has to be synced.');
				return true;
			}

			$(filtersNeedingSync).each(function (index, filter) {
				if (filter.values.indexOf(selectElem.val()) >= 0) {
					steps.push(filter.key);
				}
			});

			stepsCount = steps.length;

			if (stepsCount == 0) {
				eForm.submit();
				fDebug('No filter has to be synced for the selected value: '+selectElem.val()+'.');
				return true;
			}

			fDebug('Syncing', steps);

			var fAjax = function() {
				//fDebug([iLimit, iOffset, async2sync]);
				if (blNext) {
					fDebug("next");
					blNext = false;
					var iTime = new Date().getTime();
					var sCurrentStep = steps[0];

					if (eDialog.find('.progressStep.step'+sCurrentStep).is(':hidden')) {
						eDialog.find('.progressStep').css({'display': 'none'});
						eDialog.find('.progressStep.step'+sCurrentStep).css({'display': 'block'});
					}

					$.ajax({
						url: $(eForm).attr("action") + "&kind=ajax",
						type: $(eForm).attr("method"),
						data: $(eForm).serialize() + "&action[<?php echo $oObject->getIdent(); ?>][limit]=" + iLimit + "&action[<?php echo $oObject->getIdent(); ?>][offset]=" + iOffset + "&action[<?php echo $oObject->getIdent(); ?>][step]=" + sCurrentStep,
						success: function(data) {
							var json = $.parseJSON(data);
							var fPercent = 0.0;
							if (typeof json.success !== "undefined") {
								var iDuration = 500;
								iOffset = 0;
								iLimit = <?php echo $oObject->getConfig('limit'); ?>;
								iStep++;
							}
							if ((typeof json.success !== "undefined") && (iStep === stepsCount)) {
								eDialog.find(".successBoxBlue").css("display", "none");
								eDialog.find(".successBox").css("display", "block");
								window.clearInterval(async2sync);
								jQuery.blockUI(blockUILoading);
								fPercent = 100;
								eForm.submit();
							} else {
								blNext = true;
								if (typeof json.info !== "undefined") {
									fPercent = (json.info.current / json.info.total) * 100;
									iLimit = json.params.limit;
									iOffset = json.params.offset;
								} else {
									fPercent = 100;
								}
								var iDuration = new Date().getTime() - iTime;
							}
							if (fPercent == 100) {
								steps.shift();
							}

							eDialog.find("[data-step=\"" + sCurrentStep + "\"] .progressBar").css({
								width: fPercent + "%",
								transitionDuration: iDuration + "ms"
							});
							eDialog.find("[data-step=\"" + sCurrentStep + "\"] .progressPercent").html(Math.round(fPercent) + "%");
						},
						beforeSend: function() {
							if (eDialog.is(":hidden")) {
								fDebug("show");
								eDialog.jDialog({
									buttons: {},
									height: "auto",
									width: 400
								});
							}
						}
					});
				}
			};
			/**
			 * using interval to emulate synchronous (a)jax - problems with webkit-browser
			 * @see http://bugs.jquery.com/ticket/8819
			 */
			var async2sync = window.setInterval(function() {
				fAjax();
			}, iInterval);
		}
		$(document).ready(function() {
			$("select#<?php echo $oObject->getIdent(); ?>").change(function() {
				mlUpdateFilterData(this.form, $(this));
				return false;
			});
		});
	/*]]>*/</script>
