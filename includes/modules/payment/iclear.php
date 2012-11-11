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
  define('IC_SEC', true);

  // detect if we're coming from customer view (./iclear) or admin (../iclear)
  $icPath = DIR_FS_EXTERNAL.'iclear/class/IclearProxy.php'; //DokuMan - 2011-09-06 - move iclear to 'external' directory
  //$icPath = './iclear/class/IclearProxy.php';
  //if(!file_exists($icPath)) {
    //$icPath = '.' . $icPath;
  //}
  require_once $icPath;

  if ( !class_exists( 'iclear' ) ) {
    class iclear {

      var $proxy = false;

  // class constructor
      function iclear( $module_name = '' ) {
        global $icCore;
        $this->proxy =& $icCore->getProxy(false);
        $this->proxy->perform('moduleName', array($module_name));
        if ( strlen( $module_name ) > 2 ) {
          if (isset($_SESSION['icBasketID'])) {
            $this->proxy->perform('loadBasket', array($_SESSION['icBasketID']));
          }
          $this->proxy->perform('correctValue', array());
        }
        $this->code = 'iclear';
        $this->lang =& $icCore->getLanguage();
        $this->title = $this->proxy->perform('title');
        $this->description =  $this->lang->getParam('DESCRIPTION');
        $this->sort_order =  $this->proxy->perform('sortOrder');;
        $this->enabled = $this->proxy->perform('enabled');
        $this->update_status();
      }

      /**
       * objects methods expected 2 exist by shop system
       *
       */

      function update_status() {
        $this->proxy->perform(__FUNCTION__);
      }

      function javascript_validation() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function selection() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function pre_confirmation_check() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function confirmation() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function process_button() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function before_process() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function after_process() {
        $this->proxy->perform(__FUNCTION__);
      }

      function output_error() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function check() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function install() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function remove() {
        return $this->proxy->perform(__FUNCTION__);
      }

      function keys() {
        return $this->proxy->perform(__FUNCTION__);
      }
    }
  }
?>