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
if(
		is_dir('installer') &&
		!isset($_GET['wsdl']) &&
		!isset($_GET['xsd']) &&
		$_SERVER['REQUEST_METHOD'] != 'POST'
	) {
	header('Location: installer/install.php');
} else {
	require('iclear.php');
}


?>