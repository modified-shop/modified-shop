<?php
  /* --------------------------------------------------------------
   $Id: econda.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project 
   (c) 2002-2003 osCommerce coding standards (a typical file) www.oscommerce.com
   (c) 2003 nextcommerce (start.php,1.5 2004/03/17); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require ('includes/application_top.php');

  define('TRACKING_ECONDA_ACTIVE_TITLE','ECONDA Shop Monitor aktivieren ?');
  define('TRACKING_ECONDA_ACTIVE_DESC','Wenn auf true gesetzt, wird der ECONDA Shop Monitor gestartet.');
  define('TRACKING_ECONDA_ID_TITLE','Aktivierungscode');
  define('TRACKING_ECONDA_ID_DESC','Geben Sie ihren Aktivierungscode ein, den Sie von ECONDA erhalten.<br />Einen 14-t&auml;gigen, kostenlosen Testzugang k&ouml;nnen Sie <a href="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_new">[HIER]</a> anfordern!');
  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'save':
        $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '23' order by sort_order");
        while ($configuration = xtc_db_fetch_array($configuration_query))
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value='".$_POST[$configuration['configuration_key']]."' where configuration_key='".$configuration['configuration_key']."'");
        xtc_redirect(xtc_href_link('econda.php'));
        break;

    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_EXTERNAL; ?>econda/style.css" />
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td style="border: 0px solid; border-color: #ffffff;">
                <!--// BOF - GTB - 2011-02-01 - move Econda to External
                <p><img src="../includes/econda/econda.jpg" width="688" height="150"></p> -->
                <p><img src="<?php echo DIR_WS_EXTERNAL; ?>econda/econda.jpg" width="688" height="150"></p>
                <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                <p>&nbsp;</p>
                <table border="0" cellspacing="0" cellpadding="0" class="text_box">
                  <tbody>
                    <tr>
                      <th colspan="3">Aktivierung </th>
                    </tr>
                    <tr>
                      <td class="text_box_image"><br />      </td>
                      <td class="text_box_text">
                        <p>
                          <?php echo xtc_draw_form('configuration', 'econda.php', 'action=save'); ?>
                            <table width="100%"  border="0" cellspacing="0" cellpadding="4">
                              <?php
                                $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '23' order by sort_order");

                                while ($configuration = xtc_db_fetch_array($configuration_query)) {
                                  if (xtc_not_null($configuration['use_function'])) {
                                    $use_function = $configuration['use_function'];
                                    if (preg_match('/->/', $use_function)) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
                                      $class_method = explode('->', $use_function);
                                      if (!is_object(${$class_method[0]})) {
                                        include(DIR_WS_CLASSES . $class_method[0] . '.php');
                                        ${$class_method[0]} = new $class_method[0]();
                                      }
                                      $cfgValue = xtc_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                                    } else {
                                      $cfgValue = xtc_call_function($use_function, $configuration['configuration_value']);
                                    }
                                  } else {
                                    $cfgValue = $configuration['configuration_value'];
                                  }

                                  if (((!$_GET['cID']) || (@$_GET['cID'] == $configuration['configuration_id'])) && (!$cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                                    $cfg_extra_query = xtc_db_query("select configuration_key,configuration_value, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . $configuration['configuration_id'] . "'");
                                    $cfg_extra = xtc_db_fetch_array($cfg_extra_query);
                                    $cInfo_array = array_merge($configuration, $cfg_extra);
                                    $cInfo = new objectInfo($cInfo_array);
                                  }
                                  if ($configuration['set_function']) {
                                    eval('$value_field = ' . $configuration['set_function'] . '"' . encode_htmlspecialchars($configuration['configuration_value']) . '");');
                                  } else {
                                    $value_field = xtc_draw_input_field($configuration['configuration_key'], $configuration['configuration_value'],'size=40');
                                  }
                                  // add
                                  if (strstr($value_field,'configuration_value'))
                                    $value_field=str_replace('configuration_value',$configuration['configuration_key'],$value_field);
                                  echo '
                                        <tr>
                                          <td width="300" valign="top" class="dataTableContent"><strong>'.constant(strtoupper($configuration['configuration_key'].'_TITLE')).'</strong></td>
                                          <td valign="top" class="dataTableContent">
                                          <table width="100%"  border="0" cellspacing="0" cellpadding="2">
                                            <tr>
                                              <td style="background-color:#FCF2CF ; border: 1px solid; border-color: #CCCCCC;" class="dataTableContent">'.$value_field.'</td>
                                            </tr>
                                          </table>
                                          <br />'.constant(strtoupper( $configuration['configuration_key'].'_DESC')).'</td>
                                        </tr>
                                       ';

                                }
                              ?>
                            </table>
                            <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
                          </form>
                        </p>
                      </td>
                      <td class="text_box_lik">&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
                <table border="0" cellspacing="0" cellpadding="0" class="text_box">
                  <tbody>
                    <tr>
                      <th colspan="3">Web-Controlling f&uuml;r Profis </th>
                    </tr>
                    <tr>
                      <td class="text_box_image">
                        <a href ="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_blank">
                        <!--// BOF - GTB - 2011-02-01 - move Econda to External
                        <img src="../includes/econda/gfx/exitbereiche.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" /> -->
                        <img src="<?php echo DIR_WS_EXTERNAL; ?>econda/gfx/exitbereiche.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" />
                        <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                        </a>
                        <br />
                        <br />
                        <a href ="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_blank">
                        <!--// BOF - GTB - 2011-02-01 - move Econda to External
                        <img src="../includes/econda/gfx/diff.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" /> -->
                        <img src="<?php echo DIR_WS_EXTERNAL; ?>econda/gfx/diff.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" />
                        <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                        </a>
                      </td>
                      <td class="text_box_text">
                        <p>
                          <strong>ECONDA</strong> f&uuml;r Ihre modified eCommerce Shopsoftware
                          <br />
                          <br />
                          ECONDA Shop Monitor liefert Ihnen ab 49,90 Euro im Monat mehr als 120 wertvolle Statistiken zum Controlling Ihrer modified eCommerce Shopsoftware, wie u.a:</p>
                          <ul>
                            <li>Kaufprozess- und Warenkorbanalysen</li>
                            <li>Alle Informationen zu Ihren Werbekampagnen (Adwords, Preisvergleicher, Affiliates, Banner, etc.), wie:
                              <ul>
                                <li>Welche Keywords bringen wirklich Umsatz?</li>
                                <li>Welche Google Partner muss ich ausschlie&szlig;en?</li>
                                <li>Wie kann ich Streuverluste vermeiden?</li>
                                <li>Rechnen Preisvergleicher richtig ab?</li>
                                <li>Welcher Newsletter / welche -meldung liefert wie viel Umsatz?</li>
                              </ul>
                            </li>
                            <li>Kunden und Besucher Live Tracking</li>
                            <li>Detaillierte Produktauswertungen</li>
                            <li>Deutschlands erste Klickbetrugsanalysen, etc.</li>
                          </ul>
                        </p>
                      </td>
                      <td class="text_box_lik">&nbsp;</td>
                    </tr>
                  </tbody>
                </table>

                <table border="0" cellspacing="0" cellpadding="0" class="text_box">
                  <tbody>
                    <tr>
                      <th colspan="3">Empfehlung der modified eCommerce Shopsoftware</th>
                    </tr>
                    <tr>
                      <td class="text_box_image">
                        <a href ="http://http://www.econda.de/" target="_blank">
                        <!--// BOF - GTB - 2011-02-01 - move Econda to External
                        <img src="../includes/econda/gfx/searchConversion.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" /> -->
                        <img src="<?php echo DIR_WS_EXTERNAL; ?>econda/gfx/searchConversion.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" />
                        <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                        </a>
                        <br />
                        <br />
                        <a href ="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_blank">
                        <!--// BOF - GTB - 2011-02-01 - move Econda to External
                        <img src="../includes/econda/gfx/srcumsatz.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" /> -->
                        <img src="<?php echo DIR_WS_EXTERNAL; ?>econda/gfx/srcumsatz.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" />
                        <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                        </a>
                      </td>
                      <td class="text_box_text">
                        <p><strong>ECONDA</strong> Shop Monitor kann durch die Eingabe Ihres pers&ouml;nlichen Aktivierungscodes, den Sie von ECONDA erhalten, gestartet werden. Der Aufwand zur Aktivierung von ECONDA ist damit gleich null. </p>
                        <p>Da die Integration in die modified eCommerce Shopsoftware optimal vorbereitet ist, <strong>sparen Sie bei ECONDA die Einrichtungsgeb&uuml;hr</strong>.</p>
                        Die modified eCommerce Shopsoftware empfiehlt den ECONDA Shop Monitor, weil:
                        <ul class="text_box_text">
                          <li>er deutlich mehr leistet, als alle frei verf&uuml;gbaren Tools, </li>
                          <li>er in allen Paketen die volle Funktionalit&auml;t bietet, </li>
                          <li>er keine Begrenzungen von Seiten, Produkten, o.&auml;. aufweist </li>
                          <li>er datenschutzrechtlich unbedenklich ist und </li>
                          <li>er f&uuml;r jeden Versandhandelsprofi unverzichtbar ist.</li>
                        </ul>              
                        <p>
                          <br />
                          <br />
                          <strong>LINKS: </strong>
                        </p>
                        <ul>
                          <li><a href="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_new"> Online-Demo</a></li>
                          <li><a href="http://www.econda.de/web-analyse/shop-monitor/" target="_new"> Weitere Informationen </a></li>
                        </ul>
                      </td>
                      <td class="text_box_lik">&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
                <table border="0" cellspacing="0" cellpadding="0" class="text_box">
                  <tbody>
                    <tr>
                      <th colspan="3">Kostenlos Testen </th>
                    </tr>
                    <tr>
                      <td class="text_box_image">
                        <br />
                        <a href ="http://www.econda.de/web-analyse/shop-monitor/testen/" target="_blank">
                        <!--// BOF - GTB - 2011-02-01 - move Econda to External
                        <img src="../includes/econda/gfx/crtl.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" /> -->
                        <img src="<?php echo DIR_WS_EXTERNAL; ?>econda/gfx/crtl.png" alt="" width="540" height="360" border ="0" style="width: 132px; height: 88px;" />
                        <!--// EOF - GTB - 2011-02-01 - move Econda to External -->    
                        </a>
                      </td>
                      <td class="text_box_text">
                        <p><strong>Jetzt kostenlos Testen und in 5 Minuten Ihre Statistiken sehen!</strong></p>
                        <p>
                          ECONDA bietet Ihnen gerne einen 14-t&auml;gigen, unverbindlichen und kostenlosen Test inkl. eines pers&ouml;nlichen Optimierungsgespr&auml;chs am Ende der Testphase. <br />
                          Verwenden Sie bitte das folgende Formular, um einen Testaccount zu bestellen. Sie erhalten dann umgehend Ihren Aktivierungscode und Ihre Log-in Informationen von ECONDA. <br />
                        </p>
                        <ul>
                          <p></p>
                        </ul>
                      </td>
                      <td class="text_box_lik">&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>