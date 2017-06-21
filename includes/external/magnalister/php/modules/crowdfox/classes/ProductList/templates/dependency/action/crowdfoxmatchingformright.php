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
/* @var $oObject MLProductListDependency */
class_exists('MLProductList') or die();
$mmatch = $oObject->getConfig('matchall');
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
	<input type="hidden" value="<?php echo $oObject->getConfig('selectionname'); ?>" name="selectionName"/>
	<input type="hidden" value="_" id="actionType"/>
	<input type="hidden" value="<?php echo date('Y-m-d H:i:s'); ?>" name="PreparedTS"/>
	<input type="hidden" name="timestamp" value="<?php echo time(); ?>"/>
	<table class="right">
		<tbody>
			<tr>
				<td id="match_settings" rowspan="2" class="textleft inputCell">
					<input id="match_all_rb" type="radio" name="match" value="all"<?php echo ($mmatch ? ' checked="checked"' : ''); ?>/>
					<label for="match_all_rb"><?php echo ML_LABEL_ALL; ?></label><br />
					<input id="match_notmatched_rb" type="radio" name="match" value="notmatched"<?php echo (!$mmatch ? ' checked="checked"' : ''); ?>/>
					<label for="match_notmatched_rb"><?php echo ML_AMAZON_LABEL_ONLY_NOT_MATCHED; ?></label>
				</td>
				<td class="texcenter inputCell">
					<input type="submit" class="fullWidth ml-button smallmargin mlbtn-action" value="<?php echo ML_AMAZON_LABEL_MANUAL_MATCHING; ?>" id="prepare" name="prepare"/>
				</td>
				<td>
					<div class="desc" id="desc_man_match" title="<?php echo ML_LABEL_INFOS; ?>"><span><?php echo ML_AMAZON_LABEL_MANUAL_MATCHING; ?></span></div>
				</td>
			</tr>
			<tr>
				<td class="texcenter inputCell">
					<input type="button" class="fullWidth ml-button smallmargin mlbtn-action" value="<?php echo ML_AMAZON_LABEL_AUTOMATIC_MATCHING; ?>" id="automatching" name="automatching"/>
				</td>
				<td>
					<div class="desc" id="desc_auto_match" title="<?php echo ML_LABEL_INFOS; ?>"><span><?php echo ML_AMAZON_LABEL_AUTOMATIC_MATCHING; ?></span></div>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<div id="finalInfo" class="dialog2" title="<?php echo ML_LABEL_INFORMATION; ?>"></div>
<div id="noItemsInfo" class="dialog2" title="<?php echo ML_LABEL_NOTE; ?>"><?php echo ML_AMAZON_TEXT_MATCHING_NO_ITEMS_SELECTED; ?></div>
<div id="manMatchInfo" class="dialog2" title="<?php echo ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_MANUAL_MATCHING; ?>"><?php echo ML_CROWDFOX_TEXT_MANUALLY_MATCHING_DESC; ?></div>
<div id="autoMatchInfo" class="dialog2" title="<?php echo ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_AUTOMATIC_MATCHING; ?>"><?php echo ML_CROWDFOX_TEXT_AUTOMATIC_MATCHING_DESC; ?></div>
<div id="confirmDiag" class="dialog2" title="<?php echo ML_LABEL_NOTE; ?>"><?php echo ML_CROWDFOX_TEXT_AUTOMATIC_MATCHING_CONFIRM; ?></div>
