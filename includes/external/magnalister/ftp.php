<?php
class FTPConnect {
	private $access = array(
		'host' => null,
		'port' => null,
		'user' => null,
		'pass' => null,
		'timeout' => 30,
	);
	private $ftpConn = null;
	private $connected = false;
	private $systype = '';
	private $lastError = '';
	private $showErrors;
	
	private $forbiddenFilesLocal = array (
		'.', '..', '.svn'
	);
	
	public function __construct($host, $port, $user, $pass, $timeout = 30, $passive = false, $showErrors = false) {
		$this->access['host'] = $host;
		$this->access['port'] = $port;
		$this->access['user'] = $user;
		$this->access['pass'] = $pass;
		$this->access['timeout'] = $timeout;
		
		$this->showErrors = $showErrors;

		$this->ftpConn = @ftp_connect($this->access['host'], $this->access['port'], $this->access['timeout']);
		if (!is_resource($this->ftpConn)) {
			$this->lastError = __METHOD__.'(): Connection to host '.$this->access['host'].':'.$this->access['port'].' failed';
			$this->triggerError($this->lastError);
			return;
		}
		if (!@ftp_login($this->ftpConn, $this->access['user'], $this->access['pass'])) {
			$this->lastError = __METHOD__.'(): Login for user '.$this->access['user'].' failed';
			$this->triggerError($this->lastError);
			return;
		}
		$this->connected = true;
		$this->systype = ftp_systype($this->ftpConn);
		
		ftp_pasv($this->ftpConn, $passive);
		//$mlsd = ftp_raw($this->ftpConn, 'NLST');
		//echo print_m($mlsd);

		$features = ftp_raw($this->ftpConn, 'FEAT');
		//echo print_m($features);
	}
	
	public function __destruct() {
		if ($this->isConnected()) {
			ftp_close($this->ftpConn);
		}
	}

	private function triggerError($message) {
		if ($this->showErrors) {
			trigger_error($message);
		}
	}
	
	public function isConnected() {
		return $this->connected;
	}
	
	public function getLastError() {
		return $this->lastError;
	}
	
	public function getSystype() {
		return $this->systype;
	}
	
	public function pasv($pasv = true) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}		
		return @ftp_pasv($this->ftpConn, $pasv);
	}
	
	public function cd($path) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		return ftp_chdir($this->ftpConn, $path);
	}
	
	public function getNList() {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		return ftp_nlist($this->ftpConn, '.');
	}

	public static function convertPermissions($permissions) {
		$mode = 0;
		
		if ($permissions[1] == 'r') $mode += 0400;
		if ($permissions[2] == 'w') $mode += 0200;
		if ($permissions[3] == 'x') $mode += 0100;
		else if ($permissions[3] == 's') $mode += 04100;
		else if ($permissions[3] == 'S') $mode += 04000;
		
		if ($permissions[4] == 'r') $mode += 040;
		if ($permissions[5] == 'w') $mode += 020;
		if ($permissions[6] == 'x') $mode += 010;
		else if ($permissions[6] == 's') $mode += 02010;
		else if ($permissions[6] == 'S') $mode += 02000;
		
		if ($permissions[7] == 'r') $mode += 04;
		if ($permissions[8] == 'w') $mode += 02;
		if ($permissions[9] == 'x') $mode += 01;
		else if ($permissions[9] == 't') $mode += 01001;
		else if ($permissions[9] == 'T') $mode += 01000;
		
		return $mode;
	}

	public function getList($path = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		$pwd = $this->pwd();
		if ($path && !$this->cd($path)) {
			$this->cd($pwd);
			$this->lastError = __METHOD__.'(): Cannot change directory to '.$path.'.';
			$this->triggerError($this->lastError);
			return false;
		}
		$rList = ftp_rawlist($this->ftpConn, '-al');
		//echo var_dump_pre($rList, '$rList');
		$this->cd($pwd);
		if (empty($rList)) {
			return array();
		}
		
		$result = array();
		foreach ($rList as $entry) {
			$entry = preg_split('/[\s]+/', $entry);
			
			switch (substr($entry[0], 0, 1)) {
				case 'd': {
					$type = 'dir';
					break;
				}
				case 'l': {
					$type = 'link';
					break;
				}
				case '-': {
					$type = 'file';
					break;
				}
				default: {
					$type = 'unkown ['.substr($entry[0], 0, 1).']';
				}
			}
			$result[$entry[8]] = array(
				'type' => $type,
				'permissions' => self::convertPermissions($entry[0]),
				'user' => $entry[2],
				'group' => $entry[3],
				'size' =>  $entry[4],
				'lastmod' => strtotime($entry[5].' '.$entry[6].' '.$entry[7]),
				'filename' => $entry[8],
				'linkto' => isset($entry[10]) ? $entry[10] : false
			);
			
		}
		return $result;
	}
	
	public function pwd() {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		return ftp_pwd($this->ftpConn);
	}
	
	public function fileExists($path) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($path)) {
			$this->lastError = __METHOD__.'(): Parameter $path may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$ret = false;
		
		$pwd = $this->pwd();
		if (!$this->cd(dirname($path))) {
			$this->cd($pwd);
			return false;
		}
		$dirlist = $this->getNList();
		$fname = rtrim(basename($path), '/');
		if (in_array('./'.$fname, $dirlist)) {
			$ret = true;
		}
		$this->cd($pwd);
		return $ret;
	}
	
	function getDirContentsLocal($path = '.', $subpath = '') {
		if (!is_dir($path)) {
			$this->lastError = __METHOD__.'(): $path is not a valid directory.';
			$this->triggerError($this->lastError);
			return false;
		}
		$files = array();
		$handle = opendir($path);
		
		while (false !== ($file = readdir($handle))) {
			if (in_array($file, $this->forbiddenFilesLocal)) continue;
			
			if (is_dir($path.'/'.$file)) {
				$subdirfiles = $this->getDirContentsLocal($path.'/'.$file, $subpath.$file.'/');
				if (empty($subdirfiles)) {
					$subdirfiles[] = $subpath.$file.'/';
				}
				$files = array_merge($files, $subdirfiles);
			} else {
				$files[] = $subpath.$file;
			}
		}
		return $files;
	}

	function getDirContentsRemote($path = '', $pwdO = '') {
		$files = array();
		$pwd = $this->pwd();
		if ($pwdO == '') {
			$pwdO = $pwd.'/'.$path.'/';
		}
		$pwdP = str_replace($pwdO, '', $pwd.'/'.$path.'/');
		if (!empty($path) && !$this->cd($path)) {
			$this->cd($pwd);
			return false;
		}

		$ls = $this->getList($path);
		if (empty($ls)) {
			$this->cd($pwd);
			return $files;
		}
		foreach ($ls as $file) {
			if (in_array($file['filename'], $this->forbiddenFilesLocal)) continue;
			if ($file['type'] == 'dir') {
				$files[] = $pwdP.$file['filename'].'/';
				$files = array_merge($files, $this->getDirContentsRemote($file['filename'], $pwdO));
			} else {
				$files[] = $pwdP.$file['filename'];
			}
		}
		$this->cd($pwd);
		return $files;
	}

	public function isDir($fname) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($fname)) {
			$this->lastError = __METHOD__.'(): Parameter $fname may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$pwd = $this->pwd();
		if ($this->cd($fname)) {
			$this->cd($pwd);
			return true;
		}
		$this->cd($pwd);
		return false;
	}
	
	public function makeDir($path, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($path)) {
			$this->lastError = __METHOD__.'(): Parameter $path may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$pwd = $this->pwd();
		//echo $path."\n";
		$path = explode('/', rtrim($path, '/'));
		foreach ($path as $dir) {
			if (!$this->cd($dir)) {
				if (ftp_mkdir($this->ftpConn, $dir) === false) {
					$this->cd($pwd);
					return false;
				}
				if ($mode !== false) {
					@ftp_chmod($this->ftpConn, $mode, $dir);
				}
				$this->cd($dir);
			}
		}
		$this->cd($pwd);
		return true;
	}
	
	public function uploadFile($localFile, $remoteFile, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (!file_exists($localFile) || is_dir($localFile)) {
			$this->lastError = __METHOD__.'(): parameter $localFile does not point to a file.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($remoteFile)) {
			$this->lastError = __METHOD__.'(): parameter $remoteFile may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}

		$pwd = $this->pwd();
		
		$remoteDir = dirname($remoteFile);
		$remoteFile = basename($remoteFile);

		if (($remoteDir != '.') && (
				$this->isDir($remoteDir) || ($this->isDir($remoteDir) && $this->makeDir($remoteDir, $mode))
			) && !$this->cd($remoteDir)
		) {
			$this->lastError = __METHOD__.'(): Cannot open directory '.$remoteDir.'.';
			$this->triggerError($this->lastError);
			$this->cd($pwd);
			return false;
		}
		$ret = ftp_put($this->ftpConn, $remoteFile, $localFile, FTP_BINARY);
		if (($mode !== false) && $ret) {
			@ftp_chmod($this->ftpConn, $remoteFile, $fname);
		}
		$this->cd($pwd);
		return $ret;
	}
	
	public function uploadFileContents($contents, $remoteFile, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($remoteFile)) {
			$this->lastError = __METHOD__.'(): parameter $remoteFile may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}

		$pwd = $this->pwd();
		
		$remoteDir = dirname($remoteFile);
		$remoteFile = basename($remoteFile);
		
		if (($remoteDir != '.') && (
				$this->isDir($remoteDir) || ($this->isDir($remoteDir) && $this->makeDir($remoteDir, $mode))
			) && !$this->cd($remoteDir)
		) {
			$this->lastError = __METHOD__.'(): Cannot open directory '.$remoteDir.'.';
			$this->triggerError($this->lastError);
			$this->cd($pwd);
			return false;
		}
		
		$tempHandle = fopen('php://temp', 'r+');
		fwrite($tempHandle, $contents);
		rewind($tempHandle);
		
		$ret = ftp_fput($this->ftpConn, $remoteFile, $tempHandle, FTP_BINARY);
		if (($mode !== false) && $ret) {
			@ftp_chmod($this->ftpConn, $remoteFile, $fname);
		}
		$this->cd($pwd);
		return $ret;
	}
	
	public function uploadDir($localDir, $remoteDir, $mode = false, &$errors) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (!is_dir($localDir)) {
			$this->lastError = __METHOD__.'(): parameter $localDir does not point to a directory.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($remoteDir)) {
			$this->lastError = __METHOD__.'(): parameter $remoteDir may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		
		$pwd = $this->pwd();
		//echo 'r :: '.$remoteDir."\nl :: ".$localDir."\n\n";
		$files = $this->getDirContentsLocal($localDir);

		if (empty($files)) {
			return true;
		}
		if (!$this->cd($remoteDir)) {
			$this->cd($pwd);
			return false;
		}
		
		$workingdir = $this->pwd();
		if ($remoteDir[0] == '/') {
			$remoteDir = '';
		}
		
		$errors = array();
		$currentDir = '';
		foreach ($files as $file) {
			if ($currentDir != dirname($file)) {
				$currentDir = dirname($file);
				$this->cd($workingdir.'/'.$remoteDir);
				if (!$this->makeDir($currentDir, $mode) || !@$this->cd($currentDir)) {
					$errors[] = 'Cannot open directory '.$currentDir.'.';
					$currentDir = '';
					$this->cd($workingdir.'/'.$remoteDir);
					continue;
				}
			}
			$localFile = $localDir.$file;
			//echo $localFile."\n";
			$fname = basename($file);
			
			if (is_dir($localFile)) {
				if (!$this->makeDir($fname, $mode)) {
					$errors[] = 'Cannot create directory '.$file.'.';
					$this->cd($workingdir);
					continue;
				}
			} else {
				if (!$this->uploadFile($localFile, $fname)) {
					$errors[] = 'Cannot create file '.$file.'.';
					$this->cd($workingdir);
					continue;
				}
			}
			if ($mode !== false) {
				@ftp_chmod($this->ftpConn, $mode, $fname);
			}
		}
		$this->cd($pwd);
		return empty($errors) ? true : $errors;
	}
	
	public function upload($fileList, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (!is_array($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList has to be an array.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		
		$pwd = $this->pwd();
		$errors = array();
		foreach ($fileList as $file) {
			if (!file_exists($file['local'])) {
				$errors[] = 'Cannot find source '.$file['local'].'.';
				continue;
			}
			$dirname = dirname($file['remote']);
			if (($dirname != '.') && (!$this->makeDir($dirname, $mode) || !@$this->cd($dirname))) {
				$errors[] = 'Cannot open directory '.$dirname.'.';
				$this->cd($pwd);
				continue;
			}

			$fname = basename($file['remote']);
			if (is_dir($file['local'])) {
				if (!$this->makeDir($fname, $mode)) {
					$errors[] = 'Cannot create directory '.$file['remote'].'.';
					$this->cd($pwd);
					continue;
				}
				
				if ($this->uploadDir($file['local'], $fname, $mode) !== true) {
					$errors[] = 'Cannot upload contents of directory '.$file['remote'].'.';
				}
			} else {
				if (!$this->uploadFile($file['local'], $fname)) {
					$errors[] = 'Cannot create file '.$file['remote'].'.';
					$this->cd($pwd);
					continue;
				}
			}

			$mode = (isset($file['mode']) ? $file['mode'] : (!$mode ? $mode : false));
			if ($mode !== false) {
				@ftp_chmod($this->ftpConn, $mode, $fname);
			}

			$this->cd($pwd);
		}
		return empty($errors) ? true : $errors;
	}
	
	public function downloadFile($remoteFile, $localFile, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($remoteFile)) {
			$this->lastError = __METHOD__.'(): parameter $remoteFile may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($localFile)) {
			$this->lastError = __METHOD__.'(): parameter $localFile may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$localDir = dirname($localFile);
		
		if (!is_dir($localDir) && !mkdir($localDir, ((($mode !== false) ? $mode : 0777) | 0111), true)) {
			$this->lastError = __METHOD__.'(): Cannot create local dir '.$localDir.'.';
			$this->triggerError($this->lastError);
			return false;			
		}
		
		$pwd = $this->pwd();
		$remoteDir = dirname($remoteFile);
		$remoteFile = basename($remoteFile);
		
		if (($remoteDir != '.') && !$this->cd($remoteDir)) {
			$this->lastError = __METHOD__.'(): Cannot access remote dir '.$remoteDir.'.';
			$this->triggerError($this->lastError);
			$this->cd($pwd);
			return false;
		}
		
		$ret = ftp_get($this->ftpConn, $localFile, $remoteFile, FTP_BINARY);
		if (($mode !== false) && $ret) {
			@chmod($localFile, $mode);
		}
		$this->cd($pwd);
		return $ret;
	}
	
	public function downloadDir($remoteDir, $localDir, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($remoteDir)) {
			$this->lastError = __METHOD__.'(): parameter $remoteDir may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($localDir)) {
			$this->lastError = __METHOD__.'(): parameter $localDir may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		
		if (!is_dir($localDir) && !mkdir($localDir, ((($mode !== false) ? $mode : 0777) | 0111), true)) {
			$this->lastError = __METHOD__.'(): Cannot create local dir '.$localDir.'.';
			$this->triggerError($this->lastError);
			return false;
		}
		
		$pwd = $this->pwd();
		if (!$this->cd($remoteDir)) {
			$this->lastError = __METHOD__.'(): Cannot access remote dir '.$remoteDir.'.';
			$this->triggerError($this->lastError);
			$this->cd($pwd);
			return false;
		}
		$remoteDir = rtrim($remoteDir, '/').'/';
		$localDir = rtrim($localDir, '/').'/';
		
		$errors = array();
		
		$ls = $this->getList();
		if (!empty($ls)) {
			foreach ($ls as $item) {
				if ($item['type'] == 'file') {
					if (!$this->downloadFile($item['filename'], $localDir.$item['filename'], $mode)) {
						$errors[] = 'Cannot download file '.$remoteDir.$item['filename'].'.';
					}
				} else if ($item['type'] == 'dir') {
					$err = $this->downloadDir($item['filename'], $localDir.$item['filename'], $mode);
					if (is_array($err)) {
						$errors = array_merge($errors, $err);
					} else if ($err === false) {
						$errors[] = 'Cannot download dir '.$remoteDir.$item['filename'].'.';
					}
				}
			}
		}
		
		$this->cd($pwd);
		return empty($errors) ? true : $errors;
	}
	
	public function download($fileList, $mode = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (!is_array($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList has to be an array.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$pwd = $this->pwd();
		
		$errors = array();
		foreach ($fileList as $item) {
			$remoteDir = dirname($item['remote']);
			$remoteFile = basename($item['remote']);
			if (!$this->cd($remoteDir)) {
				$errors[] = 'Cannot change remote directory to '.$pwd.'/'.$remoteDir.'.';
				$this->cd($pwd);
				continue;
			}
			
			$localDir = dirname($item['local']);
			$mode = (isset($item['mode']) ? $item['mode'] : (!$mode ? $mode : 0777));

			if (!is_dir($localDir) && !mkdir($localDir, ($mode | 0111), true)) {
				$errors[] = 'Cannot create local directory to '.$localDir.'.';
				$this->cd($pwd);
				continue;
			}
	
			$ls = $this->getList();
			if (empty($ls)) {
				$errors[] = 'Remote dir '.$remoteDir.' is empty.';
				$this->cd($pwd);
				continue;
			}
			if (array_key_exists($remoteFile, $ls)) {
				if ($ls[$remoteFile]['type'] == 'file') {
					if (!$this->downloadFile($remoteFile, $item['local'])) {
						$errors[] = 'Cannot download file '.$remoteDir.'/'.$remoteFile.'.';
					}
				} else if ($ls[$remoteFile]['type'] == 'dir') {
					if (!$this->downloadDir($remoteFile, $item['local'])) {
						$errors[] = 'Cannot download directory '.$remoteDir.'/'.$remoteFile.'.';
					}
				} else {
					$errors[] = $remoteDir.'/'.$remoteFile.' is not a file or directory.';
				}
			} else {
				$errors[] = 'Remote file '.$pwd.'/'.$remoteDir.'/'.$remoteFile.' does not exist.';
			}
			$this->cd($pwd);
		}
		return empty($errors) ? true : $errors;
	}

	public function makeAbsolutePath($path) {
		if (substr($path, 0, 1) == '/') {
			return $path;
		}
		$path = $this->pwd().'/'.$path;
		//echo $path."\n\n";
		$result = array();
		
		$pathA = explode('/', $path);
		if (!$pathA[0]) {
			$result[] = '';
		}
		//print_r($pathA);
		foreach ($pathA as $key => $dir) {
			if ($dir == '..') {
				if (end($result) == '..') {
					$result[] = '..';
				} else if (($p = array_pop($result)) && !$p) {
					$result[] = '..';
				}
			} else if ($dir && ($dir != '.')) {
				$result[] = $dir;
			}
		}
		if (!end($pathA)) {
			$result[] = '';
		}
		if ($result[0] != '') {
			array_unshift($result, '');
		}
		//print_r($result);
		return implode('/', $result);
	}
	
	public function deleteFile($fname) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($fname)) {
			$this->lastError = __METHOD__.'(): Parameter $fname may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		return @ftp_delete($this->ftpConn, $fname);
	}

	public function deleteDir($path) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($path)) {
			$this->lastError = __METHOD__.'(): Parameter $path may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		
		$pwd = $this->pwd();
		if (!$this->cd($path)) {
			$this->cd($pwd);
			$this->lastError = __METHOD__.'(): '.$pwd.'/'.$path.' is not a directory or does not exist.';
			$this->triggerError($this->lastError);
			return false;
		}

		$errors = array();
		$ls  = $this->getList();
		if (!empty($ls)) {
			foreach ($ls as $item) {
				if ($item['type'] == 'dir') {
					if (!$this->deleteDir($item['filename'])) {
						$errors[] = 'Cannot delete directory '.$pwd.'/'.$item['filename'].'/.';
					}
				} else {
					if (!$this->deleteFile($item['filename'])) {
						$errors[] = 'Cannot delete file '.$pwd.'/'.$item['filename'].'.';
					}
				}
			}
		}
		if (empty($errors)) {
			if (!$this->cd('..')) {
				$this->cd($pwd);
				$this->lastError = __METHOD__.'(): Cannot delete '.$pwd.'/'.$path.'. Cannot change to parent directory.';
				$this->triggerError($this->lastError);
				return false;
			}
			if (!ftp_rmdir($this->ftpConn, basename($path))) {
				$this->cd($pwd);
				$this->lastError = __METHOD__.'(): Cannot delete '.$pwd.'/'.$path.'.';
				$this->triggerError($this->lastError);
				return false;
			}
			$this->cd($pwd);
			return true;
		}
		$this->cd($pwd);
		return $errors;
	}

	public function delete($fileList) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (!is_array($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList has to be an array.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($fileList)) {
			$this->lastError = __METHOD__.'(): parameter $fileList may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$errors = array();
		$pwd = $this->pwd();
		foreach ($fileList as $file) {
			if ($this->isDir($file)) {
				if (!$this->deleteDir($file)) {
					$errors[] = 'Cannot delete directory '.$pwd.'/'.$file.'.';
				}
			} else {
				if (!$this->deleteFile($file)) {
					$errors[] = 'Cannot delete file '.$pwd.'/'.$file.'.';
				}
			}

		}
		return empty($errors) ? true : $errors;
	}

	public function rename($oldname, $newname, $overwrite = false) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($oldname)) {
			$this->lastError = __METHOD__.'(): Parameter $oldname may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		if (empty($oldname)) {
			$this->lastError = __METHOD__.'(): Parameter $oldname may not be empty.';
			$this->triggerError($this->lastError);
			return false;
		}
		$oldname = $this->makeAbsolutePath($oldname);
		$newname = $this->makeAbsolutePath($newname);
		//echo $oldname." -> ".$newname."\n";
		if (!$this->fileExists($oldname)) {
			$this->lastError = __METHOD__.'(): File '.$oldname.' does not exist.';
			$this->triggerError($this->lastError);
			return false;
		}
		$ls = $this->getList(dirname($oldname));
		$this->makeDir(dirname($newname), $ls[basename($oldname)]['permissions']);
		if ($this->fileExists($newname)) {
			if ($overwrite) {
				if (!$this->deleteFile($newname)) {
					$this->lastError = __METHOD__.'(): Cannot delete '.$newname.'.';
					$this->triggerError($this->lastError);
					return false;
				}
			} else {
				$this->lastError = __METHOD__.'(): '.$newname.' already exists.';
				$this->triggerError($this->lastError);
				return false;
			}
		}
		return ftp_rename($this->ftpConn, $oldname, $newname);
	}
	
	/* Sendet ein SITE EXEC-Kommando (command) an den FTP-Server. */
	public function exec($command) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		return @ftp_exec($this->ftpConn, $command);
	}
	
	/**
	 * Sendet ein beliebiges Kommando command an den FTP-Server. 
	 * Gibt die Serverantwort als ein Array von Zeichenketten zurück. 
	 * Die Ausgabe wird in keinster Weise ausgewertet. ftp_raw() versucht auch 
	 * nicht zu bestimmen, ob das Kommando erfolgreich war.
	 */
	public function raw($command) {
		if (!$this->isConnected()) {
			$this->lastError = __METHOD__.'(): Not connected.';
			$this->triggerError($this->lastError);
			return false;
		}
		return @ftp_raw($this->ftpConn, $command);
	}

}
