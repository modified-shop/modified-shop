<?php
/* --------------------------------------------------------------
   $Id: split_page_results.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(split_page_results.php,v 1.13 2003/05/05); www.oscommerce.com
   (c) 2003 nextcommerce (split_page_results.php,v 1.6 2003/08/18); www.nextcommerce.org
   (c) 2006 xt:Commerce (split_page_results.php 950 2005-05-14); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
class splitPageResults {

    function __construct(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows, $count_key = '*') {
      if (empty($current_page_number)) $current_page_number = 1;

      $pos_to = strlen($sql_query);
      $pos_from = strpos(strtoupper($sql_query), ' FROM', 0);

      $pos_group_by = strpos(strtoupper($sql_query), ' GROUP BY', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

      $pos_having = strpos(strtoupper($sql_query), ' HAVING', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

      $pos_order_by = strpos(strtoupper($sql_query), ' ORDER BY', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      if (strpos($sql_query, 'DISTINCT') || strpos(strtoupper($sql_query), 'GROUP BY')) {
        $count_string = 'DISTINCT ' . xtc_db_input($count_key);
      } else {
        $count_string = xtc_db_input($count_key);
      }

      $reviews_count_query = xtc_db_query("SELECT count(" . $count_string . ") AS total " .  substr($sql_query, $pos_from, ($pos_to - $pos_from)));
      $reviews_count = xtc_db_fetch_array($reviews_count_query);
      $query_num_rows = $reviews_count['total'];
        
      // FIX Division by Zero
      $num_pages = $max_rows_per_page > 0 ? ceil($query_num_rows / $max_rows_per_page) : 0;        
      
      if ($current_page_number > $num_pages) {
          $current_page_number = $num_pages;
      }
      $offset = ($max_rows_per_page * ($current_page_number - 1));
      $offset = $offset < 0 ? 0 : $offset;
      
      //no page results limit (-1)
      if ($max_rows_per_page > (-1)) {
        $sql_query .= " limit " . $offset . ", " . $max_rows_per_page;
      }
    }

    function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page') {
        global $PHP_SELF;
       
        if ( xtc_not_null($parameters) && (substr($parameters, -1) != '&') ) $parameters .= '&';

        // calculate number of pages needing links // FIX Division by Zero
        $num_pages = $max_rows_per_page > 0 ? ceil($query_numrows / $max_rows_per_page) : 0;
        
        $pages_array = array();
        for ($i=1; $i<=$num_pages; $i++) {
            $pages_array[] = array('id' => $i, 'text' => $i);
        }

        if ($num_pages > 1) {
            $display_links = xtc_draw_form('pages', basename($PHP_SELF), '', 'get');

            if ($current_page_number > 1) {
                //$display_links .= '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . $page_name . '=1') . '" class="splitPageLink">' . PREVNEXT_BUTTON_FIRST . ' </a>&nbsp;';
                $display_links .= '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . ($current_page_number - 1)) . '" class="button">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';
            } else {
                //$display_links .= PREVNEXT_BUTTON_PREV . '&nbsp;&nbsp;'; // Tomcraft - 2015-11-16 - Don't show PREVNEXT_BUTTON_PREV on first page
            }

            $display_links .= sprintf(TEXT_RESULT_PAGE, xtc_draw_pull_down_menu($page_name, $pages_array, $current_page_number, 'onChange="this.form.submit();"'), $num_pages);

            if (($current_page_number < $num_pages) && ($num_pages != 1)) {
                $display_links .= '&nbsp;&nbsp;<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . ($current_page_number + 1)) . '" class="button">' . PREVNEXT_BUTTON_NEXT . '</a>';
                //$display_links .= '&nbsp;<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $num_pages) . '" class="splitPageLink">' . PREVNEXT_BUTTON_LAST . '</a>';
            } else {
                //$display_links .= '&nbsp;&nbsp;' . PREVNEXT_BUTTON_NEXT; // Tomcraft - 2015-11-16 - Don't show PREVNEXT_BUTTON_LAST on last page
            }

            if ($parameters != '') {
                if (substr($parameters, -1) == '&') $parameters = substr($parameters, 0, -1);
                $pairs = explode('&', $parameters);
                while (list(, $pair) = each($pairs)) {
                    list($key,$value) = explode('=', $pair);
                    $display_links .= xtc_draw_hidden_field(rawurldecode($key), rawurldecode($value));
                }
            }

            $display_links .= '</form>';
        } else {
            $display_links = '<span style="line-height: 28px;">'.sprintf(TEXT_RESULT_PAGE, $num_pages, $num_pages).'</span>';
        }

        return $display_links;
    }

    function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output) {
        $to_num = ($max_rows_per_page * $current_page_number);
        if ($to_num > $query_numrows) $to_num = $query_numrows;
        $from_num = ($max_rows_per_page * ($current_page_number - 1));
        if ($to_num == 0) {
            $from_num = 0;
        } else {
            $from_num++;
        }
        
        //no page results limit (-1)
        if ($max_rows_per_page == (-1)) {
          $from_num = 1;
          $to_num = $query_numrows; 
        }
        return '<span style="line-height: 28px;">'.sprintf($text_output, $from_num, $to_num, $query_numrows).'</span>';
    }
}
?>