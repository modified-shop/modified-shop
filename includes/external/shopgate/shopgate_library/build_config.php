<?php
$buildConfig = array (
	'major' => 2,
	'minor' => 9,
	'build' => 10,
	'shopgate_library_path' => "",
	'plugin_name' => "library",
	'display_name' => "Shopgate Library 2.9.x",
	'zip_filename' => "shopgate_library.zip",
	'version_files' => array (
		'0' => array (
			'path' => "/classes/core.php",
			'match' => "/define\(\'SHOPGATE_LIBRARY_VERSION\',(.+)\)/",
			'replace' => "define('SHOPGATE_LIBRARY_VERSION', '{PLUGIN_VERSION}')",
		),
	),
	'wiki' => array (
		'changelog' => array (
			'path' => "./",
			'pages' => array (
				'ShopgateLibrary' => array (
					'title' => "Template:Shopgate_Library_Changelog",
					'languages' => array (
						'0' => "English",
						'1' => "Deutsch",
					),
				),
			),
		),
	),
	'zip_basedir' => "shopgate_library",
	'exclude_files' => array (
	),
);
