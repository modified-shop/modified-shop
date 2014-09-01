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
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  class tableBlock {
    protected static $table_border = '0';
    protected static $table_width = '100%';
    protected static $table_cellspacing = '0';
    protected static $table_cellpadding = '2';
    protected static $table_parameters = '';
    protected static $table_row_parameters = '';
    protected static $table_data_parameters = '';

    // cYbercOsmOnauT - 2011-02-07 - Fallback method for old calls
    public function tableBlock($contents = '') {
      return self::constructor($contents);
    }

    protected static function constructor($contents) {
      $tableBox_string = '';
      $form_set = false;
      if (isset($contents['form'])) {
        $tableBox_string .= $contents['form'] . "\n";
        $form_set = true;
        array_shift($contents);
      }
      $tableBox_string .= '<table class="contentTable" border="' . self::$table_border . '" width="' . self::$table_width . '" cellspacing="' . self::$table_cellspacing . '" cellpadding="' . self::$table_cellpadding . '"';
      if (self::$table_parameters != '')
        $tableBox_string .= ' ' . self::$table_parameters;
      $tableBox_string .= '>' . "\n";
      for ($i = 0; $i < sizeof($contents); $i++) {
        $tableBox_string .= '  <tr';
        if (self::$table_row_parameters != '')
          $tableBox_string .= ' ' . self::$table_row_parameters;
        if (isset($contents[$i]['params']))
          $tableBox_string .= ' ' . $contents[$i]['params'];
        $tableBox_string .= '>' . "\n";
        if (!isset($contents[$i][0]))
          $contents[$i][0] = '';
        if (is_array($contents[$i][0])) {
          for ($x = 0; $i < sizeof($contents[$i]); $x++) {
            if ($contents[$i][$x]['text']) {
              $tableBox_string .= '    <td ';
              if ($contents[$i][$x]['align'] != '')
                $tableBox_string .= ' align="' . $contents[$i][$x]['align'] . '"';
              if ($contents[$i][$x]['params']) {
                $tableBox_string .= ' ' . $contents[$i][$x]['params'];
              } elseif (self::$table_data_parameters != '') {
                $tableBox_string .= ' ' . self::$table_data_parameters;
              }
              $tableBox_string .= '>';
              if ($contents[$i][$x]['form'])
                $tableBox_string .= $contents[$i][$x]['form'];
              $tableBox_string .= $contents[$i][$x]['text'];
              if ($contents[$i][$x]['form'])
                $tableBox_string .= '</form>';
              $tableBox_string .= '</td>' . "\n";
            }
          }
        } else {
          $tableBox_string .= '    <td ';
          if (!isset($contents[$i]['align']))
            $contents[$i]['align'] = '';
          if ($contents[$i]['align'] != '')
            $tableBox_string .= ' align="' . $contents[$i]['align'] . '"';
          if (isset($contents[$i]['params'])) {
            $tableBox_string .= ' ' . $contents[$i]['params'];
          } elseif (self::$table_data_parameters != '') {
            $tableBox_string .= ' ' . self::$table_data_parameters;
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
  }
?>