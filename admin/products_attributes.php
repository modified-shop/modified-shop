<?php
/* --------------------------------------------------------------
   $Id: products_attributes.php 3220 2012-07-15 15:40:20Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_attributes.php,v 1.48 2002/11/22); www.oscommerce.com
   (c) 2003 nextcommerce (products_attributes.php,v 1.10 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (products_attributes.php 1155 2005-08-13)

   Released under the GNU General Public License
--------------------------------------------------------------*/
require ('includes/application_top.php');
$languages = xtc_get_languages();

//Parameterübergabe
if (isset($_POST['option_id'])) $_GET['option_id'] = $_POST['option_id'];
$option_filter = '&option_filter='. $_GET['option_filter'] . '&value_order_by='. $_GET['value_order_by'] . '&option_id='. $_GET['option_id'] ;

if ($_GET['action']) {
  if (isset($_POST['option_filter'])) $_GET['option_filter'] = $_POST['option_filter'];
  $page_info = 'option_page=' . $_GET['option_page'] . '&value_page=' . $_GET['value_page'] . '&attribute_page=' . $_GET['attribute_page'];
  $page_info.= $option_filter; //'&option_filter='. $_GET['option_filter'] . '&value_order_by='. $_GET['value_order_by'];

  $action = $_GET['action'];
  include(DIR_WS_MODULES.'products_attributes_action.php');
}

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript">
  <!--
   function go_option() {
     if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
       location = "<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
     }
   }
  //-->
  </script>
</head>
<body>
<?php require (DIR_WS_INCLUDES . 'header.php'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
        <?php require (DIR_WS_INCLUDES . 'column_left.php'); ?>
      </table>
    </td>
    <td class="boxCenter" width="100%" valign="top">
      <!-- BOF options and values//-->
      <?php
      include (DIR_WS_MODULES.'products_attributes_options.php');
      include (DIR_WS_MODULES.'products_attributes_values.php');
      ?>
      <!-- BOF options and values//-->
    </td>
  </tr>
</table>
<?php require (DIR_WS_INCLUDES . 'footer.php'); ?>
</body>
</html>
<?php require (DIR_WS_INCLUDES . 'application_bottom.php'); ?>