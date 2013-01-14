<?php
/* --------------------------------------------------------------
   $Id: popup_memo.php 1125 2005-07-28 09:59:44Z novalis $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

   require('includes/application_top.php');
   include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/customers.php');

if ($_GET['action']) {
switch ($_GET['action']) {

        case 'save':

        $memo_title = xtc_db_prepare_input($_POST['memo_title']);
        $memo_text = xtc_db_prepare_input($_POST['memo_text']);

        if ($memo_text != '' && $memo_title != '' ) {
          $sql_data_array = array(
            'customers_id' => $_POST['ID'],
            'memo_date' => date("Y-m-d"),
            'memo_title' =>$memo_title,
            'memo_text' => nl2br($memo_text),
            'poster_id' => $_SESSION['customer_id']);

          xtc_db_perform(TABLE_CUSTOMERS_MEMO, $sql_data_array);
          }
        break;

        case 'remove':
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_MEMO." where memo_id='".$_GET['mID']."'");
        break;

}
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">

</head>
<body>
<div class="pageHeading"><?php echo TITLE_MEMO; ?></div></p>
    <table width="100%">
      <tr>
      <form name="customers_memo" method="POST" action="popup_memo.php?action=save&ID=<?php echo (int)$_GET['ID'];?>">
        <td class="main" style="border-top: 1px solid; border-color: #cccccc;"><b><?php echo TEXT_TITLE ?></b>:<?php echo xtc_draw_input_field('memo_title').xtc_draw_hidden_field('ID',(int)$_GET['ID']); ?><br /><?php echo xtc_draw_textarea_field('memo_text', 'soft', '73', '5'); ?><br /><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>'; ?></td>
      </tr>
    </table></form>
<table width="100%"  border="0" cellpadding="0" cellspacing="0">

  <tr>
    <td>



    <td class="main"><?php
  $memo_query = xtc_db_query("SELECT
                                  *
                              FROM
                                  " . TABLE_CUSTOMERS_MEMO . "
                              WHERE
                                  customers_id = '" . (int)$_GET['ID'] . "'
                              ORDER BY
                                  memo_id DESC");
  while ($memo_values = xtc_db_fetch_array($memo_query)) {
    $poster_query = xtc_db_query("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $memo_values['poster_id'] . "'");
    $poster_values = xtc_db_fetch_array($poster_query);
?><table width="100%">
      <tr>
        <td class="main"><hr noshade><b><?php echo TEXT_DATE; ?></b>:<i><?php echo $memo_values['memo_date']; ?><br /></i><b><?php echo TEXT_TITLE; ?></b>:<?php echo $memo_values['memo_title']; ?><br /><b>  <?php echo TEXT_POSTER; ?></b>:<?php echo $poster_values['customers_lastname']; ?> <?php echo $poster_values['customers_firstname']; ?></td>
      </tr>
      <tr>
        <td width="142" class="main" style="border: 1px solid; border-color: #cccccc;"><?php echo $memo_values['memo_text']; ?></td>
      </tr>
      <tr>
        <td><a class="button" onclick="this.blur();" href="<?php echo xtc_href_link('popup_memo.php', 'ID=' . $_GET['ID'] . '&action=remove&mID=' . $memo_values['memo_id']); ?>" onclick="return confirm('<?php echo DELETE_ENTRY; ?>')"><?php echo BUTTON_DELETE; ?></a></td>
      </tr>
    </table>
<?php
  }
?>
  </td>
    </td>
  </tr>
</table>

</body>
</html>