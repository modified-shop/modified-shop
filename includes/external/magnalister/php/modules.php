<?php
/*
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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/* Available modules accessable as pages */
$_modules = array(
	'amazon' => array(
		'title' => ML_MODULE_AMAZON,
		'logo' => 'amazon',
		'displayAlways' => true,
		'requiredConfigKeys' => array (
			'amazon.firstactivation',
			'amazon.lang',
			'amazon.mwstfallback',
			/*//
			{search: 1427198983}'amazon.mwst.shipping',
			//*/
			'amazon.quantity.type',
			'amazon.leadtimetoship',
			'amazon.price.addkind',
			'amazon.import',
			'amazon.orderstatus.open',
			'amazon.orderstatus.fba',
			'amazon.orderstatus.sync',
			'amazon.orderstatus.shipped',
			'amazon.orderstatus.carrier.default',
			'amazon.orderstatus.cancelled',
			'amazon.stocksync.tomarketplace',
			'amazon.stocksync.frommarketplace',
			'amazon.mail.send',
			//'amazon.CustomerGroup', /* gibt es nicht in osCommerce */
		),
		'pages' => array (
			'prepare' => array (
				'title' => ML_AMAZON_PRODUCT_PREPARE,
				'views' => array (
					'apply' => ML_AMAZON_NEW_ITMES,
					'match' => ML_AMAZON_PRODUCT_MATCHING,
					'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
				)
			),
			#'apply' => ML_AMAZON_NEW_ITMES,
			'checkin' => ML_GENERIC_CHECKIN,
			'shippinglabel' => array (
				'title' => ML_AMAZON_SHIPPINGLABEL,
				'views' => array (
					'upload' => ML_AMAZON_SHIPPINGLABEL_UPLOAD,
					'overview' => ML_AMAZON_SHIPPINGLABEL_OVERVIEW,
				),
                        ),
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				),
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'Amazon',
			'currency' => '__depends__',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'ebay' => array (
		'title' => ML_MODULE_EBAY,
		'logo' => 'ebay',
		'displayAlways' => true,
		'requiredConfigKeys' => array (
            'ebay.username',
			'ebay.firstactivation',
			'ebay.token',
			'ebay.lang',
			'ebay.location',
			'ebay.postalcode',
		),
		'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    #'match' => ML_AMAZON_PRODUCT_MATCHING,     #PBSE# suspended
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'eBay',
			'currency' => '__depends__',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'yatego' => array(
		'title' => ML_MODULE_YATEGO,
		'logo' => 'yatego',
		'displayAlways' => false,
		'referer' => array('yatego.com'),
		'requiredConfigKeys' => array (
			'yatego.firstactivation',
			'yatego.username',
			'yatego.password',
			'yatego.lang',
			'yatego.shipping.country',
			'yatego.shipping.method',
			'yatego.shipping.cost',
			'yatego.quantity.type',
			'yatego.quantity.value',
			'yatego.stocksync.frommarketplace',
			'yatego.stocksync.tomarketplace',
			'yatego.import',
			//'yatego.CustomerGroup', /* gibt es nicht in osCommerce */
			'yatego.orderstatus.open',
			//'yatego.orderstatus.cancelled',
			//'yatego.orderstatus.shipped',
			/*//{search: 1427198983}
			'yatego.mwst.shipping',
			//*/
			'yatego.mail.send',
		),
		'pages' => array (
			'catmatch' => ML_YATEGO_CATEGORY_MATCHING,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'checkin',
			'subsystem' => 'Yatego',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'hitmeister' => array(
		'title' => ML_MODULE_HITMEISTER,
		'logo' => 'kaufland',
		'displayAlways' => true,
		'requiredConfigKeys' => array (
			'hitmeister.firstactivation',
			'hitmeister.clientkey',
			'hitmeister.secretkey',
			'hitmeister.lang',
			'hitmeister.handlingtime',
			'hitmeister.itemcondition',
			'hitmeister.itemcountry',
			'hitmeister.import',
			'hitmeister.multimatching.itemsperpage'
		),
		'pages' => array (
			'prepare' => array (
				'title' => ML_GENERIC_PREPARE,
				'views' => array (
					'apply' => ML_AMAZON_NEW_ITMES,
					'match' => ML_AMAZON_PRODUCT_MATCHING,
					'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
				)
			),
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'Hitmeister',
			'currency' => '__depends__',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'cdiscount' => array(
		'title' => ML_MODULE_CDISCOUNT,
		'logo' => 'cdiscount',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'cdiscount.firstactivation',
			'cdiscount.mpusername',
			'cdiscount.mppassword',
			'cdiscount.lang',
			'cdiscount.itemcondition',
			'cdiscount.import',
			'cdiscount.multimatching.itemsperpage'
		),
		'pages' => array (
			'prepare' => array (
				'title' => ML_GENERIC_PREPARE,
				'views' => array (
					'apply' => ML_AMAZON_NEW_ITMES,
//					'match' => ML_AMAZON_PRODUCT_MATCHING,
					'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
				)
			),
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'Cdiscount',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
    'priceminister' => array(
        'title' => ML_MODULE_PRICEMINISTER,
        'logo' => 'priceminister',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'priceminister.firstactivation',
            'priceminister.apitoken',
            'priceminister.mpusername',
            'priceminister.lang',
            'priceminister.itemcondition',
            'priceminister.import',
            'priceminister.multimatching.itemsperpage',
            'priceminister.orderstatus.cancelreason',
            'priceminister.orderimport.shippingfromcountry',
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'match' => ML_AMAZON_PRODUCT_MATCHING,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED,
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'Priceminister',
            'currency' => 'EUR',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),

    'crowdfox' => array(
        'title' => ML_MODULE_CROWDFOX,
        'logo' => 'crowdfox',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'crowdfox.firstactivation',
            'crowdfox.mpusername',
            'crowdfox.mppassword',
            'crowdfox.companyname',
            'crowdfox.lang',
            'crowdfox.import',
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED,
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'Crowdfox',
            'currency' => 'EUR',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),
	'guenstiger' => array(
		'title' => ML_MODULE_GUENSTIGER,
		'logo' => 'guenstiger',
		'displayAlways' => false,
		'referer' => array('guenstiger.de'),
		'requiredConfigKeys' => array (
			'guenstiger.lang',
			'guenstiger.inventorysync.price',
			'guenstiger.shipping.country',
			'guenstiger.shipping.method',
			'guenstiger.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'getdeal' => array(
		'title' => ML_MODULE_GETDEAL,
		'logo' => 'getdeal',
		'displayAlways' => false,
		'referer' => array('getdeal.de'),
		'requiredConfigKeys' => array (
			'getdeal.lang',
			'getdeal.inventorysync.price',
			'getdeal.shipping.country',
			'getdeal.shipping.method',
			'getdeal.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'idealo' => array(
		'title' => ML_MODULE_IDEALO,
		'logo' => 'idealo',
		'displayAlways' => false,
		'referer' => array('idealo.de'),
		'requiredConfigKeys' => array (
			'idealo.lang',
			'idealo.inventorysync.price',
			'idealo.shipping.country',
			'idealo.shipping.method',
			'idealo.shipping.cost',
			'idealo.deliverytime',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'kelkoo' => array(
		'title' => ML_MODULE_KELKOO,
		'logo' => 'kelkoo',
		'displayAlways' => false,
		'referer' => array('kelkoo.de'),
		'requiredConfigKeys' => array (
			'kelkoo.lang',
			'kelkoo.inventorysync.price',
			'kelkoo.shipping.country',
			'kelkoo.shipping.method',
			'kelkoo.shipping.cost',
		),
		'pages' => array (
			//'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'preissuchmaschine' => array(
		'title' => ML_MODULE_PREISSUCHMASCHINE,
		'logo' => 'preissuchmaschine',
		'displayAlways' => false,
		'referer' => array('preissuchmaschine.de', 'preissuchmaschine.ch'),
		'requiredConfigKeys' => array (
			'preissuchmaschine.lang',
			'preissuchmaschine.inventorysync.price',
			'preissuchmaschine.shipping.country',
			'preissuchmaschine.shipping.method',
			'preissuchmaschine.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'billiger' => array(
		'title' => ML_MODULE_BILLIGER,
		'logo' => 'billiger',
		'displayAlways' => false,
		'referer' => array('billiger.de'),
		'requiredConfigKeys' => array (
			'billiger.lang',
			'billiger.inventorysync.price',
			'billiger.shipping.country',
			'billiger.shipping.method',
			'billiger.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'daparto' => array(
		'title' => ML_MODULE_DAPARTO,
		'logo' => 'daparto',
		'displayAlways' => false,
		'referer' => array('daparto.de'),
		'requiredConfigKeys' => array (
			'daparto.tecdoc',
			'daparto.condition',
			'daparto.lang',
			'daparto.inventorysync.price',
			'daparto.shipping.country',
			'daparto.shipping.method',
			'daparto.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
	'tradoria' => array (
		'title' => 'Rakuten',
		'logo' => 'rakuten',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'tradoria.apikey',
			'tradoria.mpusername',
			'tradoria.import',
		),
		'pages' => array (
			'prepare' => array (
				'title' => ML_GENERIC_PREPARE,
				'views' => array (
					'apply' => ML_AMAZON_NEW_ITMES,
					'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
				)
			),
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'Tradoria',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'lafeo' => array (
		'title' => 'lafeo',
		'logo' => 'lafeo',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'lafeo.apikey',
			'lafeo.mpusername',
			'lafeo.mppassword',
			'lafeo.import',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'checkin',
			'subsystem' => 'lafeo',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'hood' => array (
		'title' => ML_MODULE_HOOD,
		'logo' => 'hood',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'hood.mpusername',
			//'hood.mppassword',
			'hood.apikey',
                        'hood.orderstatus.canceled.nostock',
			'hood.orderstatus.canceled.defect',
			'hood.orderstatus.canceled.revoked',
			'hood.orderstatus.canceled.nopayment',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'hood',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'twenga' => array(
		'title' => ML_MODULE_TWENGA,
		'logo' => 'twenga',
		'displayAlways' => false,
		'referer' => array('twenga.de'),
		'requiredConfigKeys' => array (
			'twenga.lang',
			'twenga.inventorysync.price',
			'twenga.shipping.country',
			'twenga.shipping.method',
			'twenga.shipping.cost',
		),
		'pages' => array (
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'failed' => ML_GENERIC_FAILED
				)
			),
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'subsystem' => 'ComparisonShopping',
			'currency' => 'EUR',
			'hasOrderImport' => false,
		),
		'type' => 'marketplace',
	),
    'etsy' => array (
        'title' => ML_MODULE_ETSY,
        'logo' => 'etsy',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'etsy.shop.language',
            'etsy.currency',
            'etsy.lang',
            'etsy.imagepath',
            'etsy.price.addkind',
            'etsy.price.factor',
            //'etsy.price.group', // do not check - because its doesn't exists in oscommerce
            'etsy.import',
            //'etsy.CustomerGroup', // do not check - because its doesn't exists in oscommerce
            'etsy.preimport.start',
            'etsy.orderstatus.open',
            'etsy.orderstatus.shipped',
            'etsy.stocksync.tomarketplace',
            'etsy.stocksync.frommarketplace',
            'etsy.inventorysync.price',
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'Etsy',
            'currency' => 'EUR',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),
	'dawanda' => array (
		'title' => ML_MODULE_DAWANDA,
		'logo' => 'dawanda',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'dawanda.mpusername',
			'dawanda.mppassword',
			'dawanda.apikey',
		),
		'pages' => array (
			'prepare' => array (
				'title' => ML_GENERIC_PREPARE,
				'views' => array (
					'apply' => ML_AMAZON_NEW_ITMES,
					'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
				)
			),
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'DaWanda',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'bepado' => array (
		'title' => 'bepado',
		'logo' => 'bepado',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'bepado.access.MPUsername',
			'bepado.access.MPPassword',
			'bepado.access.ShopId',
			'bepado.access.ApiKey',
			'bepado.access.FtpUsername',
			'bepado.access.FtpPassword',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'bepado',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'ricardo' => array (
		'title' => 'ricardo',
		'logo' => 'ricardo',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'ricardo.access.MPUSERNAME',
			'ricardo.access.MPPASSWORD',
			'ricardo.access.LANG',
            'ricardo.token',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'ricardo',
			'currency' => 'CHF',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'check24' => array (
		'title' => 'check24',
		'logo' => 'check24',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'check24.access.MPUSERNAME',
			'check24.access.MPPASSWORD',
			'check24.access.PORT',
			'check24.access.FTPSERVER',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'check24',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
	'fyndiq' => array (
		'title' => 'Fyndiq',
		'logo' => 'fyndiq',
		'displayAlways' => false,
		'requiredConfigKeys' => array (
			'fyndiq.access.MPUSERNAME',
			'fyndiq.access.MPPASSWORD',
			'fyndiq.access.MPAPITOKEN',
		),
		'pages' => array (
			'prepare' => ML_GENERIC_PREPARE,
			'checkin' => ML_GENERIC_CHECKIN,
			'listings' => array (
				'title' => ML_GENERIC_LISTINGS,
				'views' => array (
					'inventory' => ML_GENERIC_INVENTORY,
					'deleted' => ML_GENERIC_DELETED,
					'rejected' => ML_GENERIC_REJECTED,
				)
			),
			'errorlog' => ML_GENERIC_ERRORLOG,
			'conf' => ML_GENERIC_CONFIGURATION,
		),
		'settings' => array (
			'defaultpage' => 'prepare',
			'subsystem' => 'fyndiq',
			'currency' => 'EUR',
			'hasOrderImport' => true,
		),
		'type' => 'marketplace',
	),
    'googleshopping' => array(
        'title' => 'Google Shopping',
        'logo' => 'googleshopping',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'googleshopping.firstactivation',
            'googleshopping.lang',
            'googleshopping.lang.match.googleshopping',
            'googleshopping.targetCountry',
            'googleshopping.currency',
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED,
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'Googleshopping',
            'currency' => '__depends__',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),
    'metro' => array(
        'title' => 'METRO',
        'logo' => 'metro',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'metro.clientkey',
            'metro.secretkey',
            'metro.shippingdestination',
            'metro.shippingorigin',
            'metro.lang',
            'metro.mwst.fallback',
            'metro.price.addkind',
            'metro.stocksync.tomarketplace',
            'metro.orderstatus.cancelled',
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED,
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'Metro',
            'currency' => 'EUR',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),
    'otto' => array(
        'title' => 'OTTO',
        'logo' => 'otto',
        'displayAlways' => false,
        'requiredConfigKeys' => array (
            'otto.lang',
            'otto.product.vat',
            'otto.price.addkind',
            'otto.stocksync.tomarketplace',
            'otto.shipping.status',
            'otto.orders.shipping.address.city',
            'otto.orders.shipping.address.countrycode',
            'otto.orders.shipping.address.zip',
            'otto.send.carrier',
            'otto.return.carrier',
            'otto.forwarding.carrier',
            'otto.orders.return.tracking.key.DBMatching.table'
        ),
        'pages' => array (
            'prepare' => array (
                'title' => ML_GENERIC_PREPARE,
                'views' => array (
                    'apply' => ML_AMAZON_NEW_ITMES,
                    'varmatch' => ML_GENERIC_VARIANTEN_MATCHING,
                )
            ),
            'checkin' => ML_GENERIC_CHECKIN,
            'listings' => array (
                'title' => ML_GENERIC_LISTINGS,
                'views' => array (
                    'inventory' => ML_GENERIC_INVENTORY,
                    'deleted' => ML_GENERIC_DELETED,
                )
            ),
            'errorlog' => ML_GENERIC_ERRORLOG,
            'conf' => ML_GENERIC_CONFIGURATION,
        ),
        'settings' => array (
            'defaultpage' => 'prepare',
            'subsystem' => 'OTTO',
            'currency' => 'EUR',
            'hasOrderImport' => true,
        ),
        'type' => 'marketplace',
    ),
	'more' => array (
		'title' => '&hellip;',
		'displayAlways' => true,
		'subtitle' => ML_LABEL_MORE_MODULES,
		'type' => 'system',
	),
	'configuration' => array (
		'title' => ML_MODULE_GLOBAL_CONFIG,
		'displayAlways' => true,
		'type' => 'system',
	),
	'statistics' => array (
		'title' => ML_MODULE_STATISTICS,
		'displayAlways' => true,
		'type' => 'system',
	),
	'guide' => array (
		'title' => ML_MODULE_GUIDE,
		#'label' => ML_MODULE_GUIDE,
		#'logo' => 'guide',
		'displayAlways' => true,
		'type' => 'system',
	),
    'rookie' => array (
        'title' => ML_MODULE_ROOKIE,
        'displayAlways' => false,
        'type' => 'system',
    ),
);
