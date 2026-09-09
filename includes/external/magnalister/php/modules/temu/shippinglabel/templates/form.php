<?php
/* @var $this TemuShippingLabelUploadForm */
class_exists('MLOrderlistTemuAbstract') or die();
require_once(DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES . 'breadcrumb.php');
?>

<div class="magnamain">
    <div id="<?php echo strtolower(get_class($this)); ?>" class="productList">
	<form  class="categoryView" action="<?php echo $this->getUrl(false, false, false, array('view' => 'upload', 'subview' => 'shippingmethod')); ?>" method="post">
	    <?php foreach ($this->getOrders() as $iRow => $aOrder) { ?>
		    <table class="list">
			<thead>
			    <tr>
				<td class="dark" colspan="6">
				    <div style="float:left"><?php echo 'Temu ' . ML_LABEL_ORDER_ID . ' ' . $aOrder['MPSpecific']['MOrderID']; ?></div>
				    <div style="float:right"><?php echo 'Buyer name: ' . fixHTMLUTF8Entities($aOrder['AddressSets']['Main']['Firstname']) . " " . fixHTMLUTF8Entities($aOrder['AddressSets']['Main']['Lastname']); ?></div>
				</td>
			    </tr>
			</thead>
			<thead>
			    <tr>
				<?php foreach ($this->aListConfig as $aElement) { ?>
					<td<?php echo ($aElement['head']['attributes'] == '') ? '' : ' ' . trim($aElement['head']['attributes']); ?>>
					    <?php echo defined($aElement['head']['content']) ? constant($aElement['head']['content']) : $aElement['head']['content']; ?>
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
</div>
