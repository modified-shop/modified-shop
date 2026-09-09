<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
      if (($pos_group_by < $pos_to) && ($pos_group_by !== false)) $pos_to = $pos_group_by;

      $pos_having = strpos(strtoupper($this->sql_query), ' HAVING', $pos_from);
      if (($pos_having < $pos_to) && ($pos_having !== false)) $pos_to = $pos_having;

      $pos_order_by = strpos(strtoupper($this->sql_query), ' ORDER BY', $pos_from);
      if (($pos_order_by < $pos_to) && ($pos_order_by !== false)) $pos_to = $pos_order_by;

      if (strpos(strtoupper($this->sql_query), 'DISTINCT') !== false || strpos(strtoupper($this->sql_query), 'GROUP BY') !== false) {
        $count_string = 'DISTINCT ' . xtc_db_input($count_key);
      } else {
        $count_string = xtc_db_input($count_key);
      }

      $count_source = substr($query, $pos_from, ($pos_to - $pos_from));

      // a LEFT JOIN nothing references cannot change count(DISTINCT ...)
      if (strpos($count_string, 'DISTINCT ') === 0) {
        $count_source = $this->strip_unused_left_joins($count_source, $count_string);
      }

      $count_query = xtDBquery("SELECT count(" . $count_string . ") as total " . $count_source);
      $count = xtc_db_fetch_array($count_query, true);
      $this->number_of_rows = $count['total'];

      if ($this->number_of_rows_per_page > 0) {
        $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);
      } else {
        $this->number_of_pages = 0;
      }

      if ($this->current_page_number > $this->number_of_pages) {
        $this->current_page_number = $this->number_of_pages;
      }

      $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($offset < 1) $offset = 0;

      $this->sql_query .= " LIMIT " . max((int)$offset, 0) . ", " . $this->number_of_rows_per_page;
    }

    // remove joins the count does not need
    function strip_unused_left_joins($sql, $count_string = '') {
      $masked = $this->mask_sql_noise($sql);
      if ($masked === null) {
        return $sql;
      }

      // shapes this analysis cannot judge are left alone
      if (preg_match('/\bNATURAL\b/i', $masked) || $this->has_top_level_comma($masked)) {
        return $sql;
      }

      $names = $this->get_table_names($masked);
      if ($this->has_unqualified_columns($masked, $names)
          || $this->has_unqualified_columns($count_string, $names)
          )
      {
        return $sql;
      }

      foreach ($this->get_join_clauses($masked) as $clause) {
        if ($clause['left'] !== true) {
          continue;
        }

        $join = substr($masked, $clause['offset'], $clause['length']);
        if (!preg_match('/\bLEFT\s+(?:OUTER\s+)?JOIN\s+`?([a-z0-9_]+)`?\s+(?:AS\s+)?`?([a-z0-9_]*)`?\s*(?:ON\b|\()/is', $join, $parts)) {
          continue;
        }

        $alias = ((isset($parts[2]) && $parts[2] != '' && strtoupper($parts[2]) != 'ON') ? $parts[2] : $parts[1]);

        // blanking keeps the offsets of the remaining clauses valid
        $rest = substr_replace($masked, str_repeat(' ', $clause['length']), $clause['offset'], $clause['length']);
        $reference = '/(?:^|[^a-z0-9_`])`?'.preg_quote($alias, '/').'`?\s*\./i';
        if (preg_match($reference, $rest.' '.$count_string)) {
          continue;
        }

        $masked = $rest;
        $sql = substr_replace($sql, str_repeat(' ', $clause['length']), $clause['offset'], $clause['length']);
      }

      return $sql;
    }

    // blank out literals and comments, they must never look like query structure
    function mask_sql_noise($sql) {
      return preg_replace_callback(
        '/\'(?:\\\\.|\'\'|[^\'\\\\])*\'|"(?:\\\\.|""|[^"\\\\])*"|\/\*.*?\*\/|(?:--[ \t]|#)[^\n]*/s',
        function ($match) {
          return str_repeat(' ', strlen($match[0]));
        },
        $sql
      );
    }

    // a comma outside of brackets means an old style table list
    function has_top_level_comma($sql) {
      $depth = 0;
      for ($i = 0, $n = strlen($sql); $i < $n; $i++) {
        if ($sql[$i] === '(') {
          $depth++;
        } elseif ($sql[$i] === ')') {
          $depth--;
        } elseif ($sql[$i] === ',' && $depth === 0) {
          return true;
        }
      }

      return false;
    }

    // the tables and aliases the query brings in, subqueries included
    function get_table_names($sql) {
      $names = array();
      if (preg_match_all('/\b(?:FROM|JOIN)\s+`?([a-z0-9_]+)`?(?:\s+(?:AS\s+)?`?([a-z0-9_]+)`?)?/i', $sql, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
          $names[] = strtolower($match[1]);
          if (isset($match[2]) && $match[2] != '') {
            $names[] = strtolower($match[2]);
          }
        }
      }

      return $names;
    }

    // a column without a table cannot be assigned to a join
    function has_unqualified_columns($sql, $names) {
      $keywords = array(
        'select', 'from', 'join', 'left', 'right', 'inner', 'cross', 'outer', 'natural', 'straight_join',
        'on', 'using', 'as', 'and', 'or', 'not', 'xor', 'where', 'in', 'is', 'null', 'like', 'rlike',
        'regexp', 'between', 'exists', 'distinct', 'case', 'when', 'then', 'else', 'end', 'asc', 'desc',
        'interval', 'div', 'mod', 'true', 'false', 'unknown', 'binary', 'collate', 'all', 'any', 'some',
        'count', 'ignore', 'force', 'use', 'index', 'key', 'partition', 'soundex', 'escape',
      );

      if (!preg_match_all('/`?\b[a-z_][a-z0-9_]*\b`?/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
        return false;
      }

      foreach ($matches[0] as $match) {
        $token = $match[0];
        $at = $match[1];

        // the column part of a qualified reference
        $back = $at - 1;
        while ($back >= 0 && ($sql[$back] === ' ' || $sql[$back] === "\t" || $sql[$back] === "\n" || $sql[$back] === "\r")) {
          $back--;
        }
        if ($back >= 0 && $sql[$back] === '.') {
          continue;
        }

        // a table qualifier or a function name
        if (preg_match('/^\s*[.(]/', substr($sql, $at + strlen($token)))) {
          continue;
        }

        $name = strtolower(trim($token, '`'));
        if (in_array($name, $keywords) || in_array($name, $names)) {
          continue;
        }

        return true;
      }

      return false;
    }

    // the join clauses of the outer query, subqueries stay untouched
    function get_join_clauses($sql) {
      $pattern = '/\b(?:LEFT\s+(?:OUTER\s+)?JOIN|RIGHT\s+(?:OUTER\s+)?JOIN|INNER\s+JOIN|CROSS\s+JOIN|STRAIGHT_JOIN|JOIN|WHERE)\b/i';
      if (!preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
        return array();
      }

      $clauses = array();
      $depth = 0;
      $scanned = 0;
      $offset = false;
      $left = false;

      foreach ($matches[0] as $match) {
        for (; $scanned < $match[1]; $scanned++) {
          if ($sql[$scanned] === '(') $depth++;
          if ($sql[$scanned] === ')') $depth--;
        }

        if ($depth != 0) {
          continue;
        }

        if ($offset !== false) {
          $clauses[] = array('offset' => $offset, 'length' => ($match[1] - $offset), 'left' => $left);
          $offset = false;
        }

        if (strtoupper($match[0]) === 'WHERE') {
          break;
        }

        $offset = $match[1];
        $left = (stripos($match[0], 'LEFT') === 0);
      }

      if ($offset !== false) {
        $clauses[] = array('offset' => $offset, 'length' => (strlen($sql) - $offset), 'left' => $left);
      }

      return $clauses;
    }

    // display split-page-number-links
    function display_links($max_page_links, $parameters = '') {
      global $PHP_SELF, $request_type;

      $display_links_string = '';
      $display_links_array = array();

      $class = 'pageResults';

      $parameters = str_replace('&amp;', '&', $parameters);
      
      if (xtc_not_null($parameters) && (substr($parameters, -1) != '&')) {
        $parameters = ltrim($parameters,'&'); //remove left standing '&'
        $parameters .= '&'; //add '&' added to the right
      } 
      
      // previous button - not displayed on first page
      if ($this->current_page_number > 1) {
        $display_links_array['previous_data'] = array(
          'LINK' => xtc_href_link(basename($PHP_SELF), $parameters . ((($this->current_page_number - 1) > 1) ? 'page=' . ($this->current_page_number - 1) : ''), $request_type),
          'TITLE' => PREVNEXT_TITLE_PREVIOUS_PAGE,
          'TEXT' => PREVNEXT_BUTTON_PREV,
          'PAGE' => ($this->current_page_number - 1),
        );
        $display_links_array['previous'] = '<a href="' . $display_links_array['previous_data']['LINK'] . '" class="'.$class.'" title="' . $display_links_array['previous_data']['TITLE'] . '">' . $display_links_array['previous_data']['TEXT'] . '</a>';
        $display_links_string .= $display_links_array['previous'].'&nbsp;&nbsp;';
      }
      
      // check if number_of_pages > $max_page_links
      $cur_window_num = (int)($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = (int)($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

      // previous window of pages
      if ($cur_window_num > 1) {
        $display_links_array['previouspages_data'] = array(
          'LINK' => xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . (($cur_window_num - 1) * $max_page_links), $request_type),
          'TITLE' => sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links),
          'TEXT' => '...',
          'PAGE' => (($cur_window_num - 1) * $max_page_links),
        );
        $display_links_array['previouspages'] = '<a href="' . $display_links_array['previouspages_data']['LINK'] . '" class="'.$class.'" title="' . $display_links_array['previouspages_data']['TITLE'] . '">' . $display_links_array['previouspages_data']['TEXT'] . '</a>';
        $display_links_string .= $display_links_array['previouspages'];
      }
      
      // page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        $display_links_array['pages_data'][$jump_to_page] = array(
          'LINK' => xtc_href_link(basename($PHP_SELF), $parameters.(($jump_to_page > 1) ? 'page='.$jump_to_page : ''), $request_type),
          'TITLE' => sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page),
          'TEXT' => $jump_to_page,
          'PAGE' => $jump_to_page,
          'CURRENT' => ($jump_to_page == $this->current_page_number),
        );
        if ($jump_to_page == $this->current_page_number) {
          $display_links_array['pages']['current'] = $display_links_array['pages_data'][$jump_to_page]['TEXT'];
          $display_links_string .= '&nbsp;<strong>' . $jump_to_page . '</strong>&nbsp;';
        } else {
          $display_links_array['pages'][$jump_to_page] = '<a href="' . $display_links_array['pages_data'][$jump_to_page]['LINK'] . '" class="'.$class.'" title="' . $display_links_array['pages_data'][$jump_to_page]['TITLE'] . '">' . $display_links_array['pages_data'][$jump_to_page]['TEXT'] . '</a>';
          $display_links_string .= '&nbsp;'.$display_links_array['pages'][$jump_to_page].'&nbsp;';
        }
      }

      // next window of pages
      if ($cur_window_num < $max_window_num) {
        $display_links_array['nextpages_data'] = array(
          'LINK' => xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . (($cur_window_num) * $max_page_links + 1), $request_type),
          'TITLE' => sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links),
          'TEXT' => '...',
          'PAGE' => (($cur_window_num) * $max_page_links + 1),
        );
        $display_links_array['nextpages'] = '<a href="' . $display_links_array['nextpages_data']['LINK'] . '" class="'.$class.'" title="' . $display_links_array['nextpages_data']['TITLE'] . '">' . $display_links_array['nextpages_data']['TEXT'] . '</a>';
        $display_links_string .= $display_links_array['nextpages'].'&nbsp;';
      }
      
       // next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) {
        $display_links_array['next_data'] = array(
          'LINK' => xtc_href_link(basename($PHP_SELF), $parameters . 'page=' . ($this->current_page_number + 1), $request_type),
          'TITLE' => PREVNEXT_TITLE_NEXT_PAGE,
          'TEXT' => PREVNEXT_BUTTON_NEXT,
          'PAGE' => ($this->current_page_number + 1),
        );
        $display_links_array['next'] = '<a href="' . $display_links_array['next_data']['LINK'] . '" class="'.$class.'" title="' . $display_links_array['next_data']['TITLE'] . '">' . $display_links_array['next_data']['TEXT'] . '</a>';
        $display_links_string .= '&nbsp;'.$display_links_array['next'].'&nbsp;';
      }
      
      if (defined('TEMPLATE_PAGINATION') && TEMPLATE_PAGINATION == 'true') {
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
