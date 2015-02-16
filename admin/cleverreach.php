<?php
  /* --------------------------------------------------------------
   $Id: cleverreach.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project 
   (c) 2002-2003 osCommerce coding standards (a typical file) www.oscommerce.com
   (c) 2003  nextcommerce (start.php,1.5 2004/03/17); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   --------------------------------------------------------------*/

require ('includes/application_top.php');

require (DIR_WS_INCLUDES.'head.php'); 
?>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_EXTERNAL; ?>econda/style.css" />
</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
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
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_modules.png'); ?></div>
        <div class="pageHeading pdg2">CleverReach</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              E-Mail Marketing
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=cleverreach'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr>
            <td colspan="2" style="border: 0px solid; border-color: #ffffff;">
              <table width="100%"  border="0" cellspacing="0" cellpadding="15">
                <tr>
                  <td>
                    <a href="http://www.cleverreach.de/" target="_blank">
                      <img src="images/bg_head.png" alt="Professionelles E-Mail Marketing f&uuml;r Ihren Online-Shop - Jetzt kostenlos &amp; unverbindlich testen!" border="0" style="margin-bottom:10px;" />
                    </a>
                    <table width="688" border="0" cellspacing="0" cellpadding="0" style="background:#fff;">
                      <tr>
                        <td colspan="2">
                          <h2 style="margin:10px 0 0 0;padding:3px 0 0 10px;height:37px;background:url(images/bg_hl.png) 0 5px no-repeat;font:bold 12px Arial, Verdana, Helvetica, sans-serif;color:#666;">CleverReach ist eine E-Mail Marketing Software f&uuml;r Ihre modified eCommerce Shopsoftware</h2>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <img src="images/screenshot_01.png" alt="CleverReach Screenshot 01" hspace="5" /><br />
                          <img src="images/screenshot_02.png" alt="CleverReach Screenshot 02" hspace="5" /><br />
                          <img src="images/screenshot_03.png" alt="CleverReach Screenshot 03" hspace="5" /><br />
                          <img src="images/screenshot_04.png" alt="CleverReach Screenshot 04" hspace="5" /><br />
                        </td>
                        <td valign="top" style="font:normal 11px Arial,Verdana, Helvetica, sans-serif;color:#666;">
                          <h3 style="margin:3px 0 0 0;font:bold 14px Arial,Verdana, Helvetica, sans-serif;color:#f60;">modified eCommerce Shopsoftware Schnittstelle</h3>
                          <p style="margin:3px 20px 15px 0;line-height:16px;text-align:justify;">Die Schnittstellen erm&ouml;glicht eine direkte Anbindung an Ihren Online-Shop. Ohne manuellen Aufwand werden Empf&auml;ngerdaten automatisiert in Ihren Account importiert.</p>
                          <h3 style="margin:3px 0 0 0;font:bold 14px Arial,Verdana, Helvetica, sans-serif;color:#f60;">Newsletter Versand</h3>
                          <p style="margin:3px 20px 15px 0;line-height:16px;text-align:justify;">Personalisierte oder unpersonalisierte Newsletter sofort oder zeitgesteuert vesenden. CleverReach bietet alle Funktionen, die man von einem anspruchsvollen Tool rund um den E-Mail Versand erwarten kann.</p>
                          <h3 style="margin:3px 0 0 0;font:bold 14px Arial,Verdana, Helvetica, sans-serif;color:#f60;">Autoresponder</h3>
                          <p style="margin:3px 20px 15px 0;line-height:16px;text-align:justify;">Automatisches Nachfassen, E-Mail-Serien und individuelle Zeitsteuerung bietet Ihnen der CleverReach Autoresponder.</p>
                          <h3 style="margin:3px 0 0 0;font:bold 14px Arial,Verdana, Helvetica, sans-serif;color:#f60;">Ausgefeilte Statistiken</h3>
                          <p style="margin:3px 20px 15px 0;line-height:16px;text-align:justify;">Die CleverReach Statistiken zeigen Ihnen &Ouml;ffnungsraten, Klicks pro Link, unzustellbare Abmeldungen und vieles mehr.</p>
                          <h3 style="margin:3px 0 0 0;font:bold 14px Arial,Verdana, Helvetica, sans-serif;color:#f60;">Sicherheit</h3>
                          <p style="margin:3px 20px 15px 0;line-height:16px;text-align:justify;">CleverReach ist Mitglied in der "Certified Senders Alliance" und dadurch bei den teilnehmenden Mail Providern auf der Positivliste.</p>  
                          <br />
                          <STRONG>Links:</STRONG><br />
                          <a href="http://www.cleverreach.de/" target="_blank">Weitere Informationen</a><br />
                          <a href="http://www.cleverreach.de/features" target="_blank">Leistungs&uuml;bersicht</a><br />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" style="font:normal 11px Arial,Verdana, Helvetica, sans-serif;color:#666;">
                          <h2 style="margin:10px 0 0 0;padding:3px 0 0 10px;height:37px;background:url(images/bg_hl.png) 0 5px no-repeat;font:bold 12px Arial, Verdana, Helvetica, sans-serif;color:#666;">Jetzt kostenlos testen</h2>
                          <p style="margin:3px 20px 5px 10px;line-height:16px;text-align:justify;">CleverReach bietet Ihnen einen unverbindlichen und kostenlosen Testmonat inklusive Kontingent f&uuml;r Ihren Newsletter Versand.</p>
                          <a href="http://www.cleverreach.de/" target="_blank"><img src="images/but_test_free.png" alt="Hier klicken &amp; kostenlosen Testzugang anfordern" hspace="5" border="0" /></a>
                          <p style="margin:5px 20px 5px 10px;line-height:16px;text-align:justify;">Nach dem kostenlosen Test ist ein Versand schon ab 15,- &euro; m&ouml;glich und der Account monatlich k&uuml;ndbar.</p>
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