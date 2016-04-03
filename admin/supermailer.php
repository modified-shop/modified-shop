<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com
   (c) 2003   nextcommerce (configuration.php,v 1.16 2003/08/19); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: configuration.php 1125 2005-07-28 09:59:44Z novalis $)
   (c) 2008 Gambio OHG (gm_trusted_info.php 2008-08-10 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/application_top.php');
require (DIR_WS_INCLUDES.'head.php');
?>
    <style type="text/css">
      .newsletter_vorteile a, .newsletter_software a { font-size: 12px; text-decoration: underline; color: #005b9c; }
      .newsletter_vorteile { background: url("images/supermailer/icon_confirmation.gif") no-repeat 0px -4px; list-style-type: none; margin-bottom: 15px; margin-left: 0; margin-top: 15px; padding-left: 30px; }
    </style>
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
        <div class="pageHeading pdg2">SuperMailer</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              E-Mail Marketing
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=supermailer'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <font color="#005b9c"><strong>Personalisierte Newsletter im HTML und Text-Format erstellen und versenden</strong></font><br />
              <img style="padding:10px 0px 10px 10px;" align="right" alt="HTML Newsletter Software und E-Mail Marketing Software SuperMailer" src="images/supermailer/mailtext.jpg" />
              <p>Das Erstellen und der Versand von Newslettern an eine Vielzahl von Empf&auml;ngern war und ist bisher kein Problem, jedoch besitzen diese Newsletter einen entscheidenden Nachteil: Diese sind unpers&ouml;nlich und es k&ouml;nnen keine empf&auml;ngerspezifischen Daten eingef&uuml;gt werden.&nbsp; Mit der <strong>Newsletter Software</strong> SuperMailer wird das jetzt anders!</p>
              <p>In der <strong>Newsletter Software</strong> und E-Mail Marketing Software erstellen Sie &auml;hnlich wie in einer Textverarbeitung Newsletter und Serien-E-Mails, die den Anschein einer pers&ouml;nlich geschriebenen E-Mail-Nachricht erwecken. Das Newsletter Programm erm&ouml;glicht das Erstellen von E-Mails im Text- oder HTML Format (inkl. Unterst&uuml;tzung f&uuml;r multipart-E-Mails). Den HTML Teil k&ouml;nnen Sie einfach mit dem integrierten WYSIWYG-HTML-Editor oder Assistenten erstellen und damit auch ohne HTML-Kenntnisse ansprechende Newsletter erstellen. F&uuml;r die pers&ouml;nliche Ansprache des Empf&auml;ngers f&uuml;gen Sie Platzhalter in Ihre E-Mail ein, die beim Versand der Serien-E-Mails mit den empf&auml;ngerspezifischen Angaben von der Newsletter Software automatisch ersetzt werden. Ebenfalls ist es m&ouml;glich Dateianh&auml;nge oder personalisierte Dateianh&auml;nge mit dem Newsletter zu versenden.<br /><br /></p>
              <p class="newsletter_software" style="margin-top:10px">
              <strong>Vorteile der Newsletter Software SuperMailer</strong></p>
              <ul class="newsletter_checkmarkstyle">
                <li class="newsletter_vorteile">Installation und Speicherung der Newsletter Empf&auml;nger direkt auf Ihrem eigenen Rechner, nicht auf fremden Servern in der "Cloud"</li>
                <li class="newsletter_vorteile">Erstellen und Versenden Sie HTML Newsletter mit der Newsletter Software so oft Sie wollen</li>
                <li class="newsletter_vorteile">Automatische Aktualisierung eines Newsletter-Archivs, auch als RSS-Feed, oder Bereitstellung einer Online-Version des Newsletters</li>
                <li class="newsletter_vorteile">Kostenlose <a href="http://www.supermailer.de/smscript_newsletter-software.htm" target="_blank">Newsletter Scripte</a> f&uuml;r die Anmeldung und Abmeldung vom Newsletter und Abmeldelink f&uuml;r den Newsletter, ebenfalls per Double-Opt-In-Verfahren</li>
                <li class="newsletter_vorteile">Erfolgskontrolle Ihrer E-Mail Marketing Aktion per Tracking (&Ouml;ffnungsstatistik der E-Mail, Z&auml;hlung der Klicks auf Hyperlinks in der E-Mail) oder optional unterst&uuml;tzt die Newsletter Software SuperMailer Google Analytics</li>
                <li class="newsletter_vorteile">Einmalige <a href="http://www.superscripte.de/Reseller/reseller.php?ResellerID=3178&ProgramName=SuperMailer" target="_blank">Lizenzgeb&uuml;hren</a> inkl. kostenfreier Softwareupdates, keine monatlichen Geb&uuml;hren </li>
                <li class="newsletter_vorteile">Newsletter Programm als kostenfreie Freeware-Version f&uuml;r bis zu 100 Newsletter Empf&auml;nger, <a href="http://www.supermailer.de/download_newsletter_software.htm" target="_blank">Download</a></li>
              </ul>
              <br />
              <p class="newsletter_software">Mehr Informationen zu den Funktionen der HTML <strong>Newsletter Software</strong> SuperMailer finden Sie unter <a href="http://www.supermailer.de/about-newsletter-software.htm" target="_blank">Produktinformation</a>.</p>
              <p align="left">
                <br />
                <a href="http://www.superscripte.de/Reseller/reseller.php?ResellerID=3178&ProgramName=SuperMailer" target="_blank"><font size="3" color="#893769"><u><strong>Jetzt bestellen!</strong></u></font></a> 
              </p>
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
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>