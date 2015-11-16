<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  $iframe = (isset($_GET['iframe']) ? '&iframe=1' : '');
  $_GET['current_product_id'] = (isset($_GET['pID']) ? (int)$_GET['pID'] : (int)$_GET['current_product_id']); //new_product or iframe
  $current_product_id = (isset($_GET['current_product_id']) ? '&current_product_id='.(int)$_GET['current_product_id'] : '');
  
  if (isset($_GET['iframe']) && !isset($_POST['action'])) {
    $_POST = $_GET;
  }
  
  if (isset($_POST['current_product_id']) && $_POST['current_product_id'] > 0 && isset($_POST['action']) && $_POST['action'] == 'change') {
    require_once (DIR_WS_CLASSES.'categories.php');
    $catfunc = new categories();
    $catfunc->save_products_tags($_POST,$_POST['current_product_id']);
  }
 
  $options_query = xtc_db_query("SELECT *
                                   FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                  WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                    AND (filter = '1' OR status = '1')
                               ORDER BY sort_order, options_name, options_description");
  
  $optFlag = false;
  if (xtc_db_num_rows($options_query) > 0) {
    $module_content = array();
    $module_content[] = array('id' => '',
                                  'text' => TEXT_NONE,
                                  'content' => ''
                                  );
    $count_content = xtc_db_num_rows($options_query);
    while ($options = xtc_db_fetch_array($options_query)) {
      $values_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                     WHERE options_id = '".$options['options_id']."'
                                       AND languages_id = '".(int)$_SESSION['languages_id']."'
                                  ORDER BY sort_order, values_name, values_description");

      if (xtc_db_num_rows($values_query) > 0) {        
        $module_values_content = array();
        $flag = false;
        while ($values = xtc_db_fetch_array($values_query)) {
          $is_checked = ((xtc_get_tags_status((int)$_GET['current_product_id'], $options['options_id'], $values['values_id']) == 1) ? true : false);
          $flag = ($is_checked ? true : $flag);
          $optFlag = ($is_checked ? true : $optFlag);
          $module_values_content[] = array('checkbox' => xtc_draw_checkbox_field('product_tags['.$options['options_id'].']['.$values['values_id'].']', 'on', $is_checked),
                                           'title' => (($values['values_name'] != '') ? $values['values_name'] : $values['values_description'])
                                           );
        }                        
        $module_content[] = array('id' => 'tab_tag_'.$options['options_id'],
                                  'text' => (($options['options_name'] != '') ? $options['options_name'] : $options['options_description']),
                                  'content' => $module_values_content,
                                  'flag' => ($flag ? ' flag' : '')
                                  );
      }
    }
  }
  $optFlag = $optFlag ? 'optFlag' : '';
  
  if (count($module_content) > 0) {
    if (isset($_GET['iframe'])) {
      require (DIR_WS_INCLUDES.'head.php');

      ?>
      </head>
      <br/>
      <div style="padding:5px;clear:both;">
        <?php 
        echo xtc_draw_form('submit_products_tags', 'products_tags.php' . str_replace('&','?',$iframe).$current_product_id, '', 'post', 'id="submit_products_tags"') .PHP_EOL; ?>
        <input type="hidden" name="action" value="change">
        <input type="hidden" name="current_product_id" value="<?php echo $_POST['current_product_id']; ?>">

      <?php
    } else {
    ?>
      <div style="padding:5px;margin-top:10px;clear:both;">
      <div class="main div_header>"><b class="<?php echo $optFlag;?>"><?php echo TEXT_PRODUCTS_TAGS; ?></b></div>
      <input type="hidden" name="products_tags_save" value="1">
    <?php
    }
    ?>
      <script type="text/javascript" src="includes/javascript/jquery.products_tags.js"></script>
    <?php
      echo '<div class="main">'. xtc_draw_pull_down_menu('ptags', $module_content,'', 'id="ptags"') .'</div>' . PHP_EOL;
      for ($i = 0, $n = sizeof($module_content); $i < $n; $i++) {
        echo '<div id="'. $module_content[$i]['id'] . '"class="'.$module_content[$i]['flag'].'" style="border:1px solid #a3a3a3;display:none">' . PHP_EOL;
        ?>
        <div class="main" style="padding: 3px; line-height:20px;">
          <?php
            foreach ($module_content[$i]['content'] as $content) {
              echo '<div style="float:left;margin-right:20px;" class="tag nobr">'.$content['checkbox'] . ' ' . $content['title'].'</div>' . PHP_EOL;
            }
          ?>
          <div style="clear:both;"></div>
        </div>
        <?php
        echo ('</div>');
      }
      ?>
      <div style="clear:both;"></div>
    <?php  
    if (isset($_GET['iframe'])) {
    ?>
      <div class="main" style="margin:10px 0;">
          <?php
          echo xtc_button(BUTTON_SAVE,'submit','name="button_submit"');
          ?>
      </div>
      </form>
    </div>
    <div style="clear:both;"></div>
    <!-- footer_eof //-->
    </body>
    </html>
    <?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
  <?php
    } //iframe
  } //module_content
  
  function xtc_get_tags_status($products_id, $options_id, $values_id) 
  {
      $tags_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PRODUCTS_TAGS."
                                   WHERE products_id = '".$products_id."'
                                     AND options_id = '".$options_id."'
                                     AND values_id = '".$values_id."'");
      return xtc_db_num_rows($tags_query);
  }
?>