<?php
/* --------------------------------------------------------------
   $Id: table_block.php 1797 2011-02-12 15:31:48Z franky-n-xtcm $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(table_block.php,v 1.5 2003/06/02); www.oscommerce.com
   (c) 2003 nextcommerce (table_block.php,v 1.8 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (table_block.php 950 2005-05-14)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  class tableBlock {
    public $table_border = '0';     //deprecated -> removed
    public $table_width = '100%';   //deprecated -> removed
    public $table_cellspacing = '0';//deprecated -> removed
    public $table_cellpadding = '2';//deprecated -> removed
    public $table_parameters = '';
    public $table_row_parameters = '';
    public $table_data_parameters = '';
    
    public function createBlock($contents) {
      $tableBox_string = '';

      $form_set = false;
      if (isset($contents['form'])) {
        $tableBox_string .= $contents['form'] . "\n";
        $form_set = true;
        xtc_array_shift($contents);
      }

      $tableBox_string .= '<table class="contentTable"';
      if ($this->table_parameters != '') {
        $tableBox_string .= ' ' . $this->table_parameters;
      }
      $tableBox_string .= '>' . "\n";

      for ($i = 0, $n = sizeof($contents); $i < $n; $i++) {
        $tableBox_string .= '  <tr';
        if ($this->table_row_parameters != '') 
            $tableBox_string .= ' ' . $this->table_row_parameters;
        if (isset($contents[$i]['params'])) 
            $tableBox_string .= ' ' . $contents[$i]['params'];
        $tableBox_string .= '>' . "\n";
        if (!isset($contents[$i][0]))   
            $contents[$i][0] = '';
        if (is_array($contents[$i][0])) {
            for ($x = 0, $y = sizeof($contents[$i]); $x < $y; $x++) {
                if ($contents[$i][$x]['text']) {
                    $tableBox_string .= '    <td ';
                    //using css
                    $this->set_styles($contents[$i][$x]);
                    $contents[$i][$x]['params'] = $this->contents_param;                        
                    if (isset($contents[$i][$x]['params']) && $contents[$i][$x]['params'] != '') {
                        $tableBox_string .= ' ' . $contents[$i][$x]['params'];
                    } elseif ($this->table_data_parameters != '') {
                        $tableBox_string .= ' ' . $this->table_data_parameters;
                    }
                    $tableBox_string .= '>';
                    if ($contents[$i][$x]['form']) $tableBox_string .= $contents[$i][$x]['form'];
                        $tableBox_string .= $contents[$i][$x]['text'];
                    if ($contents[$i][$x]['form']) $tableBox_string .= '</form>';
                        $tableBox_string .= '</td>' . "\n";
                }
            }
        } else {
            $tableBox_string .= '    <td ';
            //using css
            $this->set_styles($contents[$i]);
            $contents[$i]['params'] = $this->contents_param;                        
            if (isset($contents[$i]['params']) && $contents[$i]['params'] != '') {
                $tableBox_string .= ' ' . $contents[$i]['params'];
            } elseif ($this->table_data_parameters != '') {
                $tableBox_string .= ' ' . $this->table_data_parameters;
            }
            $tableBox_string .= '>' . $contents[$i]['text'] . '</td>' . "\n";
        }

        $tableBox_string .= '  </tr>' . "\n";
      }

      $tableBox_string .= '</table>' . "\n";

      if ($form_set) 
          $tableBox_string .= '</form>' . "\n";

      return $tableBox_string;
    }
    
    private function set_styles($contents) 
    {    
        $this->contents_param = isset($contents['params']) ? $contents['params'] : '';
        if (isset($contents['align']) && trim($contents['align']) != '') {  
            if (isset($contents['params']) && strpos($contents['params'],'text-align') === false) {
                $contents['params']  = preg_replace("'\s+=\s+'",'=',$contents['params']);
                if (strpos($contents['params'],'style="') !== false) {
                    $this->contents_param = str_replace('style="','style="text-align:'.$contents['align'].';',$contents['params']);
                } else {
                   $this->contents_param .= ' style="text-align:' . $contents['align'] . ';"';  
                }
            } else {
                $this->contents_param .= ' style="text-align:' . $contents['align'] . ';"'; 
            }
        }
        if ($this->table_data_parameters != '') {
            $table_data_parameters  = preg_replace("'\s+=\s+'",'=',$this->table_data_parameters);
            if (strpos($table_data_parameters,'style="') !== false) {
                $this->contents_param = str_replace('style="','style="text-align:'.$contents['align'].';',$table_data_parameters);
            } else {
               $this->contents_param .= ' ' . $this->table_data_parameters;  
            }            
        }                
    
    }
  }
?>