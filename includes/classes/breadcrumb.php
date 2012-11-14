<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(breadcrumb.php,v 1.3 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (breadcrumb.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (breadcrumb.php 899 2005-04-29); www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class breadcrumb {
    var $_trail;

    function breadcrumb() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    //BOF - web28 - 2010-11-13 - add target parameter
    //function add($title, $link = '') {
    //  $this->_trail[] = array('title' => $title, 'link' => $link);
    //}
    function add($title, $link = '', $target = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link, 'target' => $target );
    }
    //EOF - web28 - 2010-11-13 - add target parameter

    function trail($separator = ' - ') {
      $trail_string = '';

      //BOF - DokuMan - 2011-12-19 - precount for performance
      //for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
      $n=sizeof($this->_trail);
      for ($i=0; $i<$n; $i++) {
      //EOF - DokuMan - 2011-12-19 - precount for performance

        if (isset($this->_trail[$i]['link']) && xtc_not_null($this->_trail[$i]['link'])) {
        //BOF - web28 - 2010-11-13 - add target parameter
        //$trail_string .= '<a href="' . $this->_trail[$i]['link'] . '" class="headerNavigation">' . $this->_trail[$i]['title'] . '</a>';
          $trail_string .= '<a href="' . $this->_trail[$i]['link'] . '" class="headerNavigation" '. $this->_trail[$i]['target'] .'>' . $this->_trail[$i]['title'] . '</a>';
        //EOF - web28 - 2010-11-13 - add target parameter
        } else {
          $trail_string .= $this->_trail[$i]['title'];
        }

        //BOF - Dokuman - 2012-07-02 - breadcrumb is displayed in different text heights, whether a separator sign is included or not
        //if (($i+1) < $n) $trail_string .= $separator;
        if (($i+1) < $n) {
          $trail_string .= '<span>'.$separator.'</span>';
        } else {
          $trail_string .= '<span>&nbsp;</span>';
        }
        //EOF - Dokuman - 2012-07-02 - breadcrumb is displayed in different text heights, whether a separator sign is included or not
      }

      return $trail_string;
    }

    // Begin Econda-Monitor
    function econda() { // for drill-down
      $econda_string = '';

      //BOF - DokuMan - 2011-12-19 - precount for performance
      //for ($i=1, $n=sizeof($this->_trail); $i<$n; $i++) {
      $n=sizeof($this->_trail);
      for ($i=1; $i<$n; $i++) {
      //EOF - DokuMan - 2011-12-19 - precount for performance
        $econda_string .= $this->_trail[$i]['title'];
        if (($i+1) < $n) $econda_string .= '/';
      }

      return $econda_string;
    }
    // End Econda-Monitor
  }
?>