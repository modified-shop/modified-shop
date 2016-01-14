<?php
/* -----------------------------------------------------------------------------------------
   $Id: shipcloud.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/application_top.php');

if (isset($_GET['action'])) {
	if ($_GET['action'] == 'pickup') {
		require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');
		$shipcloud = new shipcloud();
		$shipcloud->pickup($_POST);
		xtc_redirect(xtc_href_link(basename($PHP_SELF)));
	}
}

require (DIR_WS_INCLUDES.'head.php');
?>
	<link type="text/css" href="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.css" rel="stylesheet" />
	<script type="text/javascript" src="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.full.min.js"></script>
	<script type="text/javascript">
		function selectAll(field, name) {
			var loop;
			for (loop = 0; loop < field.length; loop++) {
				field[loop].checked = document.getElementsByName(name)[0].checked;
			}
		}
		$(document).ready(function(){
		  $.datetimepicker.setLocale('<?php echo $_SESSION["language_code"]; ?>');
      $('.earliest').datetimepicker({format:'Y-m-d H:i'});
      $('.latest').datetimepicker({format:'Y-m-d H:i'});
		});
	</script>
</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL;
        echo '</td>'.PHP_EOL;
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_modules.png'); ?></div>
        <div class="pageHeading pdg2"><?php echo HEADING_SHIPCLOUD_PICKUP; ?></div>
        <div class="main">Modules</div>      
        <?php
        	$carriers_query = xtc_db_query("SELECT *
        																		FROM ".TABLE_CARRIERS."
        																	 WHERE (LOWER(carrier_name) = 'dpd'
        																	        OR LOWER(carrier_name) = 'fedex'
        																	        OR LOWER(carrier_name) = 'hermes'
        																	        OR LOWER(carrier_name) = 'ups')");
        	while ($carriers = xtc_db_fetch_array($carriers_query)) {
						$shipcloud_query = xtc_db_query("SELECT * 
																							FROM ".TABLE_ORDERS_TRACKING." 
																						 WHERE carrier_id = '".$carriers['carrier_id']."'
																						   AND TIMESTAMP(sc_date_pickup) = 0");
						if (xtc_db_num_rows($shipcloud_query) > 0) {
		          echo xtc_draw_form('pickup', basename($PHP_SELF), 'action=pickup');
		          echo xtc_draw_hidden_field('carrier', strtolower($carriers['carrier_name']));
							?>
							<table class="tableCenter">
								<tr class="dataTableHeadingRow">
									<td class="dataTableHeadingContent txta-c" colspan="4"><?php echo $carriers['carrier_name']; ?></td>
								</tr>
								<tr class="dataTableHeadingRow">
 									<td class="dataTableHeadingContent"><?php echo xtc_draw_checkbox_field(strtolower($carriers['carrier_name']), '', '', '', 'onClick="selectAll(document.getElementsByName(\'sc_'.strtolower($carriers['carrier_name']).'[]\'), \''.strtolower($carriers['carrier_name']).'\');"').' '.TABLE_HEADING_EDIT; ?></td>
									<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
									<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRACKING_ID; ?></td>
									<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
								</tr>
								<?php
								while ($shipcloud = xtc_db_fetch_array($shipcloud_query)) {
									?>
									<tr class="dataTableRow">
										<td class="dataTableContent"><?php echo xtc_draw_checkbox_field('sc_'.strtolower($carriers['carrier_name']).'[]', $shipcloud['sc_id']); ?></td>
										<td class="dataTableContent"><?php echo $shipcloud['orders_id']; ?></td>
										<td class="dataTableContent"><?php echo $shipcloud['parcel_id']; ?></td>
										<td class="dataTableContent"><?php echo xtc_date_short($shipcloud['sc_date_added']); ?></td>
									</tr>
									<?php
								}
								?>
							</table>
							<div style="clear:both;"></div>
							<div class="txta-r pdg2" style="margin-bottom:10px;">
								<?php echo xtc_draw_input_field('earliest', '', 'class="earliest" style="width: 155px" placeholder="'.TEXT_SC_EARLIEST.'"'); ?>
								<?php echo xtc_draw_input_field('latest', '', 'class="latest" style="width: 155px" placeholder="'.TEXT_SC_LATEST.'"'); ?>
								<input type="submit" class="button" style="margin-top: -4px;" name="update" value="<?php echo BUTTON_PICKUP; ?>">
							</div>
							</form>
						<?php
						}
      		}
      	?>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>