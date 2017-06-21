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
<form action="<?php echo $this->getUrl(true, true, true); ?>" method="post" onchange="this.submit();" id="<?php echo $oObject->getIdent(); ?>">
	<select class="n" name="action[<?php echo $oObject->getIdent(); ?>]">
		<option id="defaultSelectionValue" value=""><?php echo sprintf(ML_AMAZONSHIPPINGLABEL_LABEL_TO_SELECTION_SELECT, $oObject->getSelectedCount()); ?></option>
		<optgroup label="<?php echo ML_LABEL_TO_SELECTION_SELECT_ADD; ?>">
			<option value="add-page"><?php echo ML_AMAZONSHIPPINGLABEL_LABEL_TO_SELECTION_SELECT_ADD_PAGE; ?></option>
			<option value="add-filtered"><?php echo ML_AMAZONSHIPPINGLABEL_LABEL_TO_SELECTION_SELECT_ADD_FILTERED; ?></option>
		</optgroup>
		<optgroup label="<?php echo ML_LABEL_TO_SELECTION_SELECT_SUB; ?>">
			<option value="sub-page"><?php echo ML_AMAZONSHIPPINGLABEL_LABEL_TO_SELECTION_SELECT_SUB_PAGE; ?></option>
			<option value="sub-all"><?php echo ML_AMAZONSHIPPINGLABEL_LABEL_TO_SELECTION_SELECT_SUB_ALL; ?></option>
		</optgroup>
	</select>
</form>