<?php
$buildConfig = array (
	'major' => 2,
	'minor' => 9,
	'build' => 8,
	'shoppingsystem_id' => 160,
	'shopgate_library_path' => "includes/external/shopgate",
	'plugin_name' => "xtcmodified",
	'display_name' => "Modified eCommerce",
	'zip_filename' => "modified.zip",
	'version_files' => array (
		'0' => array (
			'path' => "/includes/external/shopgate/plugin.php",
			'match' => "/define\(\'SHOPGATE_PLUGIN_VERSION\',(.+)\)/",
			'replace' => "define('SHOPGATE_PLUGIN_VERSION', '{PLUGIN_VERSION}')",
		),
	),
	'wiki' => array (
		'version' => array (
			'pages' => array (
				'Modified eCommerce' => array (
					'title' => "Modified/de",
					'match' => "#Aktuelle Plugin-Version \|\| \d+.\d+.\d+#",
					'replace' => "Aktuelle Plugin-Version || {PLUGIN_VERSION}",
				),
			),
		),
		'changelog' => array (
			'path' => "./includes/external/shopgate/",
			'pages' => array (
				'Modified eCommerce' => array (
					'title' => "Template:Modified_Changelog/de",
					'languages' => array (
						'0' => "Deutsch",
						'1' => "English",
					),
				),
			),
		),
	),
	'zip_basedir' => "",
	'exclude_files' => array (
	),
);
