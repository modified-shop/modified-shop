<?php
/* -----------------------------------------------------------------------------------------
   $Id: class.econda304SP2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 xt:Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
   
   class econda{
   
   	
   	function _loginUser() {
   		$_SESSION['login_success'] = 1;
   	}
   	
   	function _emptyCart() {
//   		$_SESSION['econda_cart'] = array();
   	}
   	
   	function _delArticle($pID,$qty,$old_qty) {
   		$_SESSION['econda_cart'][] = array('todo' => 'del', 'id' => xtc_db_input($pID), 'cart_qty' => xtc_remove_non_numeric($qty), 'old_qty' => $old_qty);  		
   	}
   	
   	function _updateProduct($pID,$qty,$old_qty) {
   		$_SESSION['econda_cart'][] = array('todo' => 'update', 'id' => xtc_db_input($pID), 'cart_qty' => xtc_remove_non_numeric($qty), 'old_qty' => $old_qty);					
   	}
   	
   	function _addProduct($pID,$qty,$old_qty) {
   		$_SESSION['econda_cart'][] = array('todo' => 'add', 'id' => xtc_db_input($pID), 'cart_qty' => xtc_remove_non_numeric($qty), 'old_qty' => $old_qty);
										
   	}
   	
     	
   }
   

?>
