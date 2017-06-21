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
class_exists('MLOrderlistAmazonAbstract') or die();
require_once(DIR_MAGNALISTER_MODULES_AMAZON_ORDERLIST . '/templates/breadcrumb.php');
?>

<div class="magnamain">
    <div id="<?php echo strtolower(get_class($this)); ?>" class="productList">
	<form  class="categoryView" action="<?php echo $this->getUrl(false, false, false, array('view' => 'upload', 'subview' => 'shippingmethod')); ?>" method="post">
	    <?php foreach ($this->getOrders() as $iRow => $aOrder) { ?>
		    <table class="list">
			<thead>
			    <tr>
				<td class="dark" colspan="6">
				    <div style="float:left"><?php echo 'Amazon-' . ML_LABEL_ORDER_ID . ' ' . $aOrder['MPSpecific']['MOrderID']; ?></div>
				    <div style="float:right"><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_CUSTOMER_NAME_LABEL . ': ' . fixHTMLUTF8Entities($aOrder['AddressSets']['Main']['Firstname']) . " " . fixHTMLUTF8Entities($aOrder['AddressSets']['Main']['Lastname']); ?></div>
				</td>
			    </tr>
			</thead>
			<thead>
			    <tr>
				<?php foreach ($this->aListConfig as $aElement) { ?>
					<td<?php echo ($aElement['head']['attributes'] == '') ? '' : ' ' . trim($aElement['head']['attributes']); ?>>
					    <?php echo defined($aElement['head']['content']) ? constant($aElement['head']['content']) : $aElement['head']['content']; ?>
					    <?php foreach (array('sort' => '', 'altSort' => ' right') as $sKey => $sCssClass) { ?>
						    <?php if (isset($aElement['head'][$sKey])) { ?>
							    <span class="nowrap<?php echo $sCssClass; ?>">
								<a href="<?php echo $this->getUrl(true, false, false, array('sorting' => $aElement['head'][$sKey]['param'] . '-asc')); ?>" title="<?php echo ML_LABEL_SORT_ASCENDING; ?>" class="sorting">
								    <img alt="<?php echo ML_LABEL_SORT_ASCENDING; ?>" src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>sort_up.png">
								</a>
								<a href="<?php echo $this->getUrl(true, false, false, array('sorting' => $aElement['head'][$sKey]['param'] . '-desc')); ?>" title="<?php echo ML_LABEL_SORT_DESCENDING; ?>" class="sorting">
								    <img alt="<?php echo ML_LABEL_SORT_DESCENDING; ?>" src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>sort_down.png">
								</a>
							    </span>
						    <?php } ?>
					    <?php } ?>
					</td>
				<?php } ?>
			    </tr>
			</thead>
			<tbody>
			    <?php foreach ($aOrder['Products'] as $iRow => $aProduct) { ?>
				    <tr class="<?php echo ($iRow % 2 == 0) ? 'odd' : 'even'; ?>">
					<?php foreach ($this->aListConfig as $aElement) { ?>
						<?php foreach ($aElement['field'] as $sField) { ?>
							<?php $this->renderTemplate('field/' . $sField, array('aRow' => $aProduct, 'aField' => $aElement, 'aOrder' => $aOrder,)); ?>
						<?php } ?>
					<?php } ?>
				    </tr>
			    <?php } ?>
			</tbody>
			<tbody>
			    <tr class="<?php echo ($iRow % 2 == 0) ? 'odd' : 'even'; ?>">
				<td colspan="6">
				    <?php $this->renderTemplate('form/shippinginformation', array('aOrder' => $aOrder, 'aProduct' => $aProduct)); ?>

				</td>
			    </tr>
			</tbody>
		    </table>
	    <?php } ?>
	    <table class="actions">
		<tbody>
		    <tr>
			<td class="actionswrap">
			    <table>
				<tbody>
				    <tr>
					<td class="firstChild">
					    <?php
					    foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomLeft($oDependency);
						    if (!empty($sOut)) {
							    echo $sOut;
						    }
					    }
					    ?>
					</td>
					<td>
					    <?php
					    foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomCenter($oDependency);
						    if (!empty($sOut)) {
							    echo $sOut;
						    }
					    }
					    ?>
					</td>
					<td class="lastChild">
					    <?php
					    foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomRight($oDependency);
						    if (!empty($sOut)) {
							    echo $sOut;
						    }
					    }
					    ?>
					</td>
				    </tr>
				</tbody>
			    </table>
			</td>
		    </tr>
		</tbody>
	    </table>
	</form>
    </div>

    <script type="text/javascript">/*<![CDATA[*/
            (function ($) {
                $(document).ready(function () {
                    $('.ml-shippinglabel-configshipping').change(function (e) {
                        var element = $(this)[0];
                        var index = element.selectedIndex;
                        var selectedValue = element.options[index].value;
                        var sizes = selectedValue.split("-");
                        $('#' + $(this).attr('id') + 'length').val(sizes[0]);
                        $('#' + $(this).attr('id') + "width").val(sizes[1]);
                        $('#' + $(this).attr('id') + "height").val(sizes[2]);
                    });

                    $('.ml-shippinglabel-quantity').change(function (e) {
                        var totalweight = 0;
                        $('.ml-shippinglabel-quantity.ml-shippinglabel-orderid-' + $(this).attr('data')).each(function (index) {
                            var element = $(this)[0];
                            var index = element.selectedIndex;
                            var quantity = element.options[index].value;
                            var weight = $(this).parent().find('.ml-shippinglable-product-weight').val();
                            totalweight += quantity * weight;
                        });
                        $('.ml-shippinglabel-weight-' + $(this).attr('data')).val(totalweight);
                    });

                });
            })(jQuery);
            /*]]>*/</script>
</div>