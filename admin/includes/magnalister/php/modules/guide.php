
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
 * $Id: guide.php 1606 2012-07-12  $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$_pageCSS = '
h4 {
	border-bottom: 1px solid #999;
}
';
$_MagnaSession['currentPlatform'] = '';

$_url = array(
	'module' => 'guide'
);

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

?>

<iframe id="wikiframe"
	style="
		border: 1px solid #ccc;
		width: 100%;
		-moz-box-sizing: border-box; box-sizing: border-box; -webkit-box-sizing: border-box; 
		min-height: 500px;
		margin-bottom: 5px;" 
	src="http://wiki.magnalister.com/wiki/Hauptseite">
	<a href="http://wiki.magnalister.com/wiki/Hauptseite">http://wiki.magnalister.com/</a>
</iframe>

<script>/*<![CDATA[*/
$(window).resize(function() {
	$('#wikiframe').css('height', ($(window).innerHeight() - 10)+'px');
});
$(window).load(function() {
	$(window).resize();
});
/*]]>*/</script>

<?php

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();