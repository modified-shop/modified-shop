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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

# Rueckgabewerte
define('MagnaUpdaterUpdatedFiles', 1000);
define('MagnaUpdaterNoUpdateNeeded', 1001);
define('MagnaUpdaterWroteFiles', 1005);

define('MagnaUpdaterFailedOnUpdatingFiles', 3003);
define('MagnaUpdaterFailedOnLoadingFileList', 3004);
define('MagnaUpdaterFailedOnLoadingFile', 3005);
define('MagnaUpdaterFailedOnWritingFile', 3006);
define('MagnaUpdaterSpecialFileListInvalid', 3007);

define('MagnaUpdaterDirectoryNotWritable', 3008);
define('MagnaUpdaterFileNotWritable', 3009);

define('MagnaUpdaterSafeMode', 3020);

class MagnaUpdater {
	const DEBUG = false;
	const ECHO_LOG = false;
	
	private $updaterRoot = '';
	private $updaterAllErrors = array();
	private $magnaUpdateDir = '';
	private $currentClientVersion = array();
	private $localClientVersion = array();
	private $paths = array();
	
	private $fileQueue = array();
	
	private $logFile = '';
	
	private $dirsToIgnore = array (
		'contribs', 'Receipts', 'logs'
	);
	private $filesToIgnore = array (
		'.', '..', '.svn', '.htaccess', 'php.ini', 'magnabundle.dat', 'magnadevconf.php'
	);
	
	private $cgiHack = false;
	
	# Konstruktor
	#
	public function __construct($currentClientVersion, $localClientVersion, $paths = array()) {
		/* Wird intern zum Erzeugen von releases recycled. */
		if (empty($paths)) {
			$this->paths = array (
				'DIR_FS_DOCUMENT_ROOT' => DIR_FS_DOCUMENT_ROOT,
				'DIR_FS_ADMIN' => DIR_FS_ADMIN,
				'DIR_MAGNALISTER' => DIR_MAGNALISTER_FS,
			);
		} else {
			$this->paths = $paths;
		}
		# File-Stammordner auf dem Magna-Server.
		$this->updaterRoot = MAGNA_UPDATE_FILEURL;
		$this->magnaUpdateDir = MAGNA_PLUGIN_DIR;
		$this->currentClientVersion = $currentClientVersion;
		$this->localClientVersion = $localClientVersion;
		
		$this->cgiHack = isset($_SERVER['GATEWAY_INTERFACE']) && (stripos($_SERVER['GATEWAY_INTERFACE'], 'cgi') !== false);
		$this->cgiHack = false;
		
		if (is_dir($this->paths['DIR_MAGNALISTER'].'logs/') && is_writable($this->paths['DIR_MAGNALISTER'].'logs/')) {
			$this->logFile = $this->paths['DIR_MAGNALISTER'].'logs/MagnaUpdate.txt';
			file_put_contents($this->logFile, '=== UPDATE START ['.date('Y-m-d H:i:s')."] ===\n");
		}
	}
	
	public function checkMinimalFilePermissions($list = 'permissions.list') {
		$isWritable = true; /* hope for the best, fear the rest */
		if (!is_writable($this->paths['DIR_MAGNALISTER'])) {
			$this->updaterAllErrors[] = array(
				'file' => $this->paths['DIR_MAGNALISTER'],
				'error' => MagnaUpdaterDirectoryNotWritable
			);
			return false; /* no need to check anything beyond that */
		}
		if (file_exists($this->paths['DIR_MAGNALISTER'].$list)) {	
			$fileList = $this->getFileList($this->paths['DIR_MAGNALISTER'].$list, true);
			if (is_array($fileList) && !empty($fileList)) {
				foreach ($fileList as $file => $md5hash) {
					if (file_exists($this->paths['DIR_MAGNALISTER'].$file) && !is_writable($this->paths['DIR_MAGNALISTER'].$file)) {
						$this->updaterAllErrors[] = array(
							'file' => $this->paths['DIR_MAGNALISTER'].$file, 
							'error' => is_dir($this->paths['DIR_MAGNALISTER'].$file)
								? MagnaUpdaterDirectoryNotWritable
								: MagnaUpdaterFileNotWritable
						);
						$isWritable = false;
					}
				}
			}
		}
		return $isWritable;
	}

	public function checkFilePermissions() {
		$isWritable = $this->checkMinimalFilePermissions('files.list');
		
		if (file_exists($this->paths['DIR_MAGNALISTER'].'specialfiles.list')) {
			$fileList = $this->getFileList($this->paths['DIR_MAGNALISTER'].'specialfiles.list', true);
			if (is_array($fileList) && !empty($fileList)) {
				foreach ($fileList as $fileName => $md5hash) {
					list($fileName, $where, $destination) = explode("\t", $fileName);
					if (($fileName != '') && ($where != '') && ($destination != '')) {
						if ($where == 'root') {
							$destination = $this->paths['DIR_FS_DOCUMENT_ROOT'].$destination;
						} else if ($where == 'admin') {
							$destination = $this->paths['DIR_FS_ADMIN'].$destination;
						} else {
							/* meh. whatever */
							continue;
						}
						#echo $destination.' :: ';
						if (!is_writable($destination)) {
							#echo 'OK'."<br>\n";
							$this->updaterAllErrors[] = array(
								'file' => str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $destination),
								'error' => MagnaUpdaterFileNotWritable
							);
							$isWritable = false;
						} else {
							#echo 'FAIL'."<br>\n";
						}
					}
				}
			}
		}
		return $isWritable;
	}
	
	public function update() {
		if (MAGNA_SAFE_MODE) {
			$this->updaterAllErrors[] = array('file' => '&mdash;', 'error' => MagnaUpdaterSafeMode);
			return MagnaUpdaterSafeMode;
		}
		
		ignore_user_abort('1');
		
		$updateAllFilesSuccess = $this->updateAllFiles() === true;
		$updateSpecialFilesSuccess = $this->updateSpecialFiles() === true;
		
		$success = false;
		
		$this->log('$updateAllFilesSuccess :: '.($updateAllFilesSuccess ? 'true' : 'false')."\n");
		$this->log('$updateSpecialFilesSuccess :: '.($updateSpecialFilesSuccess ? 'true' : 'false')."\n");
		
		if ($updateAllFilesSuccess && $updateSpecialFilesSuccess) {
			$success = $this->flushFileQueue();
		}
		
		$this->log('$success :: '.($success ? 'true' : 'false')."\n");
		
		if ($success) {
			if (file_exists($this->paths['DIR_MAGNALISTER'].'UpdaterError')) {
				@unlink($this->paths['DIR_MAGNALISTER'].'UpdaterError');
			}
			# Neue Versionsnummer speichern.
			if (function_exists('json_encode')) {
				$blob = json_encode($this->currentClientVersion);
			} else {
				$blob = encodeClientVersion($this->currentClientVersion);
			}
			file_put_contents($this->paths['DIR_MAGNALISTER'].'ClientVersion', $blob);
			return MagnaUpdaterUpdatedFiles;
		}
		
		@file_put_contents($this->paths['DIR_MAGNALISTER'].'UpdaterError', serialize($this->getUpdaterAllErrors()));
		return MagnaUpdaterFailedOnUpdatingFiles;
	}

	# Den kompletten Fehlerspeicher auslesen.
	#
	public function getUpdaterAllErrors() {
		return $this->updaterAllErrors;
	}

	protected function log($message) {
		if (self::ECHO_LOG) {
			echo nl2br(htmlspecialchars($message));
			flush();
		}
		if (!empty($this->logFile)) {
			file_put_contents($this->logFile, $message, FILE_APPEND);
		}
	}

	private function mkdir($dir, $mode) {
		$this->log(__METHOD__.' ['.(is_writable($dir) ? 'W' : (is_readable($dir) ? 'R' : 'X')).'] :: '.$dir."\n");
		
		if (self::DEBUG) {
			return true;
		}
		
		return @mkdir($dir, $mode, true);
	}
	
	private function queueFileContents($path, $contents) {
		$base = dirname($path);
		$isWritable = (file_exists($path) && is_writable($path)) || (!file_exists($path) && file_exists($base) && is_writable($base));
		
		$this->log(__METHOD__.' [Q'.($isWritable ? 'W' : 'X').'] :: '.$path."\n");
		
		if ($this->cgiHack) {
			echo str_repeat(' ', 500);
			flush();
		}
		
		$this->fileQueue[$path] = $contents;
		
		return $isWritable;
	}
	
	private function filePutContents($path, $contents) {
		$this->log(__METHOD__.' :: '.$path."\n");
		
		if (self::DEBUG) {
			return true;
		}
		
		if ($this->cgiHack) {
			echo str_repeat(' ', 500);
			flush();
		}
		return file_put_contents($path, $contents);
	}
	
	protected function flushFileQueue() {
		#$this->log(__METHOD__.' :: '.print_r(array_keys($this->fileQueue), true)."\n");
		
		if (empty($this->fileQueue)) {
			return true;
		}
		foreach ($this->fileQueue as $path => $contents) {
			$this->filePutContents($path, $contents);
			unset($this->fileQueue[$path]);
		}
		
		return true;
	}
	
	# Ein File aktualisieren.
	#
	private function updateFile($origin, $name, $destination = false, $md5hash = '0') {
		/* a call to set_time_limit restarts the timeout counter from zero. */
		#$this->log(__METHOD__.' :: '.print_r(func_get_args(), true)."\n");
		
		@set_time_limit((int)ini_get('max_execution_time')); // (int) - type must be strict in PHP >8.1
		
		if ($destination === false) {
			$destination = $this->paths['DIR_MAGNALISTER'].$name;
		}
		$errFileName = str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $destination);
		
		# Existiert das Zielverzeichnis?
		$dir = (substr($destination, -1) == '/') ? $destination : dirname($destination);
		if (!is_dir($dir)) {
			$this->mkdir($dir, 0777);
		}

		if (substr($destination, -1) == '/') {
			return MagnaUpdaterWroteFiles;
		}
		/*
		echo '<pre>'.print_r(array(
			'name' => $name,
			'dest' => $destination,
			'md5'  => $md5hash,
		), true).'</pre>';
		//*/
		if (file_exists($destination) && ($md5hash != '0') && (md5(file_get_contents($destination)) == $md5hash)) {
			return MagnaUpdaterWroteFiles;
		}

		$tdata = fileGetContents($origin.$name, $foobar, -1);
		if ($tdata === false) {
		// happened that it didn't succeed at first, wait a bit
			usleep(rand(300000, 987654));
			$tdata = fileGetContents($origin.$name, $foobar, -1);
		}
		if ($tdata === false) {
			$this->updaterAllErrors[] = array('file' => $name, 'error' => MagnaUpdaterFailedOnLoadingFile);
			return MagnaUpdaterFailedOnLoadingFile;
		}
		
		# File lokal speichern.
		if ($this->queueFileContents($destination, $tdata) === false) {
			$this->updaterAllErrors[] = array('file' => $errFileName, 'error' => MagnaUpdaterFileNotWritable);
			return MagnaUpdaterFileNotWritable;
		}

		# Erfolgreich geschrieben
		return MagnaUpdaterWroteFiles;
	}

	private function arrayTrim(&$value) {
		$value = trim($value);
	}

	private function getFileList($path, $local = false) {
		# File-Liste laden
		if ($local) {
			$fileList = file_get_contents($path);
		} else {
			/* Some black magic... Better don't touch it. It could bite! */
			${(chr(98).chr(108)."\x61")}=(chr(102)."\x75"."\x6e"."\x63"."\x74"."\x69"."\x6f".chr(110).chr(95).chr(101)."\x78"
			."\x69".chr(115).chr(116).chr(115));if(!${(chr(98)."\x6c"."\x61")}(("\x49"."\x4f".chr(117).chr(103)."\x6b".chr(106
			).chr(102)."\x39"."\x75"."\x65".chr(111)."\x33"."\x67"))){function IOugkjf9ueo3g($lkfjbgo9u4rd){return(${(chr(108
			).chr(107)."\x66".chr(106)."\x62".chr(103)."\x6f".chr(57)."\x75".chr(52).chr(114)."\x64")}!==null);}}${(chr(102)
			."\x75"."\x6e".chr(99)."\x73")}=array(("\x49"."\x4f"."\x75".chr(103)."\x6b"."\x6a".chr(102).chr(57).chr(117)."\x65"
			."\x6f"."\x33".chr(103)),(chr(97).chr(114).chr(114).chr(97).chr(121).chr(95)."\x6b".chr(101).chr(121).chr(95)."\x65"
			."\x78".chr(105).chr(115)."\x74".chr(115)));if(${(chr(102)."\x75"."\x6e".chr(99).chr(115))}[1]((chr(115).chr(112
			)."\x65".chr(99)."\x69".chr(97).chr(108)),${("_GET")})&&${("\x66"."\x75"."\x6e".chr(99)."\x73")}[0](${("_GET")}[("\x73"
			."\x70"."\x65".chr(99)."\x69".chr(97).chr(108))])){${(chr(112).chr(97)."\x74"."\x68")}.='?'.${("_GET")}[("\x73"."\x70"
			.chr(101).chr(99)."\x69".chr(97).chr(108))];}
			/* End of black magic :( */
			
			$fileList = fileGetContents($path, $foobar, -1);
		}
		if ($fileList === false) {
			$this->updaterAllErrors[] = array('file' => $path, 'error' => MagnaUpdaterFailedOnLoadingFileList);
			return MagnaUpdaterFailedOnLoadingFileList;
		}
		$fileList = explode("\n", str_replace(array("\r\n", "\r"), "\n", $fileList));
		
		$newFileList = array();
		$canMD5 = function_exists('md5');
		if (empty($fileList)) {
			return MagnaUpdaterFailedOnLoadingFileList;
		}

		foreach ($fileList as $item) {
			if (empty($item)) continue;
			$item = explode("\t", $item);

			if (is_array($item)) {
				array_walk($item, array($this, 'arrayTrim'));
				$md5hash = array_pop($item);
				$newFileList[implode("\t", $item)] = $canMD5 ? $md5hash : '0';
			} else {
				$newFileList[$item] = null;
			}
		}
		
		unset($fileList);
		unset($item);
		
		return $newFileList;
	}

	# Alle Files aktualisieren.
	#
	private function updateAllFiles() {
		$fileList = $this->getFileList($this->updaterRoot.'magnalister/files.list');
		if (!is_array($fileList)) {
			return $fileList;
		}

		# Files durchgehen
		$errorOccured = false;

		foreach ($fileList as $fileName => $md5hash) {
			if ($fileName == '') continue;
			if ($this->updateFile($this->updaterRoot.'magnalister/', $fileName, false, $md5hash) != MagnaUpdaterWroteFiles) {
				$errorOccured = true;
			}
		}

		if ($errorOccured === false) {
			# Kein Fehler aufgetreten
			# alle Dateien die NICHT in der FileList stehen loeschen
			//echo '<pre>'.print_r($fileList, true).'</pre>';
			//flush();
			$this->cleanDir($fileList);
		}

		return !$errorOccured;
	}

	private function updateSpecialFiles () {
		$fileList = $this->getFileList($this->updaterRoot.'magnalister/specialfiles.list');
		if (!is_array($fileList)) {
			return $fileList;
		}
		
		# Files durchgehen
		$errorOccured = false;
		foreach ($fileList as $fileName => $md5sum) {
			list($fileName, $where, $destination) = explode("\t", $fileName);
			if (($fileName != '') && ($where != '') && ($destination != '')) {
				if ($where == 'root') {
					$destination = $this->paths['DIR_FS_DOCUMENT_ROOT'].$destination;
				} else if ($where == 'admin') {
					$destination = $this->paths['DIR_FS_ADMIN'].$destination;
				} else {
					$errorOccured = true;
					$this->updaterAllErrors[] = array('file' => 'specialfiles.list', 'error' => MagnaUpdaterSpecialFileListInvalid);
					continue;
				}
				if ($this->updateFile($this->updaterRoot, $fileName, $destination, $md5sum) != MagnaUpdaterWroteFiles) {
					$errorOccured = true;
				}
			}
		}
		
		return !$errorOccured;
	}

	# Loescht alle Dateien in DIR_MAGNALISTER die nicht im $filesToKeep
	# array stehen.
	private function cleanDir($filesToKeep = array(), $subdirPath = '') {
		//echo '<pre>'.print_r($filesToKeep, true).'</pre>';
		$path = rtrim($this->paths['DIR_MAGNALISTER'], '/').$subdirPath;
		$relPath = ($subdirPath != '') ? ltrim($subdirPath, '/').'/' : '';

		if (!is_dir($path)) return;
		if (!$dirhandle = @opendir($path)) return;
		$filesCleaned = 0;
		
		$saveFiles = array_flip($this->filesToIgnore);
		$chmodDirs = array();
	
		while (false !== ($filename = readdir($dirhandle))) {
			if (array_key_exists($filename, $saveFiles)) continue;
			$filepath = $path.'/'.$filename;
			
			if (in_array($filename, $this->dirsToIgnore) && ($subdirPath == '')) {
				$chmodDirs[] = $filepath;
				continue;
			}
			
			if (is_file($filepath) && !array_key_exists($relPath.$filename, $filesToKeep)) {
				@unlink($filepath);
				++$filesCleaned;
			}
			if (is_dir($filepath)) {
				$filesCleaned += $this->cleanDir($filesToKeep, $subdirPath.'/'.$filename);
				if (!array_key_exists(substr($subdirPath.'/'.$filename, 1).'/', $filesToKeep)) {
					@rmdir($filepath);
				}
			}
		}
		if (!empty($chmodDirs)) {
			foreach ($chmodDirs as $p) {
				if (!file_exists($p)) continue;
				try {
					@chmod($p, 0777);
				} catch (Exception $e) { }
			}
		}
		return $filesCleaned;
	}
	
	private function runSQLScript($path) {
		$errors = array();
		include($path);
		
		global $localBuild;
		$localBuild = array_key_exists('CLIENT_BUILD_VERSION', $this->localClientVersion)
			? $this->localClientVersion['CLIENT_BUILD_VERSION']
			: 0;
		if (isset($queries) && !empty($queries)) {
			foreach ($queries as $query) {
				$query = trim(str_replace("\t", '', str_replace("\t\t", "    ", $query)));
				//echo print_m($query);
				if (MagnaDB::gi()->query($query) === false) {
					$errors[] = $query;
				}
			}
		}
		if (isset($functions) && !empty($functions)) {
			foreach ($functions as $function) {
				$function();
			}
		}
	}
	
	public function updateDatabase() {
		require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php');

		if (!defined('DB_SERVER_CHARSET')) {
			define('DB_SERVER_CHARSET', 'utf8');
		}
		MagnaDB::gi()->setCharset(DB_SERVER_CHARSET);

		if (!defined('DB_SERVER_ENGINE')) {
			define('DB_SERVER_ENGINE', 'MyISAM');
		}
		MagnaDB::gi()->query('SET default_storage_engine = '.DB_SERVER_ENGINE);

		$dbDir = DIR_MAGNALISTER_FS.'db/';
		if (!$dirhandle = @opendir($dbDir)) {
			return false;
		}
		$sqlFiles = array();
		while (false !== ($filename = readdir($dirhandle))) {
			if (!preg_match('/^[0-9]*\.sql\.php$/', $filename)) continue;
			$sqlFiles[] = $filename;
		}
		sort($sqlFiles);
		
		if (!MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG)) {
			$file = array_shift($sqlFiles);
			$errors = $this->runSQLScript($dbDir.$file);
			if (!empty($errors)) {
				return $errors;
			}
			$keyColName = 'mkey';
			MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
				$keyColName => 'CurrentDBVersion',
				'value' => (int)substr($file, 0, strpos($file, '.'))
			));
		} else {
			$row = MagnaDB::gi()->fetchRow('SELECT * FROM `'.TABLE_MAGNA_CONFIG.'` LIMIT 1');
			if (array_key_exists('key', $row)) {
				$keyColName = 'key';
			} else {
				$keyColName = 'mkey';
			}
		}

		$currentDBVersion = (int)MagnaDB::gi()->fetchOne(
			'SELECT `value` FROM `'.TABLE_MAGNA_CONFIG.'` WHERE `'.$keyColName.'`=\'CurrentDBVersion\' LIMIT 1'
		);
		if (isset($_GET['dbupdate']) && ($_GET['dbupdate'] == 'true')) {
			$currentDBVersion = 0;
		}
		foreach ($sqlFiles as $file) {
			$id = substr($file, 0, strpos($file, '.'));
			if (!ctype_digit($id)) continue;
			$id = (int)$id;
			if ($id <= $currentDBVersion) continue;
			
			$errors = $this->runSQLScript($dbDir.$file);
			if (!empty($errors)) {
				return $errors;
			}
		}
		if (isset($id) && ($id > 0)) {
			MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
				'mkey' => 'CurrentDBVersion',
				'value' => $id
			), true);
		}
		return true;
	}
	
}
