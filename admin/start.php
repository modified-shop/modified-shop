<?php
  /* --------------------------------------------------------------
   $Id: start.php 4738 2013-05-07 15:57:00Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project
   (c) 2002-2003 osCommerce coding standards (a typical file) www.oscommerce.com
   (c) 2003 nextcommerce (start.php,1.5 2004/03/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (start.php 1235 2005-09-21)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_validate_vatid_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'xtc_js_lang.php');
require_once (DIR_FS_INC.'get_external_content.inc.php');
require_once (DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$time_last_click = 900;
if (defined('WHOS_ONLINE_TIME_LAST_CLICK')) {
  $time_last_click = (int)WHOS_ONLINE_TIME_LAST_CLICK;
}
$xx_mins_ago = (time() - $time_last_click);

// remove entries that have expired
xtc_db_query("DELETE FROM " . TABLE_WHOS_ONLINE . " WHERE time_last_click < '" . $xx_mins_ago . "'");

// customer stats
$customers_query = xtc_db_query("SELECT cs.customers_status_name cust_group, 
                                        count(*) cust_count   
                                   FROM " . TABLE_CUSTOMERS . " c
                                   JOIN " . TABLE_CUSTOMERS_STATUS . " cs 
                                        ON cs.customers_status_id = c.customers_status
                                  --  exclude admin
                                  WHERE c.customers_status > 0
                                    -- restrict to current language setting
                                    AND cs.language_id = '" . (int) $_SESSION['languages_id'] . "'
                               GROUP BY 1
                                  UNION (SELECT '" . TOTAL_CUSTOMERS . "', count(*)   
                                           FROM " . TABLE_CUSTOMERS . ")
                               ORDER BY 2 DESC");
// save query result
$customers = array();
while ($row = xtc_db_fetch_array($customers_query)) {
  $customers[] = $row;
}

// newsletter
$newsletter_query = xtc_db_query("SELECT count(*) as count 
                                    FROM " . TABLE_NEWSLETTER_RECIPIENTS. " 
                                   WHERE mail_status='1'");
$newsletter = xtc_db_fetch_array($newsletter_query);
  
// products  
$products_query = xtc_db_query("SELECT count(if(products_status = 0, products_id, null)) inactive_count,
                                       count(if(products_status = 1, products_id, null)) active_count, 
                                       count(*) total_count 
                                  FROM ".TABLE_PRODUCTS);
$products = xtc_db_fetch_array($products_query);            
    
// orders (status)    
$orders_query = xtc_db_query("SELECT os.orders_status_name 
                                     status, 
                                     coalesce(o.order_count, 0) order_count
                                FROM " . TABLE_ORDERS_STATUS . " os
                           LEFT JOIN (SELECT orders_status, 
                                             count(*) order_count
                                        FROM " . TABLE_ORDERS . " 
                                    GROUP BY 1) o 
                                     ON o.orders_status = os.orders_status_id
                               WHERE os.language_id = '" . (int) $_SESSION['languages_id'] . "'
                            ORDER BY os.orders_status_id");
$orders = array();
$orders_status_validating = xtc_db_num_rows(xtc_db_query("SELECT orders_status 
                                                            FROM " . TABLE_ORDERS ." 
                                                           WHERE orders_status ='0'"));
$orders[] = array('status' => TEXT_VALIDATING, 'order_count' => $orders_status_validating);
while ($row = xtc_db_fetch_array($orders_query)) {
  $orders[] = $row;
}

// specials 
$specials_query = xtc_db_query("SELECT count(*) as specials_count FROM " . TABLE_SPECIALS);
$specials = xtc_db_fetch_array($specials_query);

// turnover
$where = '';
if (ORDER_STATUSES_FOR_SALES_STATISTICS != '') {
  $status_array = explode(',', ORDER_STATUSES_FOR_SALES_STATISTICS);
  $where = " AND o.orders_status IN ('".implode("','", $status_array)."') ";
}
$turnover_query = xtc_db_query("SELECT round(coalesce(sum(if(date(o.date_purchased) = current_date, ot.value/o.currency_value, null)), 0), 2) today,
                                       round(coalesce(sum(if(date(o.date_purchased) = current_date - interval 1 day, ot.value/o.currency_value, null)), 0), 2) yesterday,
                                       round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date), ot.value/o.currency_value, null)), 0), 2) this_month,
                                       round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year), ot.value/o.currency_value, null)), 0), 2) this_month_last_year,
                                       round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year_month), ot.value/o.currency_value, null)), 0), 2) last_month,
                                       round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year_month - interval 1 year), ot.value/o.currency_value, null)), 0), 2) last_month_last_year,
                                       round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year_month) and o.orders_status <> 1, ot.value/o.currency_value, null)), 0), 2) last_month_paid,
                                       round(coalesce(sum(if(extract(year from o.date_purchased) = extract(year from current_date - interval 1 year), ot.value/o.currency_value, null)), 0), 2) last_year,
                                       round(coalesce(sum(if(extract(year from o.date_purchased) = extract(year from current_date), ot.value/o.currency_value, null)), 0), 2) this_year,
                                       round(coalesce(sum(ot.value/o.currency_value), 0), 2) total  
                                  FROM " . TABLE_ORDERS . " o
                                  JOIN " . TABLE_ORDERS_TOTAL . " ot 
                                       ON ot.orders_id = o.orders_id
                                 WHERE ot.class = 'ot_total'
                                       ".$where);
$turnover = xtc_db_fetch_array($turnover_query);  

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript" src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
  <script type="text/javascript">
    $(function() {
      $('.blog_title').click(function(e) {
        var the_block = $(this).next('.blogentry');
        var the_active_block = $(this);
        
        $('.blog_title + .blogentry').not(the_block).slideUp(300);
        $('.blog_title').not(the_active_block).removeClass('active');
        the_active_block.toggleClass('active');
        
        if (the_active_block.hasClass('active')) {
          the_block.slideDown(300);
        } else {
          the_block.slideUp(300);
        }          
      });
    });
  </script>
  <style type="text/css">
    h1 {
      font-size:18px;
      font-weight:bold;
      padding-left:10px;
    }
    .h2 {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 13pt;
      font-weight: bold;
    }
    .h3 {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 9pt;
      font-weight: bold;
    }
    .startphp td {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 12px;
      padding:5px;
    }
    .startphp td div a, .startphp td.infoBoxHeading a {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 12px;
      padding:0px;
    }
    .feedtitle a {
      font-size:12px;
      font-weight:bold;
    }
    .feedtitle ul li {
      list-style-type:disc;
    }
  </style>
</head>
<body>   
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <?php include(DIR_WS_MODULES.FILENAME_SECURITY_CHECK); ?>
  <!-- header_eof //-->
  <table class="tableBody pdg5">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter" style="border:0;background:#fff;">
        <div class="admin_container cf">
          <div class="admincol_left">
          <?php
            // tabs
            $tabs_array = array();
            if($admin_access['whos_online'] == 1) { 
              $tabs_array[] = array('id' => 'online', 'name' => TABLE_CAPTION_USERS_ONLINE);
            }
            if($admin_access['orders'] == 1) { 
              $tabs_array[] = array('id' => 'orders', 'name' => TABLE_CAPTION_NEW_ORDERS);
            }
            if($admin_access['customers'] == 1) { 
              $tabs_array[] = array('id' => 'customer', 'name' => TABLE_CAPTION_NEW_CUSTOMERS);
            }
            if($admin_access['stats_sales_report'] == 1
              || $admin_access['categories'] == 1
              || $admin_access['customers'] == 1
              || $admin_access['orders'] == 1
              ) 
            { 
              $tabs_array[] = array('id' => 'statistic', 'name' => HEADING_CAPTION_STATISTIC);
            }
            $langtabs = '<div class="tablangmenu"><ul>';
            $csstabstyle = 'border: 1px solid #aaaaaa; padding: 4px; width: 99%; margin-top: -1px; margin-bottom: 10px; float: left;background: #F3F3F3;';
            $csstab = '<style type="text/css">' .  '#tab_lang_0' . '{display: block;' . $csstabstyle . '}';
            $csstab_nojs = '<style type="text/css">';
            for ($i = 0, $n = count($tabs_array); $i < $n; $i++) {
              $tabtmp = "\'tab_lang_$i\'," ;
              $langtabs.= '<li onclick="showTab('. $tabtmp. $n.')" style="cursor: pointer;" id="tabselect_' . $i .'">' .$tabs_array[$i]['name'].  '</li>';
              if($i > 0) $csstab .= '#tab_lang_' . $i .'{display: none;' . $csstabstyle . '}';
              $csstab_nojs .= '#tab_lang_' . $i .'{display: block;' . $csstabstyle . '}';
            }
            $csstab .= '</style>';
            $csstab_nojs .= '</style>';
            $langtabs.= '</ul></div>';
            ?>
            <?php if (USE_ADMIN_LANG_TABS != 'false') { ?>
            <script type="text/javascript">
              $.get("includes/lang_tabs_menu/lang_tabs_menu.css", function(css) {
                $("head").append("<style type='text/css'>"+css+"<\/style>");
              });
              document.write('<?php echo ($csstab);?>');
              document.write('<?php echo ($langtabs);?>');
            </script>
            <style type="text/css">
              .tablangmenu {
                border-left: 1px solid #aaaaaa;
                font-family: Verdana,Arial,Helvetica,sans-serif;
                font-size: 12px !important;
                font-weight: bold;
                height: auto !important;
                line-height: 16px;
                padding: 0;
              }  
              #tab_lang_0, #tab_lang_1, #tab_lang_2, #tab_lang_3 {
                background: none repeat scroll 0 0 #f9f9f9;
                border: solid #aaaaaa;
                border-width: 1px;
                margin-top: 0;
                padding: 10px 10px 20px 10px;
                width: 100%;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
              }
              .tablangmenu ul li {
                border-right: 1px solid #aaaaaa;
                border-top: 1px solid #aaaaaa;
                color: #aaaaaa;
                display: inline;
                float: left;
                padding: 8px 20px !important;
                position: relative;
                width: auto !important;
              }
              .tablangmenu ul li:first-child {
                border-left:1px solid #aaa !important;
              }
            </style>
            <?php 
            } else { 
              echo ($csstab_nojs);
            }
            ?>
            <noscript>
              <?php echo ($csstab_nojs);?>
            </noscript>
            <?php
            for ($i = 0, $n = count($tabs_array); $i < $n; $i++) {
              echo ('<div id="tab_lang_' . $i . '">');
              switch($tabs_array[$i]['id']) {
                case 'online':
                  if ($admin_access['whos_online'] == 1) { 
                    ?>
                      <p><?php echo TABLE_CAPTION_USERS_ONLINE_HINT; ?></p>
                      <table class="admin_table">
                        <tr class="header_row">
                          <td><?php echo TABLE_HEADING_USERS_ONLINE_SINCE; ?></td>
                          <td><?php echo TABLE_HEADING_USERS_ONLINE_NAME; ?></td>
                          <td><?php echo TABLE_HEADING_USERS_ONLINE_LAST_CLICK; ?></td>
                          <td><?php echo TABLE_HEADING_USERS_ONLINE_INFO; ?></td>
                        </tr>
                        <?php
                          $whos_online_query = xtc_db_query("SELECT *
                                                               FROM " . TABLE_WHOS_ONLINE ." 
                                                           ORDER BY time_last_click DESC 
                                                              LIMIT ".MAX_DISPLAY_SEARCH_RESULTS);
                          while ($whos_online = xtc_db_fetch_array($whos_online_query)) { 
                            $time_online = (time() - $whos_online['time_entry']); 
                            ?>
                        <tr class="content_row">
                          <td><a href="<?php echo xtc_href_link(FILENAME_WHOS_ONLINE, 'info='.$whos_online['session_id']); ?>"><?php echo gmdate('H:i:s', $time_online); ?></a></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_WHOS_ONLINE, 'info='.$whos_online['session_id']); ?>"><?php echo $whos_online['full_name']; ?></a></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_WHOS_ONLINE, 'info='.$whos_online['session_id']); ?>"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></a></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_WHOS_ONLINE, 'info='.$whos_online['session_id']); ?>"><strong><?php echo TABLE_CELL_USERS_ONLINE_INFO; ?></strong></a></td>
                        </tr>
                        <?php } ?>          
                      </table>
                    <?php 
                  } 
                  break;
        
                case 'orders':
                  if ($admin_access['orders'] == 1) { 
                    ?>
                    <table class="admin_table">
                      <tr class="header_row">
                        <td><?php echo TABLE_HEADING_NEW_ORDERS_ORDER_NUMBER; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_ORDERS_ORDER_DATE; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_ORDERS_CUSTOMERS_NAME; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_ORDERS_EDIT; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_ORDERS_DELETE; ?></td>
                      </tr>
                      <?php
                      $last_orders_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 20");
                      while ($last_orders = xtc_db_fetch_array($last_orders_query)) {
                      ?>
                        <tr class="content_row">
                          <td><?php echo $last_orders['orders_id']; ?></td>
                          <td><?php echo $last_orders['date_purchased']; ?></td>
                          <td><?php echo $last_orders['delivery_name']; ?></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_ORDERS, 'page=1&oID='.$last_orders['orders_id'].'&action=edit'); ?>"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_EDIT; ?></strong></a></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_ORDERS, 'page=1&oID='.$last_orders['orders_id'].'&action=delete'); ?>"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_DELETE; ?></strong></a></td>
                        </tr>
                      <?php } ?>
                    </table>
                    <?php 
                  }
                  break;
        
                case 'customer':
                  if ($admin_access['customers'] == 1) { 
                    ?>
                    <table class="admin_table">
                      <tr class="header_row">
                        <td><?php echo TABLE_HEADING_NEW_CUSTOMERS_LASTNAME; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_CUSTOMERS_FIRSTNAME; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_CUSTOMERS_REGISTERED; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_CUSTOMERS_EDIT; ?></td>
                        <td><?php echo TABLE_HEADING_NEW_CUSTOMERS_ORDERS; ?></td>
                      </tr>
                      <?php
                      $last_customers_query = xtc_db_query("SELECT * 
                                                              FROM " . TABLE_CUSTOMERS . " 
                                                          ORDER BY customers_date_added DESC 
                                                             LIMIT 15");
                      while($last_customers = xtc_db_fetch_array($last_customers_query)) {
                        ?>
                        <tr class="content_row">
                          <td><?php echo $last_customers['customers_lastname']; ?></td>
                          <td><?php echo $last_customers['customers_firstname']; ?></td>
                          <td><?php echo $last_customers['customers_date_added']; ?></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, 'page=1&cID='.$last_customers['customers_id'].'&action=edit'); ?>"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_EDIT; ?></strong></a></td>
                          <td><a href="<?php echo xtc_href_link(FILENAME_ORDERS, 'cID='.$last_customers['customers_id']); ?>"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_ORDERS; ?></strong></a></td>
                        </tr>
                      <?php } ?>      
                    </table>
                    <?php 
                  }
                  break;
                
                case 'statistic':
                  if ($admin_access['stats_sales_report'] == 1) {
                  ?>
                    <table class="admin_table">
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_TODAY; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['today']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_YESTERDAY; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['yesterday']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_THIS_MONTH; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['this_month']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_THIS_MONTH. '&nbsp;' . (strftime("%Y")-1); ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['this_month_last_year']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_LAST_MONTH_PAID; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['last_month_paid']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_LAST_MONTH; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['last_month']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TURNOVER_LAST_MONTH. '&nbsp;' . (strftime("%Y")-1); ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['last_month_last_year']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TOTAL_TURNOVER. '&nbsp;' . (strftime("%Y")-1); ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['last_year']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TOTAL_TURNOVER. '&nbsp;' . strftime("%Y"); ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['this_year']); ?></td>
                      </tr>
                      <tr class="content_row">
                         <td><strong><?php echo TOTAL_TURNOVER; ?>:</strong></td>
                         <td align="right"><?php echo $currencies->format($turnover['total']); ?></td>
                      </tr>
                    </table>
                    <br />
                    <br />
                    <?php } ?>
                    
                    <?php if($admin_access['customers'] == 1) { ?>
                    <table class="admin_table">
                      <?php
                        foreach ($customers as $customer) {
                          echo '<tr class="content_row"><td><strong>' . $customer['cust_group'] . ':</strong></td>';
                          echo '<td align="right">' . $customer['cust_count'] . '</td></tr>';
                        }
                      ?>
                      <tr class="content_row">
                        <td><strong><?php echo TOTAL_SUBSCRIBERS; ?>:</strong></td>
                        <td align="right"><?php echo $newsletter['count']; ?></td>
                      </tr>
                    </table>
                    <?php } ?>
                    
                    <?php if($admin_access['categories'] == 1) { ?>
                    <table class="admin_table">
                      <tr class="content_row">
                        <td><strong><?php echo TOTAL_PRODUCTS_ACTIVE; ?>:</strong></td>
                        <td align="right"><?php echo $products['active_count']; ?></td>
                      </tr>
                      <tr class="content_row">
                        <td><a href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'search_inactive=1');?>"><strong><?php echo TOTAL_PRODUCTS_INACTIVE; ?>:</strong></a></td>
                        <td align="right"><a href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'search_inactive=1');?>"><?php echo $products['inactive_count']; ?></a></td>
					            </tr>
                      <tr class="content_row">
                        <td><strong><?php echo TOTAL_PRODUCTS; ?>:</strong></td>
                        <td align="right"><?php echo $products['total_count'] ?></td>
                      </tr>
                      <tr class="content_row">
                        <td><strong><?php echo TOTAL_SPECIALS; ?>:</strong></td>
                        <td align="right"><?php echo $specials['specials_count']; ?></td>
                      </tr>
                    </table>
                    <br />
                    <br />
                   <?php } ?>
                    
                    <?php if($admin_access['orders'] == 1) { ?>
                    <table class="admin_table">
                    <?php
                      foreach ($orders as $order) {
                        echo '<tr class="content_row"><td><strong>' . $order['status'] . ':</strong></td>';
                        echo '<td align="right">' . $order['order_count'] . '</td></tr>';
                      }
                    ?>   
                    </table>
                  <?php
                  break;
                }
              }
              echo ('</div>');
            }
          ?>
          </div>
          <div class="admincol_right">
            <div class="admin_headline"><a target="_blank" href="<?php echo RSS_FEED_LINK; ?>"><?php echo RSS_FEED_TITLE; ?></a></div>
            <div class="admin_contentbox">
              <?php
              $news_query = xtc_db_query("SELECT * FROM newsfeed ORDER BY news_date DESC");
              if (xtc_db_num_rows($news_query) > 0) {
                $i = 0;
                while ($news = xtc_db_fetch_array($news_query)) {
                  $pagebreak = strpos($news['news_text'], '<tt class="bbc_tt"></tt>');
                  $news['news_text'] = str_replace('src="http://', 'src="https://', $news['news_text']);
                  ?>
                  <div class="blog_title<?php echo (($i == 0) ? ' active' : ''); ?>"><?php echo $news['news_title']; ?></div>                          
                  <div class="blogentry" <?php echo (($i != 0) ? ' style="display:none; margin-top:2px;"' : ''); ?>>
                    <div class="blog_date"><?php echo date('d.m.Y', $news['news_date']); ?></div>                                      
                    <div class="blog_desc"><?php echo (($pagebreak !== false) ? substr($news['news_text'], 0, $pagebreak) : $news['news_text']); ?></div>
                    <div class="blog_read_more"><a target="_blank" href="<?php echo $news['news_link']; ?>">weiterlesen &raquo;</a></div>
                  </div>
                  <?php
                  $i ++;
                }
              } else {
              ?>
                <div class="blogentry">
                  <div class="blog_title"><?php echo RSS_FEED_ALTERNATIVE; ?></div>                          
                  <div class="blog_desc"><?php echo RSS_FEED_DESCRIPTION; ?></div>
                </div>
              <?php
              }
            ?>           
            </div>
        </div>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>