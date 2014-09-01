<?php
/* -----------------------------------------------------------------------------------------
   $Id: econda.php 899 2006-07-29 02:40:57Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2006 xt:Commerce
   -----------------------------------------------------------------------------------------

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
