<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(breadcrumb.php,v 1.3 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (breadcrumb.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  class breadcrumb {
    var $_trail;


    function __construct() {
      $this->reset();
    }


    function reset() {
      $this->_trail = array();
    }


    function remove_last() {
      array_pop($this->_trail);
    }


    function remove($title) {
      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if ($this->_trail[$i]['title'] == $title) {
          unset($this->_trail[$i]);
        }
      }
      $this->_trail = array_values($this->_trail);
    }


    function add($title, $link = '') {
      global $PHP_SELF, $request_type;
            
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }


    function trail($separator = ' - ') {
      $smarty = new Smarty();
      $smarty->assign('TRAIL', $this->_trail);
      $smarty->assign('SEPARATOR', $separator);
      $trail_string = $smarty->fetch(CURRENT_TEMPLATE.'/module/breadcrumb.html');
      
      return $trail_string;
    }
    

    function econda() {
      $econda_string = '';

      for ($i=1, $n=sizeof($this->_trail); $i<$n; $i++) {
        $econda_string .= $this->_trail[$i]['title'];

        if (($i+1) < $n) $econda_string .= '/';
      }

      return $econda_string;
    }
    
  }
?>