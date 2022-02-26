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
/* @var $aRow array */
/* @var $aFieldConfig array */
class_exists('MLProductList') or die();
?>
<td>
	<table class="nostyle">
		<tbody>
			<tr>
				<td><?php echo fixHTMLUTF8Entities($aRow['products_name']); ?></td>
			</tr>
			<tr>
				<td class="artNr">
                    <?php echo ML_LABEL_ART_NR_SHORT; ?>: <?php echo fixHTMLUTF8Entities($aRow['products_model']); ?><br />
                    <?php if(getDBConfigValue('general.keytype', '0') === 'pID'){ ?>
				        <?php echo ML_LABEL_PRODUCT_ID; ?>: <?php echo fixHTMLUTF8Entities($aRow['products_id']); ?>
                    <?php } ?>
                </td>
			</tr>
		</tbody>
	</table>
	<div class="warning-wrapper">
		<?php if (method_exists($this, 'isPreparedDifferently') && $this->isPreparedDifferently($aRow)) { ?>
			<span class="ml-warning" title="<?php echo ML_GENERAL_VARMATCH_ATTRIBUTE_DIFFERENT_ON_PRODUCT; ?>">&nbsp;</span>
		<?php }
		$message = '';
		if (method_exists($this, 'isDeletedAttributeFromShop') && $this->isDeletedAttributeFromShop($aRow, $message)) { ?>
			<span class="ml-warning" title="<?php echo $message; ?>">&nbsp;</span>
		<?php } ?>
	</div>
</td>