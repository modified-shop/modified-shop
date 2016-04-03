<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Based on:
   (c) 2001 by the Post-Nuke Development Team - http://www.postnuke.com/
   (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Original Author of file: Jim McDonald
   Purpose of file: The PostNuke API

   Protects better diverse attempts of Cross-Site Scripting attacks
   thanks to webmedic, Timax, larsneo.
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------*/

die('Deprecated Function xtc_Security. Use xss_secure instead.');

function xtc_Security()
{
    // Cross-Site Scripting attack defense - Sent by larsneo
    // some syntax checking against injected javascript
    // extended by Neo
    if (count($_GET) > 0) {
        // Lets now sanitize the GET vars
        foreach ($_GET as $secvalue) {
            if (!is_array($secvalue)) {
                if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/.*[[:space:]](or|and)[[:space:]].*(=|like).*/i", $secvalue)) ||
                    (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*style.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*form.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*img.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue))) {
                    
                    //xtc_mail_hack_attempt(__FILE__, __LINE__, 'modified eCommerce Shopsoftware - Security Alert', 'Intrusion detection.');
                    xtc_redirect(FILENAME_DEFAULT);
                }
            }
        }
    }

    // Lets now sanitize the POST vars
    if (count($_POST) > 0) {
        foreach ($_POST as $secvalue) {
            if (!is_array($secvalue)) {
                if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue))) {

                    //xtc_mail_hack_attempt(__FILE__, __LINE__, 'modified eCommerce Shopsoftware - Security Alert', 'Intrusion detection.');
                    xtc_redirect(FILENAME_DEFAULT);
                }
             }
        }
    }

    // Lets now sanitize the COOKIE vars
    if ( count($_COOKIE) > 0) {
        foreach ($_COOKIE as $secvalue) {
            if (!is_array($secvalue)) {
                if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/.*[[:space:]](or|and)[[:space:]].*(=|like).*/i", $secvalue)) ||
                    (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*style.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*form.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue)) ||
                    (preg_match("/<[^>]*img.*\"?[^>]*>/i", $secvalue))) {

                    //xtc_mail_hack_attempt(__FILE__, __LINE__, 'modified eCommerce Shopsoftware - Security Alert', 'Intrusion detection.');
                    xtc_redirect(FILENAME_DEFAULT);
                }
            }
        }
    }
}

function xtc_mail_hack_attempt($detecting_file = "(no filename available)",
                               $detecting_line = "(no line number available)",
                               $hack_type = "(no type given)",
                               $message = "(no message given)" ) {

    $output         =        "Attention site admin of ".STORE_NAME.",\n";
    $output        .=        "On ".@strftime(DATE_FORMAT_LONG);
    $output        .=        " at ". @strftime(DATE_TIME_FORMAT_SHORT);
    $output        .=        " the modified Shopsoftware has detected that somebody tried to"
                            ." send information to your site that may have been intended"
                            ." as a hack. Do not panic, it may be harmless: maybe this"
                            ." detection was triggered by something you did! Anyway, it"
                            ." was detected and blocked. \n";
    $output        .=        "The suspicious activity was recognized in $detecting_file "
                            ."on line $detecting_line, and is of the type $hack_type. \n";
    $output        .=        "Additional information given by the code which detected this: ".$message;
    $output        .=        "\n\nBelow you will find a lot of information obtained about "
                            ."this attempt, that may help you to find  what happened and "
                            ."maybe who did it.\n\n";

    $output        .=        "\n=====================================\n";
    $output        .=        "Information about this user:\n";
    $output        .=        "=====================================\n";

    if (!isset($_SESSION['customer_id'])) {
        $output    .=        "This person is not logged in.\n";
    }  else {
        $output    .=        "This person is logged in!!\n Customers ID =".$_SESSION['customer_id'];

    }

    $output        .=        "IP numbers: [note: when you are dealing with a real cracker "
                            ."these IP numbers might not be from the actual computer he is "
                            ."working on]"
                            ."\n\t IP according to HTTP_CLIENT_IP: ".$_SERVER['HTTP_CLIENT_IP']
                            ."\n\t IP according to REMOTE_ADDR: ".$_SERVER['REMOTE_ADDR']
                            ."\n\t IP according to GetHostByName(".$_SERVER['REMOTE_ADDR']."): ".@GetHostByName($_SERVER['REMOTE_ADDR'])
                            ."\n\n";

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_REQUEST array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_REQUEST ) ) {
        $output    .=        "REQUEST * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_GET array\n";
    $output        .=        "This is about variables that may have been ";
    $output        .=        "in the URL string or in a 'GET' type form.\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_GET ) ) {
       $output     .=        "GET * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_POST array\n";
    $output        .=        "This is about visible and invisible form elements.\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_POST ) ) {
        $output    .=        "POST * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Browser information\n";
    $output        .=        "=====================================\n";

    $output        .=        "HTTP_USER_AGENT: ".$_SERVER['HTTP_USER_AGENT'] ."\n";

    $browser = (array) @get_browser();
    while ( list ( $key, $value ) = @each ( $browser ) ) {
        $output    .=        "BROWSER * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_SERVER array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_SERVER ) ) {
        $output    .=        "SERVER * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_ENV array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_ENV ) ) {
        $output    .=        "ENV * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_COOKIE array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_COOKIE ) )  {
        $output    .=        "COOKIE * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_FILES array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_FILES ) ) {
        $output    .=        "FILES * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_SESSION array\n";
    $output        .=        "This is session info.";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_SESSION ) ) {
        $output    .=        "SESSION * $key : $value\n";
    }

    // Smarty Template Engine 
    if (!defined('TEMPLATE_ENGINE')) {
      define('TEMPLATE_ENGINE','smarty_2');
    }
    require (DIR_FS_EXTERNAL.'smarty/'.TEMPLATE_ENGINE.'/Smarty.class.php');

    xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                EMAIL_SUPPORT_NAME,
                EMAIL_SUPPORT_ADDRESS,
                EMAIL_SUPPORT_NAME,
                EMAIL_SUPPORT_FORWARDING_STRING,
                EMAIL_SUPPORT_REPLY_ADDRESS,
                EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                '',
                '',
                'Attempted hack on your site? (type: '.$message.')',
                nl2br($output),
                $output);

}
?>