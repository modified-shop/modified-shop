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

define('RSS_FEED_TITLE', 'modified eCommerce Shopsoftware Support Forum - Ank&uuml;ndigungen / Neuigkeiten');
define('RSS_FEED_DESCRIPTION', 'Aktuelle Information von modified eCommerce Shopsoftware Support Forum');
define('RSS_FEED_LINK', 'http://www.modified-shop.org/blog');
define('RSS_FEED_ALTERNATIVE', 'Leider k&ouml;nnen die aktuellen Neuigkeiten nicht im RSS Feed dargestellt werden. Bitte besuchen sie unseren Blog unter <a href="'.RSS_FEED_LINK.'">www.modified-shop.org/blog</a> um wichtige Informationen f&uuml;r Shopbetreiber zu diesen Themen zu erfahren: <ul><li>Wichtige Updates und Fixes</li><li>Funktionserweiterungen</li><li>Rechtssprechungen</li><li>Neuigkeiten</li><li>Klatsch und Tratsch</li></ul>');

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_validate_vatid_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'xtc_js_lang.php');
require_once (DIR_FS_INC.'get_external_content.inc.php');
require_once (DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

function convert_utf8 ($string) {
  if (strtolower($_SESSION['language_charset'] == 'utf-8')) {
    return $string;
  } else {
    return utf8_decode($string);
  }
}

$time_last_click = 900;
if (defined('WHOS_ONLINE_TIME_LAST_CLICK')) {
  $time_last_click = (int)WHOS_ONLINE_TIME_LAST_CLICK;
}
$xx_mins_ago = (time() - $time_last_click);

$customers_statuses_array = xtc_get_customers_statuses();
// remove entries that have expired
xtc_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

$language_id = (int) $_SESSION['languages_id'];
// customer stats
$customers_query = xtc_db_query('select cs.customers_status_name cust_group, count(*) cust_count   
                     from ' . TABLE_CUSTOMERS . ' c
                     join ' . TABLE_CUSTOMERS_STATUS . ' cs on cs.customers_status_id = c.customers_status
                     --  exclude admin
                     where c.customers_status > 0
                     -- restrict to current language setting
                     and cs.language_id = ' . $language_id . '
                     group by 1
                     union
                     select \'' . TOTAL_CUSTOMERS . '\', count(*)   
                     from ' . TABLE_CUSTOMERS . '
                     order by 2 desc');
// save query result
$customers = array();
while ($row = xtc_db_fetch_array($customers_query))
  $customers[] = $row;

// newsletter
$newsletter_query = xtc_db_query("select count(*) as count 
                    from " . TABLE_NEWSLETTER_RECIPIENTS. " where mail_status='1'");
$newsletter = xtc_db_fetch_array($newsletter_query);
  
// products  
$products_query = xtc_db_query('select 
                  count(if(products_status = 0, products_id, null)) inactive_count,
                  count(if(products_status = 1, products_id, null)) active_count, 
                  count(*) total_count 
                  from ' . TABLE_PRODUCTS);
$products = xtc_db_fetch_array($products_query);            
    
// orders (status)    
$orders_query = xtc_db_query('select os.orders_status_name status, coalesce(o.order_count, 0) order_count
                from ' . TABLE_ORDERS_STATUS . ' os
                left join (select orders_status, count(*) order_count
                           from ' . TABLE_ORDERS . ' 
                           group by 1) o on o.orders_status = os.orders_status_id
                where os.language_id = ' . $language_id . '
                order by os.orders_status_id');
$orders = array();
while ($row = xtc_db_fetch_array($orders_query))
  $orders[] = $row;
// specials 
$specials_query = xtc_db_query("select count(*) as specials_count from " . TABLE_SPECIALS);
$specials = xtc_db_fetch_array($specials_query);
// turnover
$turnover_query = xtc_db_query('select 
  round(coalesce(sum(if(date(o.date_purchased) = current_date, ot.value, null)), 0), 2) today,
  round(coalesce(sum(if(date(o.date_purchased) = current_date - interval 1 day, ot.value, null)), 0), 2) yesterday, 
  round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date), ot.value, null)), 0), 2) this_month,
  round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year_month), ot.value, null)), 0), 2) last_month,
  round(coalesce(sum(if(extract(year_month from o.date_purchased) = extract(year_month from current_date - interval 1 year_month) and o.orders_status <> 1, ot.value, null)), 0), 2) last_month_paid,
  round(coalesce(sum(ot.value), 0), 2) total   
  from ' . TABLE_ORDERS . ' o 
  join ' . TABLE_ORDERS_TOTAL . ' ot on ot.orders_id = o.orders_id 
  where ot.class = \'ot_total\'');
$turnover = xtc_db_fetch_array($turnover_query);  

require (DIR_WS_INCLUDES.'head.php');
?>
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
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <table border="0" width="100%" cellspacing="2" cellpadding="2" class="startphp">
      <tr>
        <td><?php include(DIR_WS_MODULES.FILENAME_SECURITY_CHECK); ?></td>
      </tr>
    </table>
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2" class="startphp">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="boxCenter" width="100%" valign="top">   
                <table border="0" width="100%" cellspacing="0" cellpadding="0">          
                  <tr>
                    <td><h1><?php echo HEADING_TITLE; ?></h1></td>
                  </tr>
                  <tr>
                    <td>
                      <!--  BOF START INFOS STATISTIK -->
                      <table width="100%" border="0" cellspacing="0">
                        <tr>
                          <td width="25%" valign="top">
                          <?php
                            if($admin_access['stats_sales_report'] == 1) {
                            ?>
                            <table width="100%" border="0">
                              <tr>
                                 <td style="background:#eee"><strong><?php echo TURNOVER_TODAY; ?>:</strong></td>
                                 <td  style="background:#eee" align="right"><?php echo $currencies->format($turnover['today']); ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#fff"><strong><?php echo TURNOVER_YESTERDAY; ?>:</strong></td>
                                 <td style="background:#fff" align="right"><?php echo $currencies->format($turnover['yesterday']); ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#eee"><strong><?php echo TURNOVER_THIS_MONTH; ?>:</strong></td>
                                 <td  style="background:#eee" align="right"><?php echo $currencies->format($turnover['this_month']); ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#ccc"><strong><?php echo TURNOVER_LAST_MONTH; ?>:</strong></td>
                                 <td style="background:#ccc" align="right"><?php echo $currencies->format($turnover['last_month']); ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#ccc"><strong><?php echo TURNOVER_LAST_MONTH_PAID; ?>:</strong></td>
                                 <td style="background:#ccc" align="right"><?php echo $currencies->format($turnover['last_month_paid']); ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#666; color:#FFF"><strong><?php echo TOTAL_TURNOVER; ?>:</strong></td>
                                 <td style="background:#666; color:#FFF" align="right"><?php echo $currencies->format($turnover['total']); ?></td>
                              </tr>
                            </table>
                            <?php
                            }
                          ?>
                          </td>
                          <td width="25%" valign="top">
                          <?php
                            if($admin_access['customers'] == 1) {
                            ?>
                            <table width="100%">
                              <?php
                              foreach ($customers as $customer) {
                                echo '<tr><td style="background:#e4e4e4"><strong>' . $customer['cust_group'] . ':</strong></td>';
                                echo '<td style="background:#e4e4e4" align="center">' . $customer['cust_count'] . '</td></tr>';
                              }
                              ?>
                              <tr>
                                 <td style="background:#e4e4e4"><strong><?php echo TOTAL_SUBSCRIBERS; ?>:</strong></td>
                                 <td style="background:#e4e4e4" align="center"><?php echo $newsletter['count']; ?></td>
                              </tr>
                            </table>
                            <?php
                            }
                          ?>
                          </td>
                          <td width="25%" valign="top">
                          <?php
                            if($admin_access['categories'] == 1) {
                            ?>
                            <table width="100%">
                              <tr>
                                 <td style="background:#e4e4e4"><strong><?php echo TOTAL_PRODUCTS_ACTIVE; ?>:</strong></td>
                                 <td style="background:#e4e4e4"><?php echo $products['active_count']; ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#e4e4e4"><strong><?php echo TOTAL_PRODUCTS_INACTIVE; ?>:</strong></td>
                                 <td style="background:#e4e4e4"><?php echo $products['inactive_count']; ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#e4e4e4"><strong><?php echo TOTAL_PRODUCTS; ?>:</strong></td>
                                 <td style="background:#e4e4e4"><?php echo $products['total_count'] ?></td>
                              </tr>
                              <tr>
                                 <td style="background:#e4e4e4"><strong><?php echo TOTAL_SPECIALS; ?>:</strong></td>
                                 <td style="background:#e4e4e4"><?php echo $specials['specials_count']; ?></td>
                              </tr>
                            </table>
                            <?php
                            }
                          ?>
                          </td>
                          <td width="25%" valign="top">
                          <?php
                            if($admin_access['orders'] == 1) {
                            ?>
                              <table width="100%">
                              <?php
                                   foreach ($orders as $order) {
                                        echo '<tr><td style="background:#e4e4e4"><strong>' . $order['status'] . ':</strong></td>';
                                        echo '<td style="background:#e4e4e4">' . $order['order_count'] . '</td></tr>';
                                     }
                                   ?>   
                              </table>
                            <?php
                            }
                          ?>
                          </td>
                        </tr>
                      </table>         
                      <!--  EOF START INFOS STATISTIK -->
                    </td>
                  </tr>
                  <tr>          
                    <td>
                      <table valign="top" width="100%" cellpadding="0" cellspacing="0">     
                        <tr>
                          <td>
                            <!--  BOF START INFOS USER ONLINE + NEUE KUNDEN  + LETZTE BESTELLUNGEN +  NEWSFEED-->
                            <table border="0" width="100%" cellspacing="0">
                              <tr>
                                <td width="48%" class="infoBoxHeading" style="border: 1px solid #b40076; border-bottom: 1px solid #b40076;"><strong><?php echo TABLE_CAPTION_USERS_ONLINE; ?></strong></td>
                                <td width="4%"><p style="margin-left: 3px"></p></td>
                                <td width="48%" class="infoBoxHeading" style="border: 1px solid #b40076; border-bottom: 1px solid #b40076;"><strong><?php echo TABLE_CAPTION_NEWSFEED; ?></strong> <a href="<?php echo RSS_FEED_LINK; ?>" target="_blank">modified eCommerce Shopsoftware - Blog</a></td>
                              </tr>
                              <tr>
                                <?php
                                if($admin_access['whos_online'] == 1) {
                                  ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076;" height="200" valign="top">&nbsp;<em><font color="#7691A2"><?php echo TABLE_CAPTION_USERS_ONLINE_HINT; ?></font></em>
                                    <table border="0" width="98%" cellspacing="0" cellpadding="0">
                                      <tr class="dataTableHeadingRow">
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="22%"><strong><?php echo TABLE_HEADING_USERS_ONLINE_SINCE; ?></strong></td>
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="33%"><strong><?php echo TABLE_HEADING_USERS_ONLINE_NAME; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="33%"><strong><?php echo TABLE_HEADING_USERS_ONLINE_LAST_CLICK; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="33%"><strong><?php echo TABLE_HEADING_USERS_ONLINE_INFO; ?></strong></td>
                                      </tr>
                                      <?php
                                        $whos_online_query = xtc_db_query("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE ." order by time_last_click desc");
                                        while ($whos_online = xtc_db_fetch_array($whos_online_query)) { 
                                          $time_online = (time() - $whos_online['time_entry']); 
                                          if ( ((!$_GET['info']) || (@$_GET['info'] == $whos_online['session_id'])) && (!$info) ) { 
                                            $info = $whos_online['session_id']; 
                                          }     
                                          ?>
                                          <tr>
                                            <td class="dataTableContent" width="22%"><a href="whos_online.php?info=<?php echo $whos_online['session_id']; ?>"><?php echo gmdate('H:i:s', $time_online); ?></a></td>
                                            <td class="dataTableContent" width="33%"><a href="whos_online.php?info=<?php echo $whos_online['session_id']; ?>"><?php echo $whos_online['full_name']; ?></a></td>
                                            <td class="dataTableContent" align="center" width="33%"><a href="whos_online.php?info=<?php echo $whos_online['session_id']; ?>"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></a></td>
                                            <td class="dataTableContent" align="center" width="33%"><a href="whos_online.php?info=<?php echo $whos_online['session_id']; ?>"><font color="#800000"><strong><?php echo TABLE_CELL_USERS_ONLINE_INFO; ?></strong></font></a></td>
                                          </tr>
                                        <?php 
                                        } 
                                      ?>          
                                    </table>
                                  </td>
                                  <?php
                                } else {
                                ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076;" height="200" valign="top">&nbsp;</td>
                                  <?php
                                }
                                ?>
                                <td width="4%">&nbsp;</td>
                                <td style="background: #F9F0F1; border: 1px solid #b40076; min-width:400px;" height="200" valign="top">
                                  <?php
                                  $feed = get_external_content('http://www.modified-shop.org/feed/', 2);    
                                  if ($feed && class_exists('SimpleXmlElement')) {
                                    $rss = new SimpleXmlElement($feed, LIBXML_NOCDATA);
                                    $rss->addAttribute('encoding', 'UTF-8');
                                    ?>
                                    <div style="background:#F0F1F1;font-size:12px; border:1px solid #999; padding:5px; font-weight: 700" align="left">
                                      <a target="_blank" href="<?php echo $rss->channel->link; ?>"><?php echo convert_utf8($rss->channel->title); ?></a>
                                      <br/>
                                      <?php echo convert_utf8($rss->channel->description); ?>
                                    </div>
                                    <br/>
                                    <?php
                                    for ($i=0; $i<=3; $i++) {
                                    ?>
                                      <div class="feedtitle" align="left" style="padding:5px;font-size:12px;">
                                        <a target="_blank" href="<?php echo $rss->channel->item[$i]->link; ?>"><?php echo convert_utf8($rss->channel->item[$i]->title); ?></a>
                                        <br/><br/>
                                        <?php echo convert_utf8($rss->channel->item[$i]->description); ?>
                                      </div>
                                      <hr noshade="noshade">
                                    <?php
                                    }
                                  } else {
                                  ?>
                                    <div style="background:#F0F1F1;font-size:12px; border:1px solid #999; padding:5px; font-weight: 700" align="left">
                                      <a target="_blank" href="<?php echo RSS_FEED_LINK; ?>"><?php echo RSS_FEED_TITLE; ?></a>
                                      <br/>
                                      <?php echo RSS_FEED_DESCRIPTION; ?>
                                    </div>
                                    <br/>
                                    <div class="feedtitle" align="left" style="padding:5px;font-size:12px;">
                                      <?php echo RSS_FEED_ALTERNATIVE; ?>
                                    </div>
                                  <?php
                                  }
                                  ?>
                                </td>
                              </tr>
                              <tr>
                                <td width="48%">&nbsp;</td>
                                <td width="4%">&nbsp;</td>
                                <td width="48%">&nbsp;</td>
                              </tr>
                              <tr>
                                <td width="48%" class="infoBoxHeading" style="border: 1px solid #b40076; border-bottom: 1px solid #b40076;"><strong><?php echo TABLE_CAPTION_NEW_ORDERS; ?><?php echo TABLE_CAPTION_NEW_ORDERS_COMMENT; ?></strong></td>
                                <td width="4%"><p style="margin-left: 3px"></p></td>
                                <td width="48%" class="infoBoxHeading" style="border: 1px solid #b40076; border-bottom: 1px solid #b40076;"><strong><?php echo TABLE_CAPTION_NEW_CUSTOMERS; ?></strong><?php echo TABLE_CAPTION_NEW_CUSTOMERS_COMMENT; ?></td>
                              </tr>
                              <tr>
                                <?php
                                if($admin_access['orders'] == 1) {
                                  ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076; height: 200px;" valign="top">
                                    <table border="0" width="98%" cellspacing="0" cellpadding="0">
                                      <tr class="dataTableHeadingRow">
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="25%"><strong><?php echo TABLE_HEADING_NEW_ORDERS_ORDER_NUMBER; ?></strong></td>
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="25%"><p align="center"><strong><?php echo TABLE_HEADING_NEW_ORDERS_ORDER_DATE; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="25%"><p align="center"><strong><?php echo TABLE_HEADING_NEW_ORDERS_CUSTOMERS_NAME; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="12%"><p align="center"><strong><?php echo TABLE_HEADING_NEW_ORDERS_EDIT; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="12%"><p align="center"><strong><?php echo TABLE_HEADING_NEW_ORDERS_DELETE; ?></strong></td>
                                      </tr>
                                      <?php
                                        $abfrage = "SELECT * FROM " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 20";
                                        $ergebnis = mysql_query($abfrage);
                                        while($row = mysql_fetch_object($ergebnis)){
                                      ?>
                                      <tr>
                                        <td class="dataTableContent" width="25%"><?php echo $row-> orders_id; ?></td>
                                        <td class="dataTableContent" width="25%"><p align="center"><?php echo $row-> date_purchased; ?></td>
                                        <td class="dataTableContent" align="center" width="25%"><?php echo $row-> delivery_name; ?></td>
                                        <td class="dataTableContent" align="center" width="12%"><a href="orders.php?page=1&oID=<?php echo $row-> orders_id; ?>&action=edit"><font color="#7691A2"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_EDIT; ?></strong></font></a></td>
                                        <td class="dataTableContent" align="center" width="12%"><a href="orders.php?page=1&oID=<?php echo $row-> orders_id; ?>&action=delete"><font color="#800000"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_DELETE; ?></strong></font></a></td>
                                      </tr>
                                      <?php
                                        }
                                      ?>
                                    </table>
                                  </td>
                                  <?php
                                } else {
                                ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076;" height="200" valign="top">&nbsp;</td>
                                  <?php
                                }
                                ?>
                                <td>&nbsp;</td>
                                <?php
                                if($admin_access['customers'] == 1) {
                                  ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076;" height="200" valign="top">
                                    <table border="0" width="98%" cellspacing="0" cellpadding="0">
                                      <tr class="dataTableHeadingRow">
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="25%"><strong><?php echo TABLE_HEADING_NEW_CUSTOMERS_LASTNAME; ?></strong></td>
                                        <td class="dataTableHeadingContent" bgcolor="#D9D9D9" height="20" width="25%"><strong><?php echo TABLE_HEADING_NEW_CUSTOMERS_FIRSTNAME; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="25%"><strong><?php echo TABLE_HEADING_NEW_CUSTOMERS_REGISTERED; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="12%"><strong><?php echo TABLE_HEADING_NEW_CUSTOMERS_EDIT; ?></strong></td>
                                        <td class="dataTableHeadingContent" align="center" bgcolor="#D9D9D9" height="20" width="12%"><strong><?php echo TABLE_HEADING_NEW_CUSTOMERS_ORDERS; ?></strong></td>
                                      </tr>
                                      <?php
                                        $abfrage = "SELECT * FROM " . TABLE_CUSTOMERS . " ORDER BY customers_date_added DESC LIMIT 15";
                                        $ergebnis = mysql_query($abfrage);
                                        while($row = mysql_fetch_object($ergebnis)) {
                                      ?>
                                      <tr>
                                        <td class="dataTableContent" width="25%"><?php echo $row-> customers_lastname; ?>
                                        </td>
                                        <td class="dataTableContent" width="25%"><?php echo $row-> customers_firstname; ?>
                                        </td>
                                        <td class="dataTableContent" align="center" width="25%"><?php echo $row-> customers_date_added; ?>
                                        </td>
                                        <td class="dataTableContent" align="center" width="12%"><a href="customers.php?page=1&cID=<?php echo $row-> customers_id; ?>&action=edit"><font color="#800000"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_EDIT; ?></strong></font></a></td>
                                        <td class="dataTableContent" align="center" width="12%"><a href="orders.php?cID=<?php echo $row-> customers_id; ?>"><font color="#7691A2"><strong><?php echo TABLE_CELL_NEW_CUSTOMERS_ORDERS; ?></strong></font></a></td>
                                      </tr>
                                      <?php
                                      } 
                                    ?>       
                                    </table>
                                  </td>
                                  <?php
                                } else {
                                ?>
                                  <td style="background: #F9F0F1; border: 1px solid #b40076;" height="200" valign="top">&nbsp;</td>
                                  <?php
                                }
                                ?>
                              </tr>
                              <?php
                              if($admin_access['customers'] == 1) {
                                ?>
                                <tr>
                                  <td>&nbsp;</td>
                                  <td>&nbsp;</td>
                                  <td>&nbsp;</td>
                                </tr>
                                <tr>
                                  <td colspan="3" style="padding:0px">
                                    <!--  BOF START INFOS GEBURTSTAGSLISTE -->
                                    <table cellpadding="5" cellspacing="0" width="100%" id="table1" class="contentTable">
                                      <tr>
                                        <td class="infoBoxHeading"><strong><?php echo TABLE_CAPTION_BIRTHDAYS; ?></strong></td>
                                      </tr>
                                    </table>
                                    <table cellpadding="5" cellspacing="0" style="border: 1px solid #b40076; border-top:0px;" width="100%" id="AutoNumber1">
                                      <tr>
                                        <td width="100%" colspan="2" bgcolor="#F1F1F1" style="border-bottom: 1px solid #CCCCCC"><strong><?php echo TABLE_CELL_BIRTHDAYS_TODAY; ?>:</strong></td>
                                      </tr>
                                      <?php
                                      $ergebnis = xtc_db_query("select concat(customers_firstname, ' ', customers_lastname) name,
                                                                       customers_dob dob,
                                                                       if(day(customers_dob) = day(current_date), true, false) today
                                                                  from " . TABLE_CUSTOMERS . "
                                                                 where month(customers_dob) = month(current_date) 
                                                                   and day(customers_dob) >= day(current_date)
                                                              order by customers_dob");
                                      $this_month = array();
                                      while($row = xtc_db_fetch_array($ergebnis))
                                      {
                                        if ($row['today'] == 1) {
                                           echo '<tr><td width="68%" bgcolor="#F9F0F1">' . $row['name'] . '</td>';
                                           echo '<td width="32%" bgcolor="#F9F0F1">' . xtc_date_long($row['dob']) . '</td></tr>';
                                        } else {
                                          $this_month[] = array('name' => $row['name'], 'dob' => $row['dob']);
                                        }
                                      }
                                      ?>
                                      <tr>
                                         <td width="100%" colspan="2" style="border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC" bgcolor="#F1F1F1"><strong><?php echo TABLE_CELL_BIRTHDAYS_THIS_MONTH; ?>:</strong></td>
                                      </tr>
                                      <?php
                                      foreach($this_month as $row) {
                                        echo '<tr><td width="68%" bgcolor="#F9F0F1">' . $row['name'] . '</td>';
                                        echo '<td width="32%" bgcolor="#F9F0F1">' . xtc_date_long($row['dob']) . '</td></tr>';
                                      }
                                      ?>
                                    </table>
                                    <!--  EOF START INFOS GEBURTSTAGSLISTE -->
                                  </td>
                                </tr>
                                <?php
                              }
                              ?>
                            </table>
                            <!--  EOF START INFOS USER ONLINE + NEUE KUNDEN  + LETZTE BESTELLUNGEN +  NEWSFEED-->
                          </td>
                        </tr>     
                      </table>
                    </td>          
                  </tr>          
                </table>
              </td>
            </tr>
          </table>
        </td>          
      </tr>  
    </table>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
