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
$sFilter = $oObject->getFilterRequest();
?>
<select class="n" name="filter[<?php echo $oObject->getIdent() ?>]" id="<?php echo $oObject->getIdent(); ?>">
	<?php foreach ($oObject->getFilterValues() as $sKey => $sI18n) { ?>
		<option value="<?php echo $sKey; ?>"<?php echo($sFilter != null && ($sFilter == $sKey) ? ' selected="selected"' : '') ?>>
			<?php echo sprintf($sI18n, constant('ML_MODULE_' . strtoupper($oObject->getMagnaSession('currentPlatform')))); ?>
		</option>
	<?php } ?>
</select>