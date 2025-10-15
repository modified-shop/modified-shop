<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  use Teambank\EasyCreditApiV3\Integration\StorageInterface;

  class TeambankStorage implements \Teambank\EasyCreditApiV3\Integration\StorageInterface  {
    protected $data = [];
  
    public function set($key, $value) {
      $this->data[$key] = $value;
      return $this;
    }
    
    public function get($key) {
      return $this->data[$key]; 
    }
    
    public function clear() {
      $this->data = [];
    }
    
    public function save() {
      $_SESSION['easycredit']['storage'] = $this->data;
    }

    public function restore() {
      if (isset($_SESSION['easycredit']['storage'])) {
        $this->data = $_SESSION['easycredit']['storage'];
      }
    }
  }
