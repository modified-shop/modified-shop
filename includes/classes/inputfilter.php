<?php
/* -----------------------------------------------------------------------------------------
$Id: inputfilter.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

modified eCommerce Shopsoftware
http://www.modified-shop.org

Copyright (c) 2009 - 2013 [www.modified-shop.org]

Released under the GNU General Public License
---------------------------------------------------------------------------------------*/

class Inputfilter {
    private $params=false;
    public function __construct()
    {
        $this->params=array();
    }

    public function validate($source)
    {
        $this->params=$source;
        $this->inputValidate();
        
        return $this->params;
    }

    public function removeTags($value)
    {
        return strip_tags ($value) == $value ? $value : '';
        //return preg_replace ('/<[^>]*>/', ' ', $value) == $value ? $value : ''; //alternative zu stip_tags
    }

    public function validateCPath($value)
    {
        return preg_replace('/[^0-9_]/','',$value);
    }

    public function validateNumeric($value)
    {
        return preg_replace('/[^0-9]/','',$value);
    }

    public function validateSigns($value)
    {
        return preg_replace('/[^0-9a-zA-Z_-]/','',$value);
    }

    public function validateSessionID($value)
    {
        return preg_replace('/[^0-9a-zA-Z]/','',$value);
    }

    private function inputValidate()
    {
        if (is_array($this->params)) {
            foreach($this->params as $key => $value ) {
                switch($key) {
                  //remove tags
                  case 'search':
                  case 'search_email':                      
                  case 'searchoption':
                  case 'search_optionsname':
                  case 'product_search':
                      $this->params[$key] = $this->removeTags($value);
                      break;
                  //numeric
                  case 'page':
                  case 'value_page':
                  case 'option_page':
                  case 'option_id':
                  case 'value_id':
                  case 'oID':
                  case 'pID':
                  case 'gID':
                  case 'coID':
                  case 'tID':
                  case 'zID':
                  case 'cID':
                  case 'lID':
                  case 'ID':
                  case 'mID':
                  case 'rID':
                  case 'sID':
                      $this->params[$key] = $this->validateNumeric($value);
                      break;
                  //0-9a-zA-Z _ -
                  case 'action':
                      $this->params[$key] = $this->validateSigns($value);
                      break;
                  //cPath
                  case 'cPath':
                      $this->params[$key] = $this->validateCPath($value);
                      break;
                  case 'info':
                  case 'MODsid':
                      $this->params[$key] = $this->validateSessionID($value);
                      break;
                }
            }
        }
    }
}
?>