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
class_exists('MLProductList') or die();
?>
<div id="<?php echo strtolower(get_class($this)); ?>" class="productList">
	<?php
		foreach ($this->getDependencies() as $oDependency) {
			$sOut = $this->renderDependencyHeader($oDependency);
			if (!empty($sOut)) {
				echo $sOut;
			}
		}
	?>
	<table class="fullWidth nospacing nopadding valigntop topControls">
		<tbody>
			<tr>
				<td class="actionLeft">
					<table class="nospacing nopadding left">
						<tbody>
							<tr>
								<td class="actionLeft"><?php
									foreach ($this->getDependencies() as $oDependency) {
										$sOut = $this->renderDependencyActionTop($oDependency);
										if (!empty($sOut)) {
											echo $sOut; 
										}
									}
								?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
					<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post" onchange="this.submit();">
						<table class="nospacing nopadding right">
							<tbody>
								<tr>
									<td class="filterRight">
										<div class="filterWrapper">
										<?php
											foreach ($this->getDependencies() as $oDependency) {
												$sOut = $this->renderDependencyFilterLeft($oDependency);
												if (!empty($sOut)) {
													echo $sOut;
												}
											}
											foreach ($this->getDependencies() as $oDependency) {
												$sOut = $this->renderDependencyFilterRight($oDependency);
												if (!empty($sOut)) {
													echo $sOut;
												}
											}
										?>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</td>
			</tr>
		</tbody>
	</table>
	<?php ob_start(); ?>
	<table class="ml-pagination">
		<tr>
			<td class="ml-pagination">
				<span class="bold"><?php echo ML_LABEL_CURRENT_PAGE.' '.$this->getCurrentPage(); ?></span>
			</td>
			<td class="textright">
				<?php 
					echo renderPagination(
						$this->getCurrentPage(),
						$this->getPageCount(),
						$this->getUrlParameters(true, true, true)
					); 
				?>
			</td>
		</tr>
	</table>
	<?php 
		$sPagination = ob_get_contents();
		ob_end_clean();
		echo $sPagination;
	?>
	<form class="categoryView">
		<table class="list">
			<thead>
				<tr>
					<?php foreach ($this->aListConfig as $aElement) { ?>
						<td<?php echo ($aElement['head']['attributes'] == '') ? '' : ' ' . trim($aElement['head']['attributes']); ?>>
							<?php echo defined($aElement['head']['content']) ? constant($aElement['head']['content']) : $aElement['head']['content']; ?>
							<?php foreach (array ('sort' => '', 'altSort' => ' right') as $sKey => $sCssClass) { ?>
								<?php if (isset($aElement['head'][$sKey])) { ?>
									<span class="nowrap<?php echo $sCssClass; ?>">
										<a href="<?php echo $this->getUrl(true, false, false, array('sorting'=>$aElement['head'][$sKey]['param'].'-asc')); ?>" title="<?php echo ML_LABEL_SORT_ASCENDING; ?>" class="sorting">
											<img alt="<?php echo ML_LABEL_SORT_ASCENDING; ?>" src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>sort_up.png">
										</a>
										<a href="<?php echo $this->getUrl(true, false, false, array('sorting'=>$aElement['head'][$sKey]['param'].'-desc')); ?>" title="<?php echo ML_LABEL_SORT_DESCENDING; ?>" class="sorting">
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
				<?php foreach ($this->getProducts() as $iRow => $aProduct) { ?>
					<tr class="<?php echo ($iRow % 2 == 0) ? 'odd' : 'even'; ?>">
						<?php foreach ($this->aListConfig as $aElement) { ?>
							<?php foreach ($aElement['field'] as $sField) { ?>
								<?php $this->renderTemplate('field/'.$sField, array('aRow' => $aProduct, 'aField' => $aElement)); ?>
							<?php } ?>
						<?php } ?>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</form>
	<?php echo $sPagination; ?>
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
</div>
