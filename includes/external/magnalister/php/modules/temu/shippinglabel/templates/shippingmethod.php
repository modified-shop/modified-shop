<?php
/* @var $this TemuShippingLabelUploadShippingmethod */
class_exists('MLOrderlistTemuAbstract') or die();
require_once(DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES . 'breadcrumb.php');
?>

<div class="magnamain">
    <div id="<?php echo strtolower(get_class($this)); ?>" class="productList">
	<form class="categoryView" action="<?php echo $this->getUrl(false, false, false, array('view' => 'upload', 'subview' => 'summary')); ?>" method="post">
	    <?php foreach ($this->getOrders() as $iRow => $aOrder) {
		$sMOrderID = isset($aOrder['MOrderID']) ? $aOrder['MOrderID'] : '';
		?>
		    <table class="list">
			<thead>
			    <tr>
				<td class="dark" colspan="6">
				    <div style="float:left"><?php echo 'Temu ' . ML_LABEL_ORDER_ID . ' ' . htmlspecialchars($sMOrderID); ?></div>
				</td>
			    </tr>
			</thead>
			<thead>
			    <tr>
				<td></td>
				<?php foreach ($this->aListConfig as $aElement) { ?>
					<td<?php echo ($aElement['head']['attributes'] == '') ? '' : ' ' . trim($aElement['head']['attributes']); ?>>
					    <?php echo defined($aElement['head']['content']) ? constant($aElement['head']['content']) : $aElement['head']['content']; ?>
					</td>
				<?php } ?>
			    </tr>
			</thead>
			<?php
			if (!empty($aOrder['shippingservice'])) {
				?>
				<tbody>
				    <?php foreach ($aOrder['shippingservice'] as $iRow => $aRow) {
						if (isset($aRow['Rate']['Amount'])) {
							$aRow['Value'] = $aRow['Rate']['Amount'];
						}
						if (isset($aRow['Rate']['CurrencyCode'])) {
							$aRow['Currency'] = $aRow['Rate']['CurrencyCode'];
						}
					    ?>
					    <tr class="<?php echo ($iRow % 2 == 0) ? 'odd' : 'even'; ?>">
						<td>
							<input <?php echo $iRow == 0 ? ' checked="checked" ' : '' ?> name="<?php echo 'shippingserviceid[' . $sMOrderID . ']'?>" type="radio" value="<?php echo htmlentities(json_encode($aRow), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8') ?>" />
						</td>
						<?php foreach ($this->aListConfig as $aElement) { ?>
							<?php foreach ($aElement['field'] as $sField) { ?>
								<?php $this->renderTemplate('field/' . $sField, array('aRow' => $aRow, 'aField' => $aElement, 'aOrder' => $aOrder)); ?>
							<?php } ?>
						<?php } ?>
					    </tr>
				    <?php } ?>
				</tbody>
				<?php
			} else {
				?>
				<tbody class="even ml-shippinglabel-form" id="orderlist-<?php echo htmlspecialchars($sMOrderID); ?>">
				    <tr>
					<td colspan="6" class="errorBox">
					    <?php echo ML_TEMU_SHIPPINGLABEL_SHIPPINGMETHOD_NOSERVICE; ?>
					</td>
				    </tr>
				</tbody>
			<?php } ?>
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
					    <?php foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomLeft($oDependency);
						    if (!empty($sOut)) echo $sOut;
					    } ?>
					</td>
					<td>
					    <?php foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomCenter($oDependency);
						    if (!empty($sOut)) echo $sOut;
					    } ?>
					</td>
					<td class="lastChild">
					    <?php foreach ($this->getDependencies() as $oDependency) {
						    $sOut = $this->renderDependencyActionBottomRight($oDependency);
						    if (!empty($sOut)) echo $sOut;
					    } ?>
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
