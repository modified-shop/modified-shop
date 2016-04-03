<?php
  /* --------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------
   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if (defined('MODULE_ADMIN_LOG_STATUS') && MODULE_ADMIN_LOG_STATUS == 'true') {
    if (MODULE_ADMIN_LOG_DISPLAY == 'true') {
    ?>
      <style type="text/css">
        .log_title {
          cursor: pointer;
        }
        .log_arrow {
          float: left;
          display: block;
          font-size: 20px;
          height: 9px;
          line-height: 6px;
          margin: 8px 10px 0 0;
          overflow: hidden;
          width: 8px;
          background:#fff;
        }
        .log_title.active .log_arrow {
          -moz-transform:rotate(90deg);
          -webkit-transform:rotate(90deg);
          -o-transform:rotate(90deg);
          -ms-transform:rotate(90deg);
          transform:rotate(90deg);
          height:10px;
          margin: 8px 10px 0 0;
        }
        .log_title.active, .log_title:hover {
          color: rgb(190, 50, 50);
        }
        .log_row:nth-child(even) .log_title {
          background: #e8f2e7;
          border-top:1px solid #bdd5bb;
          line-height:26px;
          padding: 0px 5px;
        }
        .log_row:nth-child(odd) .log_title {
          background: #f5f5f5;
          border-top:1px solid #bdd5bb;
          line-height:26px;
          padding: 0px 5px;
        }
        .log_row:last-child {
          border-bottom:1px solid #bdd5bb;
        }
        .log_entry {
          border-top: 1px solid #ccc;
          margin: 0px;
          padding: 5px 24px;
        }
        .log_desc {
          border-left: 5px solid #ccc;
          padding-left: 20px;
        }
      </style>
      <?php if (MODULE_ADMIN_LOG_SHOW_DETAILS == 'true') { ?>
      <script type="text/javascript">
        $(function() {
          $('.log_title').click(function(e) {
            var the_block = $(this).next('.log_entry');
            var the_active_block = $(this);
  
            $('.log_title + .log_entry').not(the_block).slideUp(300);
            $('.log_title').not(the_active_block).removeClass('active');
            the_active_block.toggleClass('active');
  
            if (the_active_block.hasClass('active')) {
              the_block.slideDown(300);
            } else {
              the_block.slideUp(300);
            }
          });
        });
      </script>
      <?php
      }
  
      $where = '';
      switch (basename($PHP_SELF)) {
  
        case 'categories.php':
          if (isset($_GET['action'])) {
            if (isset($_GET['pID']) && $_GET['pID'] != '0') {
              $where = " products_id = '".(int)$_GET['pID']."' ";
            }
            if (isset($_GET['cID']) && $_GET['cID'] != '0') {
              $where = " categories_id = '".(int)$_GET['cID']."' ";
            }
          }
          break;
    
        case 'content_manager.php':
          if (isset($_GET['coID']) && $_GET['coID'] != '0') {
            $where = " content_group = '".(int)$_GET['coID']."' ";
          }
          break;

        case 'manufacturers.php':
          if (isset($_GET['mID']) && $_GET['mID'] != '0') {
            $where = " manufacturers_id = '".(int)$_GET['mID']."' ";
          }
          break;

        case 'modules.php':
        case 'module_export.php':
          if (isset($_GET['module']) && isset($_GET['set'])) {
            $where = " module = '".xtc_db_input($_GET['module'])."' AND type = '".xtc_db_input($_GET['set'])."' ";
          }
          break;

        case 'configuration.php':
          if (isset($_GET['gID']) && $_GET['gID'] != '6') {
            $where = " configuration_id = '".(int)$_GET['gID']."' ";
          }
          break;

        case 'orders.php':
          if (isset($_GET['oID'])) {
            $where = " type = '' AND orders_id = '".(int)$_GET['oID']."' ";
          }
          break;

        case 'orders_edit.php':
          if (isset($_GET['oID'])) {
            $where = " type = '".((isset($_GET['edit_action'])) ? $_GET['edit_action'] : 'edit')."' AND orders_id = '".(int)$_GET['oID']."' ";
          }
          break;
      }
                              
      if ($where != '') {
        $log_query = xtc_db_query("SELECT al.text, 
                                          al.date_modified,
                                          c.customers_firstname,
                                          c.customers_lastname
                                     FROM admin_log al
                                     JOIN ".TABLE_CUSTOMERS." c
                                          ON al.customers_id = c.customers_id
                                    WHERE ".$where);
  
        if (xtc_db_num_rows($log_query) > 0) {
          echo '<div style="max-width:1000px; margin:20px auto; font-family: Verdana,Arial,sans-serif; font-size: 10px;">' . PHP_EOL .
                 '<h2 style="color: rgb(190, 50, 50);">Admin Log:</h2>' . PHP_EOL;
          $lc = 0;
          while ($log = xtc_db_fetch_array($log_query)) {
            if (MODULE_ADMIN_LOG_SHOW_DETAILS == 'true') {
              $log['text'] = unserialize(base64_decode($log['text']));
              if (MODULE_ADMIN_LOG_SHOW_DETAILS_FULL == 'false') {
                if ($lc == 0) {
                  $base = $log['text'];
                }
                $base_new = array_merge($base, $log['text']);
                $log['text'] = arrayRecursiveLogDiff($base, $base_new);
              }
            }
            echo '<div class="log_row">
                    <div class="log_title cf">'.((MODULE_ADMIN_LOG_SHOW_DETAILS == 'true') ? '<div class="log_arrow">&rsaquo;</div>' : '').date('d.m.Y H:i:s', strtotime($log['date_modified'])).'&nbsp;&nbsp;&nbsp;&nbsp;'.$log['customers_firstname'].' '.$log['customers_lastname'].'</div>
                      '.((MODULE_ADMIN_LOG_SHOW_DETAILS == 'true') ? '<div class="log_entry" style="display:none;">
                        <div class="log_desc">
                          <pre>'.((count($log['text']) > 0) ? print_r($log['text'], true) : TEXT_NONE).'</pre>
                        </div>
                      </div>' : '').'
                  </div>';
            if (MODULE_ADMIN_LOG_SHOW_DETAILS == 'true' && MODULE_ADMIN_LOG_SHOW_DETAILS_FULL == 'false') {
              if ($lc != 0) {
                $base = $base_new;
              }
              $lc ++;
            }
          }
          echo '</div>';
        }
      }
    }
  }

  function arrayRecursiveLogDiff($aArray1, $aArray2) {
    $aReturn = array();

    foreach($aArray1 as $mKey => $mValue) {
      if(array_key_exists($mKey, $aArray2)) {
        if(is_array($mValue)) {
          $aRecursiveDiff = arrayRecursiveLogDiff($mValue, $aArray2[$mKey]);
          if(count($aRecursiveDiff)) {
            $aReturn[$mKey] = $aRecursiveDiff;
          }
        } else {
          if((string)$mValue != (string)$aArray2[$mKey]) {
            $aReturn[$mKey] = $aArray2[$mKey];
          }
        }
      } else {
        $aReturn[$mKey] = $mValue;
      }
    }

    return $aReturn;
  }
?>