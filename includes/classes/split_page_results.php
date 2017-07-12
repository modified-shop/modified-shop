<?php
/* -----------------------------------------------------------------------------------------
   $Id: split_page_results.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(split_page_results.php,v 1.14 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (split_page_results.php,v 1.6 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (split_page_results.php 1166 2005-08-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class splitPageResults {
    var $sql_query;
    var $number_of_rows;
    var $current_page_number;
    var $number_of_pages;
    var $number_of_rows_per_page;

    // class constructor
    function __construct($query, $page, $max_rows, $count_key = '*') {
      $this->sql_query = $query;

      if (empty($page) || !is_numeric($page) || $page < 0) $page = 1;
      $this->current_page_number = $page;

      $this->number_of_rows_per_page = $max_rows;

      $pos_to = strlen($this->sql_query);
      $pos_from = strpos(strtoupper($this->sql_query), ' FROM', 0);

      $pos_group_by = strpos(strtoupper($this->sql_query), ' GROUP BY', $pos_from);
      if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

      $pos_having = strpos(strtoupper($this->sql_query), ' HAVING', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

      $pos_order_by = strpos(strtoupper($this->sql_query), ' ORDER BY', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      if (strpos(strtoupper($this->sql_query), 'DISTINCT') || strpos(strtoupper($this->sql_query), 'GROUP BY')) {
        $count_string = 'DISTINCT ' . xtc_db_input($count_key);
        //$count_string = xtc_db_input($count_key);
      } else {
        $count_string = xtc_db_input($count_key);
      }

      //BOF - DokuMan - 2010-08-26 - performance improvement
      //$count_query = xtDBquery($query);
      //$count = xtc_db_num_rows($count_query,true);
      $reviews_count_query = xtc_db_query("select count(" . $count_string . ") as total " . substr($query, $pos_from, ($pos_to - $pos_from)));
      $reviews_count = xtc_db_fetch_array($reviews_count_query);
      $count = $reviews_count['total'];
      //EOF - DokuMan - 2010-08-26 - performance improvement

      $this->number_of_rows = $count;

      //BOF -web28- 2010-08-07 - FIX Division by Zero
      //$this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);
      if ($this->number_of_rows_per_page > 0) {
      $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);
      } else {
      $this->number_of_pages = 0;
      }
      //EOF -web28- 2010-08-07 - FIX Division by Zero

      if ($this->current_page_number > $this->number_of_pages) {
        $this->current_page_number = $this->number_of_pages;
      }

      $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      //BOF -web28- 2010-08-07 - FIX possible $offset = -0
      if ($offset < 1) $offset = 0;
      //EOF -web28- 2010-08-07 - FIX possible $offset = -0

      //BOF - DokuMan - 2010-08-26 - limit by highest offset
      //$this->sql_query .= " LIMIT " . $offset . ", " . $this->number_of_rows_per_page;
      $this->sql_query .= " LIMIT " . max((int)$offset, 0) . ", " . $this->number_of_rows_per_page;
      //EOF - DokuMan - 2010-08-26 - limit by highest offset
    }

    // class functions

    // display split-page-number-links
    function display_links($max_page_links, $parameters = '') {
      global $PHP_SELF, $request_type;

      $display_links_string = '';
      $display_links_array = array();

      $class = 'class="pageResults"';

      $parameters = str_replace('&amp;', '&', $parameters);
      
      if (xtc_not_null($parameters) && (substr($parameters, -1) != '&')) {
        $parameters = ltrim($parameters,'&'); //remove left standing '&'
        $parameters .= '&'; //add '&' added to the right
      } 
      
      
      // previous button - not displayed on first page
      if ($this->current_page_number > 1) {
        $display_links_array['previous'] = '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . ($this->current_page_number - 1), $request_type) . '" '.$class.' title="' . PREVNEXT_TITLE_PREVIOUS_PAGE . '">' . PREVNEXT_BUTTON_PREV . '</a>';
        $display_links_string .= $display_links_array['previous'].'&nbsp;&nbsp;';
      }
      
      // check if number_of_pages > $max_page_links
      $cur_window_num = (int)($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = (int)($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

      // previous window of pages
      if ($cur_window_num > 1) {
        $display_links_array['previouspages'] = '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" '.$class.' title="' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . '">...</a>';
        $display_links_string .= $display_links_array['previouspages'];
      }
      
      // page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if ($jump_to_page == $this->current_page_number) {
          $display_links_array['pages']['current'] = $jump_to_page;
          $display_links_string .= '&nbsp;<strong>' . $jump_to_page . '</strong>&nbsp;';
        } else {
          $display_links_array['pages'][$jump_to_page] = '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . $jump_to_page, $request_type) . '" '.$class.' title="' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . '">' . $jump_to_page . '</a>';
          $display_links_string .= '&nbsp;'.$display_links_array['pages'][$jump_to_page].'&nbsp;';
        }
      }

      // next window of pages
      if ($cur_window_num < $max_window_num) {
        $display_links_array['nextpages'] = '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" '.$class.' title="' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . '">...</a>';
        $display_links_string .= $display_links_array['nextpages'].'&nbsp;';
      }
      
       // next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) {
        $display_links_array['next'] = '<a href="' . xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" '.$class.' title="' . PREVNEXT_TITLE_NEXT_PAGE . '">' . PREVNEXT_BUTTON_NEXT . '</a>';
        $display_links_string .= '&nbsp;'.$display_links_array['next'].'&nbsp;';
      }
      
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/pagination.html')) {
        return $display_links_array;
      }
      return $display_links_string;
    }

    // display number of total products found
    function display_count($text_output) {
      $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }
  }
?>