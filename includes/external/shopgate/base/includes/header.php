<?php
include_once DIR_FS_CATALOG.'includes/external/shopgate/shopgate_library/shopgate.php';
$shopgate_config = ShopgateConfig::getConfig();
$shopgateMobileHeader = '';
$shopgateLanguages = xtc_db_fetch_array(xtc_db_query("SELECT * FROM `".TABLE_LANGUAGES."` WHERE UPPER(code) = UPPER('".$shopgate_config['plugin_language']."')"));
$shopgateLanguage = isset($shopgateLanguages['directory']) ? strtolower($shopgateLanguages["directory"]) : 'german';
$shopgateCurrentLanguage = isset($_SESSION['language']) ? strtolower($_SESSION['language']) : 'german';

if (
	isset($shopgate_config["shop_is_active"]) &&
	isset($shopgate_config["enable_mobile_website"]) &&
	$shopgate_config["shop_is_active"] &&
	$shopgate_config["enable_mobile_website"]
) {
	// instantiate and set up redirect class
	$redirector = new ShopgateMobileRedirect();
	
	
	############################
	# redirector configuration #
	############################
	
	### Shop-Alias setzen / Set Shop Alias ###
	#
	# Rufen Sie diese Methode auf, um zu Ihrem Shopgate Shop-Alias umzuleiten,
	# im Beispiel: "my-shop.shopgate.com".
	#
	# Call to redirect to your Shopgate shop alias, e.g. "my-shop.shopgate.com".
	#
	if (!empty($shopgate_config['alias'])) $redirector->setAlias($shopgate_config['alias']);
	
	### URL setzen / Set URL ###
	#
	# Rufen Sie diese Methode auf, um zu Ihrer eigenen URL für Ihre mobilen Webseite
	# umzuleiten, im Beispiel: "https://m.my-shop.de".
	# Dies überschreibt einen ggf. gesetzten Alias.
	#
	# Call to redirect to your own URL of your mobile webpage, e.g. "https://m.my-shop.de".
	# This overrides the alias if set.
	#
	if (!empty($shopgate_config['cname'])) $redirector->setCustomMobileUrl($shopgate_config['cname']);
	
	### Dauerhafte HTTPS-Kommunikation aktivieren / activate communication via HTTPS permanently
	#
	# Im Normalfall findet die Umleitungs-Klasse selbst heraus, ob HTTP oder HTTPS zum Laden der externen
	# Button-Grafiken von Shopgate verwendet werden soll. Das kann in bestimmten Konstellationen fehl-
	# schlagen und provoziert dann (falsche) Meldungen über Sicherheitsrisiken beim Betrachter Ihres
	# Shops. Mit dem Aufruf dieser Funktion können Sie den HTTPS-Download dauerhaft aktivieren.
	#
	# The redirect class is usually able to determine whether to use HTTP or HTTPS to load the external
	# button images from Shopgate. However, in certain constellations the detection fails and causes
	# (false) security alerts to the visitor of your shop. Call this method to permanently activate
	# downloading via HTTPS.
	#
	if (!empty($shopgate_config['always_use_ssl'])) $redirector->setAlwaysUseSSL();
	
	### Aktivierung des Keyword-Updates / Activate
	#
	# Sie können hier die Aktualisierung der Stichwörter, die eine Weiterleitung
	# auslösen, einschalten. Als Parameter kann der Zeitintervall in Stunden übergeben
	# werden, in dem die Wörter aktualisiert werden.
	#
	# Call to enable redirect keyword updates. You can pass the interval (in hours)
	# after which keywords are updated.
	#
	$redirector->enableKeywordUpdate(24);
	
	### Beschriftung des Buttons / Button description
	#
	# Nutzen Sie diese Funktion, um einen anderen Beschriftungstext für den Button zu setzen
	# (z.B. sprachabhängig).
	#
	# Use this to set a different description for your mobile header button.
	#
	$redirector->setButtonDescription('Mobile Webseite aktivieren');
	
	
	##################
	# redirect logic #
	##################
	
	// check request for mobile devices
 	if ($redirector->isRedirectAllowed() && $redirector->isMobileRequest() && ($shopgateCurrentLanguage == $shopgateLanguage)) {
		$redirectionUrl = null;
		
		// set redirection url
		if ($product->isProduct) {
			// product redirect
			$redirectionUrl = $redirector->getItemUrl($product->pID);
		
		} elseif (!empty($current_category_id)) {
			// category redirect
			$redirectionUrl = $redirector->getCategoryUrl($current_category_id);
			
		} else {
			// default redirect
			$redirectionUrl = $redirector->getShopUrl();
		}
		
		// perform the redirect
		$redirector->redirect($redirectionUrl);
 	} elseif ($redirector->isMobileRequest() && !$redirector->isRedirectAllowed() && ($shopgateCurrentLanguage == $shopgateLanguage)) {
 		$shopgateMobileHeader = $redirector->getMobileHeader();
 	}
}