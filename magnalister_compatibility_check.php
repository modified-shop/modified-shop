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
 * $Id: magnalister_compatibility_check.php 431 2015-05-27 12:13:35Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

define('ML_CONNECT_TIMEOUT', 2);

ini_set('display_errors', 1);
error_reporting(-1);

/**
 * Browsersprache ermitteln
 *
 * @author Christian Seiler
 * @origin http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
 */
function lang_getfrombrowser($allowed_languages, $default_language, $lang_variable = null, $strict_mode = true) {
    // $_SERVER['HTTP_ACCEPT_LANGUAGE'] verwenden, wenn keine Sprachvariable mitgegeben wurde
    if ($lang_variable === null) {
        $lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    // wurde irgendwelche Information mitgeschickt?
    if (empty($lang_variable)) {
        // Nein? => Standardsprache zurueckgeben
        return $default_language;
    }

    // Den Header auftrennen
    $accepted_languages = preg_split('/,\s*/', $lang_variable);

    // Die Standardwerte einstellen
    $current_lang = $default_language;
    $current_q = 0;

    // Nun alle mitgegebenen Sprachen abarbeiten
    foreach ($accepted_languages as $accepted_language) {
        // Alle Infos ueber diese Sprache rausholen
        $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
                           '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);

        // war die Syntax gueltig?
        if (!$res) {
            // Nein? Dann ignorieren
            continue;
        }
        
        // Sprachcode holen und dann sofort in die Einzelteile trennen
        $lang_code = explode ('-', $matches[1]);

        // Wurde eine Qualitaet mitgegeben?
        if (isset($matches[2])) {
            // die Qualitaet benutzen
            $lang_quality = (float)$matches[2];
        } else {
            // Kompabilitaetsmodus: Qualitaet 1 annehmen
            $lang_quality = 1.0;
        }

        // Bis der Sprachcode leer ist...
        while (count ($lang_code)) {
            // mal sehen, ob der Sprachcode angeboten wird
            if (in_array (strtolower (join ('-', $lang_code)), $allowed_languages)) {
                // Qualitaet anschauen
                if ($lang_quality > $current_q) {
                    // diese Sprache verwenden
                    $current_lang = strtolower (join ('-', $lang_code));
                    $current_q = $lang_quality;
                    // Hier die innere while-Schleife verlassen
                    break;
                }
            }
            // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
            if ($strict_mode) {
                // innere While-Schleife aufbrechen
                break;
            }
            // den rechtesten Teil des Sprachcodes abschneiden
            array_pop ($lang_code);
        }
    }

    // die gefundene Sprache zurueckgeben
    return $current_lang;
}

$strings = array (
	'en' => array (
		'headline' => 'compatibility check',
		'minrequirements' => 'Minimum requirements',
		'status' => 'Status',
		'version' => 'Version',
		'connect_ext_server' => 'Establish connections to external servers',
		'optimal_support' => 'For optimal support',
		'cURL_installed' => 'cURL installed',
		'connect_ext_server_curl' => 'Establish connections to external servers with cURL',
		'connect_ext_server_fgc' => 'Establish connections to external servers with PHP (file_get_contens)',
		'with' => 'with',
		'without' => 'without',
		'php_safe_mode_disabled' => 'PHP Safe Mode disabled',
		'php_magic_quotes_disabled' => 'Magic Quotes disabled',
		'configure_not_found' => 'The shop configuration file could not be found. Is the script positioned in the proper directory?',
		'max_execution_time' => 'Maximum executiontime changeable (standard: %ds)',
		'max_ram' => 'Maximum amount of memory changeable (Standard: %s)',
	),
	'de' => array (
		'headline' => 'Kompatibilit&auml;ts-Check',
		'minrequirements' => 'Mindestvoraussetzung',
		'status' => 'Status',
		'version' => 'Version',
		'connect_ext_server' => 'Verbindungsaufbau zu externen Server',
		'optimal_support' => 'F&uuml;r optimale Unterst&uuml;zung',
		'cURL_installed' => 'cURL installiert',
		'connect_ext_server_curl' => 'Verbindungsaufbau zu externen Server via cURL',
		'connect_ext_server_fgc' => 'Verbindungsaufbau zu externen Server via PHP (file_get_contens)',
		'with' => 'mit',
		'without' => 'ohne',
		'php_safe_mode_disabled' => 'PHP Safe Mode deaktiviert',
		'php_magic_quotes_disabled' => 'Magic Quotes deaktiviert',
		'configure_not_found' => 'Die Shop-Konfigurationsdatei konnte nicht gefunden werden. Liegt das Script im richtigen Verzeichnis?',
		'max_execution_time' => 'Maximale Ausf&uuml;hrungszeit &auml;nderbar (Standard: %ds)',
		'max_ram' => 'Maximaler Ramverbrauch &auml;nderbar (Standard: %s)',
	)
);

/**
 * Convert output of phpinfo() to an array
 *
 * @author webmaster at askapache dot com
 * @origin http://www.php.net/manual/de/function.phpinfo.php#87463
 */
function phpinfo_array($return = false){
	ob_start();
	phpinfo(-1);
	
	$pi = preg_replace(
		array(
			'#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
			'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
			"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
			.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
			'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
			"# +#", '#<tr>#', '#</tr>#'
		),
		array(
			'$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
			'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
			"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
			'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
			'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
			'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
		),
		ob_get_clean()
	);
	
	$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
	unset($sections[0]);
	
	$pi = array();
	foreach($sections as $section) {
		$n = str_replace(' ', '_', substr($section, 0, strpos($section, '</h2>')));
		preg_match_all(
			'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
		 	$section, $askapache, PREG_SET_ORDER
		);
		foreach($askapache as $m) {
			$pi[$n][str_replace(' ', '_', $m[1])] = (
				!isset($m[2]) 
					?
						$m[1] 
					: 
						(
							(!isset($m[3]) || ($m[2] == $m[3]))
								?
									$m[2]
								:
									array_slice($m, 2)
						)
			);
		}
	}
	return ($return === false) ? print_r($pi) : $pi;
}

function decodeClientVersion($str) {
	$ret = array();
	
	if (!preg_match('/^\{([^\}]*)\}$/', $str, $match)) return $ret;
	if (!preg_match_all('/"([^\"]*)":"?([^\"]*)"?,/', $match[1].',', $match)) return $ret;

	foreach ($match[1] as $i => $key) {
		$ret[$key] = $match[2][$i];
	}
	return $ret;
}

function fileGetContentsCURL($path, $forceSSLOff = false, &$warnings, $timeout = -1, &$method) {
	if (!function_exists('curl_init') || (strpos($path, 'http') === false)) {
		return false;
	}
	$warnings = '';
	$cURLVersion = curl_version();

    $ch = curl_init();
	
	$hasSSL = is_array($cURLVersion) && array_key_exists('protocols', $cURLVersion) && @in_array('https', $cURLVersion['protocols']);
	if ($hasSSL && !$forceSSLOff) {
		$path = str_replace('http://', 'https://', $path);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	}
    curl_setopt($ch, CURLOPT_URL, $path);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($timeout > 0) {
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	}
    $return = curl_exec($ch);
    if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
    	$return = false;
    }
   	$warning = curl_error($ch);
    curl_close($ch);
	$method = 'cURL ('.($hasSSL ? LANG_WITH : LANG_WITHOUT).' SSL)';
    return $return;
}

function fileGetContentsPHP($path, &$warnings, $timeout = -1, &$method) {
	if ($timeout > 0) {
		$context = stream_context_create(
			array('http' => 
			    array(
			        'method'  => 'GET',
			        'timeout' => $timeout
			    )
			)
		);
	} else {
		$context = null;
	}
	ob_start();
	$return = file_get_contents($path, false, $context);
	$warnings = trim(ob_get_contents(), ' <br/>');
	ob_end_clean();

	if (($return !== false) && ($warnings == '')) {
		$method = 'PHP file*';
		return $return;
	}
	return false;
}

function fileGetContents($path, &$warnings, $timeout = -1, &$method) {
	$return = fileGetContentsCURL($path, false, $warnings, $timeout, $method);
	if ($return === false) {
		$return = fileGetContentsCURL($path, true, $warnings, $timeout, $method);
	}
	if ($return === false) {
		$return = fileGetContentsPHP($path, $warnings, $timeout, $method);
	}
	return $return;
}

function microtime2human($time) {
	$str = '';
	if ($time > 3600) {
		$hours = floor($time / 3600);
		$str .= $hours.'h';
		$time -= $hours * 3600;
	}
	if ($time > 60) {
		$minutes = floor($time / 60);
		$str .= ' '.$minutes.'m';
		$time -= $minutes * 60;
		round($time % 60, 2).'s';
	}
	if ($time > 1) {
		$seconds = $time % 60;
		$str .= ' '.$seconds.'s';
		$time -= $seconds;
	}
	return trim(trim($str).' '.round($time * 1000, 2).'ms');
}

class GetOxidDBConfig {
	function __construct() {
		require('config.inc.php');
	}
}

function guessMySQLVersion(&$messages) {
	$aAccess = array();

	if (file_exists('includes/configure.php')) {
		/* oscommerce and forks */
		include ('includes/configure.php');
		$aAccess = array(
			'Server' => DB_SERVER,
			'User' => DB_SERVER_USERNAME,
			'Pass' => DB_SERVER_PASSWORD,
		);
	} else if (file_exists('conf/config.php')) {
		/* veyton */
		define('_VALID_CALL', 'true');
		include('conf/config.php');
		$aAccess = array(
			'Server' => _SYSTEM_DATABASE_HOST,
			'User' => _SYSTEM_DATABASE_USER,
			'Pass' => _SYSTEM_DATABASE_PWD,
		);
	} else if (file_exists('config.php')) {
		/* shopware */
		$dbConf = include('config.php');
		$aAccess = array(
			'Server' => $dbConf['db']['host'],
			'User'   => $dbConf['db']['username'],
			'Pass'   => $dbConf['db']['password'],
		);
		if (array_key_exists('dbname', $dbConf['db'])) {
			$aAccess['DB']   = $dbConf['db']['dbname'];
		}
		if (array_key_exists('port', $dbConf['db'])) {
			$aAccess['Port'] = $dbConf['db']['port'];
		}
	} else if (file_exists('app/etc/local.xml')) {
		/* magento */
		$dbConf = simplexml_load_file('app/etc/local.xml');
		@$dbConf = $dbConf->global->resources->default_setup->connection;
		if (($dbConf === null) || !isset($dbConf->host)) {
			return false;
		}
		$aAccess = array(
			'Server' => (string)$dbConf->host,
			'User' => (string)$dbConf->username,
			'Pass' => (string)$dbConf->password,
		);
	} else if (file_exists('config/settings.inc.php')) {
		/* prestashop */
		include('config/settings.inc.php');
		$aAccess = array(
			'Server' => _DB_SERVER_,
			'User' => _DB_USER_,
			'Pass' => _DB_PASSWD_,
		);
	} else if (file_exists('config.inc.php')) {
		/* oxid */
		$dbConf = new GetOxidDBConfig();
		$aAccess = array(
			'Server' => $dbConf->dbHost,
			'User' => $dbConf->dbUser,
			'Pass' => $dbConf->dbPwd,
		);
	} else {
		$messages[] = '<p>'.LANG_CONFIGURE_NOT_FOUND.'</p>';
	}

	if (!empty($aAccess)) {
		$aRequire = array('Server', 'User', 'Pass');
		foreach ($aRequire as $sVar) {
			if (empty($aAccess[$sVar])) {
				return false;
			}
		}
		if (function_exists('mysqli_connect')) {
			if (    array_key_exists('DB', $aAccess)
			     && isset($aAccess['DB'])
			     && array_key_exists('Port', $aAccess)
			     && isset($aAccess['Port'])) {
				$rLink = mysqli_connect($aAccess['Server'], $aAccess['User'], $aAccess['Pass'], $aAccess['DB'], $aAccess['Port']);
			} else {
				$rLink = mysqli_connect($aAccess['Server'], $aAccess['User'], $aAccess['Pass']);
			}
			$aMySQLVersion = mysqli_fetch_array(mysqli_query($rLink, 'SELECT VERSION()'), MYSQL_NUM);
		} else {
			if (    array_key_exists('Port', $aAccess)
			     && isset($aAccess['Port'])) {
				mysql_connect($aAccess['Server'].':'.$aAccess['Port'], $aAccess['User'], $aAccess['Pass']);
			} else {
				mysql_connect($aAccess['Server'], $aAccess['User'], $aAccess['Pass']);
			}
			$aMySQLVersion = mysql_fetch_array(mysql_query('SELECT VERSION()'), MYSQL_NUM);
		}

		return $aMySQLVersion[0];
	}


	return false;
}

$langs = array_keys($strings);
$browserLang = lang_getfrombrowser($langs, array_shift($langs), null, false);
foreach ($strings[$browserLang] as $key => $val) {
	define('LANG_'.strtoupper($key), $val);
}

/* Testscript */
$messages = array();

/* MySQL Check */
$mySQLVersion = guessMySQLVersion($messages);
if ($mySQLVersion == false) {
	$info = phpinfo_array(true);
	$mySQLVersion = $info['mysql']['Client_API_version'];
}

$_timers = array();

$currentClientURL = 'http://api.magnalister.com/update/oscommerce/ClientVersion/get';

/* Extenal Connection Check */
$_timers['FileGetContentsMixed'] = microtime(true);
if (($localClientVersion = fileGetContents($currentClientURL, $warnings, ML_CONNECT_TIMEOUT, $method)) === false) {
	$localClientVersion = 0;
} else {
	$localClientVersion = decodeClientVersion($localClientVersion);
}
$_timers['FileGetContentsMixed'] = microtime(true) - $_timers['FileGetContentsMixed'];


/* file_get_contents Check */
$_timers['FileGetContentsPHP'] = microtime(true);
if (($localClientVersionPHP = fileGetContentsPHP($currentClientURL, $filePHPError, ML_CONNECT_TIMEOUT, $phpmethod)) === false) {
	$localClientVersionPHP = 0;
} else {
	$localClientVersionPHP = decodeClientVersion($localClientVersionPHP);
}
$_timers['FileGetContentsPHP'] = microtime(true) - $_timers['FileGetContentsPHP'];

/* cURL Check */
$_timers['FileGetContentsCURL'] = microtime(true);
$localClientVersionCURL = fileGetContentsCURL($currentClientURL, false, $fileCURLError, ML_CONNECT_TIMEOUT, $curlmethod);
if ($localClientVersionCURL === false) {
	$localClientVersionCURL = fileGetContentsCURL($currentClientURL, true, $fileCURLError, ML_CONNECT_TIMEOUT, $curlmethod);
}
if ($localClientVersionCURL !== false) {
	$localClientVersionCURL = decodeClientVersion($localClientVersionCURL);
}
$_timers['FileGetContentsCURL'] = microtime(true) - $_timers['FileGetContentsCURL'];

if (function_exists('curl_version')) {
	$cURLVersion = curl_version();
	if (!is_array($cURLVersion)) {
		$cURLVersion = explode(' ', $cURLVersion);
		$cURLVersion['version'] = $cURLVersion[0];
	}
}

$localClientVersionCURL = (int)$localClientVersionCURL;

/* TimeOut Check */
$maxExecutionTime = ini_get('max_execution_time');
if ($maxExecutionTime != '0') {
	@set_time_limit($maxExecutionTime+5);
	$newMaxExecutionTime = ini_get('max_execution_time');
}

/* RAM Check */
$maxRam = ini_get('memory_limit');
ini_set('memory_limit', '247M');
$newMaxRam = ini_get('memory_limit');
ini_set('memory_limit', $maxRam);

?>
<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>magnalister <?php echo LANG_HEADLINE; ?></title>
		<style>
body {
	font: 12px sans-serif;
	line-height: 1.5em;
}
h1 {
	border-bottom: 1px solid #999;
	padding-bottom: 0.05em;
	margin-bottom: 0.5em;
}
h2 {
	font-weight: normal;
	border-bottom: 1px dashed #aaf;
	padding-bottom: 0.05em;
	margin-bottom: 0.5em;
}
.magnatext {
	color: #DC043D;
}
.subline {
	font-style: italic;
	font-size: 120%;
	color: #999;
	margin-left: 25em;
	margin-top: -0.7em;
}
.instruction {
	font-style: italic;
}.highlight {
	color: #090;
}
ol li,
ul li.bottomSpace {
	margin-bottom: 1.5em;
}
ol li ul li {
	margin-bottom: 0em;
}
pre.sourcecode {
	border: 1px dashed #acbcff;
	padding: 4px;
	overflow-x: auto;
	line-height: 1.1em;
}
div.paddingTop {
	padding-top: 1em;
}
span.tt {
	font-family: monospace;
}
.noticeBox {
	border-width: 1px 1px 1px 5px;
	border-style: solid;
	border-color: #F00;
	padding: 5px;
	background: #FA2;
	margin: 0 0 1em 0;
	text-align: left;
}
table#systemcheck {
	border-spacing: 1px;
}
table#systemcheck td {
	padding: 3px 5px;
}
table#systemcheck td.ok,
table#systemcheck td.fail {
	font-weight: bold;
	text-align: center;
	text-shadow: 0px 0px 2px #fff;
}
table#systemcheck td.ok {
	color: #2AC800;
}
table#systemcheck td.fail {
	color: #F22800;
}
table#systemcheck tbody tr td {
	background: #f6f6f6;	
}
table#systemcheck tbody tr:nth-child(odd) td {
	background: #e8e8e8;
}
table#systemcheck tbody tr.head td {
	font-weight: bold;
	background: #d8d8d8;
}
table#systemcheck tbody tr.space td {
	font-weight: bold;
	background: #fff;
}

div.why {
	position: relative;
	color: black;
	font-weight: normal;
	font-size: 9px;
	display: inline-block;
	top: -6px;
    left: 4px;
    margin-left: -4px;
}
div.reason {
	display: none;
	position: absolute;
	border: 2px solid #999;
	background: #eee;
	width: 500px;
	padding: 5px;
	text-align: left;
}
div.why:hover div.reason {
	display: block;
}
		</style>
	</head>
	<body>
		<h1><span class="magnatext">m</span>agnalister <?php echo LANG_HEADLINE; ?></h1>
<?php
echo implode("\n", $messages);
echo '
		<table id="systemcheck">
			<tbody>
				<tr class="head">
					<td>'.LANG_MINREQUIREMENTS.'</td>
					<td>'.LANG_STATUS.'</td>
					<td>'.LANG_VERSION.'</td>
				</tr>
				<tr>
					<td>PHP Version (min. 5.0)</td>
					'.(version_compare(PHP_VERSION, '5.0.0', '<') ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>'.PHP_VERSION.'</td>
				</tr>
				<tr>
					<td>MySQL Version (min. 5.0)</td>
					'.(version_compare($mySQLVersion, '5.0.0', '<') ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>'.$mySQLVersion.'</td>
				</tr>
				<tr>
					<td>'.LANG_CONNECT_EXT_SERVER.' ('.$method.')</td>
					'.(empty($localClientVersion) ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>'.(empty($localClientVersion) ? '&mdash;' : microtime2human($_timers['FileGetContentsMixed'])).'</td>
				</tr>
				<tr class="space">
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr class="head">
					<td>'.LANG_OPTIMAL_SUPPORT.'</td>
					<td>'.LANG_STATUS.'</td>
					<td>'.LANG_VERSION.'</td>
				</tr>
				<tr>
					<td>'.LANG_CURL_INSTALLED.'</td>
					'.(!function_exists('curl_version') ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>'.(!function_exists('curl_version') ? '&mdash;' : $cURLVersion['version']).'</td>
				</tr>
				'.(function_exists('curl_version') ? '
				<tr>
					<td>'.LANG_CONNECT_EXT_SERVER_CURL.' ('.((strpos($curlmethod, LANG_WITHOUT) === false) ? LANG_WITH : LANG_WITHOUT).' SSL)</td>
					'.(empty($localClientVersionCURL) ? '<td class="fail">X<div class="why">?<div class="reason">'.$fileCURLError.'</div></div></td>' : '<td class="ok">OK</td>').'
					<td>'.(empty($localClientVersionCURL) ? '&mdash;' : microtime2human($_timers['FileGetContentsCURL'])).'</td>
				</tr>' : '').'
				<tr>
					<td>'.LANG_CONNECT_EXT_SERVER_FGC.'</td>
					'.(empty($localClientVersionPHP) 
						? '<td class="fail">X'.(!empty($filePHPError) 
								? '<div class="why">?<div class="reason">'.$filePHPError.'</div></div>' 
								: ''
							).'</td>'
						: '<td class="ok">OK</td>'
					).'
					<td>'.(empty($localClientVersionPHP) ? '&mdash;' : microtime2human($_timers['FileGetContentsPHP'])).'</td>
				</tr>
				<tr>
					<td>'.LANG_PHP_SAFE_MODE_DISABLED.' (<a href="http://de.php.net/manual/de/features.safe-mode.php" target="_blank" title="Info Safe Mode">?</a>)</td>
					'.(ini_get('safe_mode') ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>&mdash;</td>
				</tr>
				<tr>
					<td>'.LANG_PHP_MAGIC_QUOTES_DISABLED.' (<a href="http://de.php.net/manual/de/security.magicquotes.php" target="_blank" title="Info Magic Quotes">?</a>)</td>
					'.((get_magic_quotes_gpc() != 0) ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>&mdash;</td>
				</tr>
				<tr>
					<td>'.sprintf(LANG_MAX_EXECUTION_TIME, $maxExecutionTime).'</td>
					'.((($maxExecutionTime != '0') && ($maxExecutionTime == $newMaxExecutionTime)) ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>&mdash;</td>
				</tr>
				<tr>
					<td>'.sprintf(LANG_MAX_RAM, $maxRam).'</td>
					'.(($newMaxRam == $maxRam) ? '<td class="fail">X</td>' : '<td class="ok">OK</td>').'
					<td>&mdash;</td>
				</tr>				
			</tbody>
		</table>';
?>
	</body>
</html>
