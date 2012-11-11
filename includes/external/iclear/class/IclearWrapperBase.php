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

class IclearWrapperBase extends IclearBase {
	 
	var $icCore = false;
	 
	var $id = '';
	 
	var $icTableExist = false;
	 
	var $currentProductID = 0;
	 
	var $customerInfo = false;
	 
	var $_installType = 'update';
	 
	function IclearWrapperBase(&$icCore) {
		$this->icVersion = '$Id$';
		parent::IclearBase($icCore);
		$this->icTable = $this->iclearTable();
		$this->icTableExist = $this->dbFetchRecord('SHOW TABLES LIKE "' . $this->icTable . '"') ? true : false;
	}
	/**
	 * object id of current instance
	 *
	 * @return string
	 */
	function id() {
		return $this->id;
	}
	 
	/**
	 * get the state of the (previously performed)
	 * iclear table check
	 *
	 * @return boolean
	 */
	function tableExist() {
		return $this->icTableExist;
	}
	 
	/**
	 * loads basket object from iclear table
	 * and assigns current IclearCore object to it
	 *
	 * @param string $basketID
	 * @return IclearBasket $basket
	 */
	function &loadBasket($basketID = '') {
		$rv = false;

		if($this->icTableExist && $basketID) {
			$icCore =& $this->icCore;
			if(!class_exists('IclearBasket', false)) {
				require_once $icCore->getPath('class').'IclearBasket.php';
			}

			if(!class_exists('IclearAddress', false)) {
				require_once $icCore->getPath('class') . 'IclearAddress.php';
			}


			$qry = 'SELECT * FROM ' . $this->icTable . ' WHERE ' .
         			 'basketID = "'.$basketID.'"';

			if($rec = $this->dbFetchRecord($qry)) {
				if($rv = unserialize(base64_decode($rec['basket']))) {
					/**
					 *  if there's an internal service version mismatch, update basket item keys
					 *  this could happen if the interface is beeing updated and waiting baskets R stored in DB!
					 */
					if($rv->serviceVersion != $this->serviceVersion) {
						$rv->update();
					}
					$rv->iclearID($rec['iclearID']);

					// recreate basket core reference
					$icCore->setObject('IclearBasket', $rv);
					$rv->setCore($icCore);
					
					//20110128 CA - reset error
					$rv->dropError();
					$rv->errorCode = 0;
				}
			} else {
				$basket = $icCore->getBasket();
				$basket->errorCode = IC_ERROR_BASKET_NOT_FOUND;
				$basket->addError('Basket not submitted by shop system!');
			}
		}

		return $rv;
	}

	/**
	 * Write iclear basket object 2 iclear table
	 * removes existing core references in object
	 *
	 * @param IclearBasket $basket
	 */
	function storeBasket($basket = false) {
		$rc = false;
		if(!$basket) {
			$basket = $this->icCore->getBasket();
		}

		if($this->icTableExist && $basket && $basket->basketItemCount()) {
			$iclearID = $basket->iclearID();

			// remove core from basket/delivery address 2 prevent incomplete classes and overhead
			// used unset - delete would kill the base core object!
			//$basket->unsetCore();

			$qry = 'REPLACE INTO ' . $this->icTable .
				  '(iclearID, basketID, basket) VALUES ('.
			($iclearID ? '"' . $iclearID . '"' : 'NULL'). ','.
          '"'.$basket->basketID().'",' .
				  '"'.base64_encode(serialize($basket)).'"'.
				')';

			if($rc = $this->dbQuery($qry)) {
				$basket->iclearID($this->dbLastInsertID());

			}
			//$basket->setCore($this->icCore);

		}
		return $rc;
	}

	/**
	 * returns current session_save_path()
	 * @return string $path
	 */
	function sessionSavePath() {
		$rv = session_save_path();
		if(!preg_match('!/$!', $rv)) {
			$rv .= '/';
		}
		return $rv;
	}

	/**
	 * write $_SESSION to FS if session is FS based
	 * @param string $sessionID
	 * @param array $session
	 */
	function storeSession($sessionID, $session) {
		if(session_module_name() == 'files') {
			$path = $this->sessionSavePath() . 'sess_' . $sessionID;
			if($fp = @fopen($path, 'w')) {
				$session['ic_modified'] = date('Y-m-d H:i:s');
				fputs($fp, serialize($session));
				fclose($fp);
			}
		}
	}

	/**
	 * Remove basket from iclear table
	 *
	 * @param int $iclearID
	 */
	function deleteBasket($iclearID = 0) {
		$rc = false;
		if($iclearID) {
			$qry = 'DELETE FROM ' . $this->icTable .
				  ' WHERE iclearID = "'.$iclearID.'"';
			$rc = $this->dbQuery($qry);
		}
		return $rc;
	}

	/**
	 * Installs iclear table in current shop system
	 * if $clean is true, a previously created table will be dropped
	 *
	 * @param boolean $clean
	 */
	function installTable($clean = true) {
		$rc = false;
		if($clean) {
			$this->dropTable();
		}
		 
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $this->icTable . ' (' .
    	       'iclearID INT(11) NOT NULL AUTO_INCREMENT,' .
    	       'basketID VARCHAR(32) NOT NULL,' .
    	       'basket LONGTEXT NOT NULL,' .
    	       'ts TIMESTAMP NOT NULL, ' .
    	       'PRIMARY KEY (iclearID),'.
    	       'UNIQUE KEY basketID (basketID))';

		return $this->dbQuery($sql);
	}

	/**
	 * drop iclear table from DB
	 */
	function dropTable() {
		return $this->dbQuery("DROP TABLE IF EXISTS " . $this->icTable);
	}
	/**
	 * retrieves iclear table name
	 * @return string
	 */
	function iclearTable() {
		return IC_TABLE_ORDERS;
	}

	/**
	 * sets order item count 2 zero
	 */
	function rewindOrder() {
		$this->currentProductID = 0;
	}
    /**
     * Parses the street name from a street_houseno. string
     * @return string $street
     */
    function parseStreet($txt) {
    	$rv = $this->parseAddress($txt);
    	return $rv['street'];
    }
    
    /**
     * Parses the streetno name from a street_houseno. string
     * @return string $streetno
     */
    function parseStreetNo($txt) {
    	$rv = $this->parseAddress($txt);
    	return $rv['streetNo'];
    }
    
    /**
     * Parses the street + no string in an array with keys
     * street and streetNo.
     * If processing of streetNo is not possible whole input txt is
     * in street.
     * @return array [street, streetNo]
     */
    function parseAddress($txt = '') {
    	$txt = trim($txt);
	    $rv = array('street' => $txt, 'streetNo' => '');
			(strlen($txt <= 7) && preg_match('/(.*?(?:str\.|strasse|straße|weg|gasse))\s+([0-9]+[a-z]{1})$/', $txt, $match)) ||
			(preg_match('/([a-z]{1}\d{1,2})[, ]+([0-9]{1,2}[a-z]{0,1})$/i', $txt, $match)) ||
			(preg_match('/([0-9]+[a-z]{0,1})$/i', substr($txt, -5), $match) && preg_match('/(.*?)(' . $match[1] . ')/', $txt, $match));
			sizeof($match) && ($rv['street'] = trim($match[1])) && ($rv['streetNo'] = trim($match[2]));
			return $rv;
    }
	/**
	 * @return string installation type
	 */
	function installType() {
		return $this->_installType;
	}

	/**
	 * Pseudo abstract methods follows
	 */

	function finalizeBasket() {
		return false;
	}

	/**
	 *  abstract methods follows
	 */

	function dbQuery($qry = '') {
		die('Abstract method ' . __FUNCTION__ . 'called!');
	}
	 
	function dbQuote($val) {
		die('Abstract method ' . __FUNCTION__ . 'called!');
	}

	function dbFetchRecord($qry = '') {
		die('Abstract method ' . __FUNCTION__ . 'called!');
	}
	 
	function dbLastInsertID() {
		die('Abstract method ' . __FUNCTION__ . 'called!');
	}
	 
}


?>
