<?php
/*************************************************************************

$Id$

iclear payment system - because secure is simply secure
http://www.iclear.de

Copyright (c) 2001 - 2009 iclear

Released under the GNU General Public License

************************************************************************

All rights reserved.

This program is free software licensed under the GNU General Public License (GPL).

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
USA

*************************************************************************/

if(!defined('IC_SEC')) {
	die('No external calls allowed!');
}

class IclearError {

	var $error = array();

	function IclearError() {
	}

	function isError() {
		return $this->getErrorCount();
	}

	function addError($msg) {
		if(!is_array($this->error)) {
			$this->error = array();
		}
		array_push($this->error, $msg);
		return $msg;
	}

	function getErrorCount() {
		$rc = 0;
		if(is_array($this->error)) {
			$rc = sizeof($this->error);
		}
		return $rc;
	}

	function dumpErrorList() {
		if($this->getErrorCount()) {
			print join("\n", $this->error);
		}
	}

	function getErrorString($lineBreak = "\n") {
		$error = '';
		if($this->getErrorCount()) {
			$error =  join($lineBreak, $this->error);
		}
		return $error;
	}

	function lastError() {
		$error = '';
		if($this->getErrorCount()) {
			$error =  $this->error[$this->getErrorCount()-1];
		}
		return $error;
	}
	
	function dropError () {
		if($this->getErrorCount()) {
			$this->error = array();
		}
	}

}


?>
