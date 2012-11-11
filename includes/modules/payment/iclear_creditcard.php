<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2001 - 2003 osCommerce

  Released under the GNU General Public License

************************************************************************
  Copyright (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Pl�nkers
       http://www.themedia.at & http://www.oscommerce.at

  WSDL extensions
  Copyright (C) 2005 - 2006 BSE, David Brandt

                    All rights reserved.

  iclear design pattern
  Copyright (C) 2007 - 2009 iclear GmbH

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

if ( file_exists( 'includes/modules/payment/iclear.php' ) ) {
	require_once 'includes/modules/payment/iclear.php';
} else {
	require_once '../includes/modules/payment/iclear.php';
}

class iclear_creditcard extends iclear {
	
	function iclear_creditcard ( ) {
		parent::iclear( 'creditcard' );
		$this->code = 'iclear_creditcard';
		$this->description =  $this->lang->getParam('DESCRIPTION_CREDITCARD');
		$this->enabled = $this->proxy->perform('enabled');
	}
	
	function selection() {
		$res = parent::selection( );
		$res['id'] = 'iclear_creditcard';
		$res['module'] = $this->proxy->perform('title');
		$res['description'] = $this->lang->getParam('INFO_EXTENDED_CREDITCARD');
		return $res;
	}
	
}
?>
