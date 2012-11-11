<script language="Javascript">
	function create_billpay_invoice()
	{
		Check = confirm('<?php echo MODULE_PAYMENT_BILLPAY_TEXT_CREATE_INVOICE; ?>');
		if(Check == true) 
		{
			window.location.href = "<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$_GET["oID"].'&action=edit&billpayactive=true') ?>";			
		}
	}
	
	function cancel_billpay_invoice()
	{
		Check = confirm('<?php echo MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_ORDER; ?>');
		if(Check == true)
		{
			window.location.href = "<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$_GET["oID"].'&action=edit&billpaycancel=true') ?>";
		}
	}
</script>