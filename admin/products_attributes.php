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

$filter_params_arr = array(
    'option_filter',
    'value_order_by',
    'option_id',
    'search_optionsname'
    );
    
$filter_arr = array();
foreach($filter_params_arr as $key) {
  if (isset($_GET[$key])) {
    $filter_arr[] = $key .'='.$_GET[$key];
  }
}
$option_filter = '&'. implode('&', $filter_arr);

$page_params_arr = array(
    'option_page',
    'value_page',
    'attribute_page',
    'search_optionsname'
    );
    
$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

if ($_GET['action']) {
  if (isset($_POST['option_filter'])) $_GET['option_filter'] = $_POST['option_filter'];
 
  $page_filter_arr = array();
  $page_params_arr = array_merge($page_params_arr,$filter_params_arr);
  foreach($page_params_arr as $key) {
    if (isset($_GET[$key])) {
      $page_filter_arr[] = $key .'='.$_GET[$key];
    }
  }
  $page_info = implode('&', $page_filter_arr);
  $action = $_GET['action'];
  include(DIR_WS_MODULES.'products_attributes_action.php');
}

if ($_GET['search_optionsname']) {
  $search_optionsname = isset($_GET['search_optionsname']) && $_GET['search_optionsname'] ? '&search_optionsname=' . $_GET['search_optionsname'] : '';
}

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript">
  <!--
   function go_option() {
     if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
       location = "<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . (isset($_GET['option_page']) ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
     }
   }
   function option_filter(obj) {
     location = "<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . (isset($_GET['option_page']) ? $_GET['option_page'] : 1)); ?>&option_id="+obj.value;
   }
  //-->
  </script>
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
        <!-- BOF options and values//-->
        <?php
        include (DIR_WS_MODULES.'products_attributes_options.php');
        include (DIR_WS_MODULES.'products_attributes_values.php');
        ?>
        <!-- BOF options and values//-->
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