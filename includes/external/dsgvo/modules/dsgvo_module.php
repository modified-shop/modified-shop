<?php
  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      )
  {
    $content_group_array = explode(',', MODULE_SYSTEM_DSGVO_CONTENT);
  
    $check_dsgvo_query = xtc_db_query("SELECT cod.*,
                                              cud.date_confirmed
                                         FROM `content_dsgvo` cod
                                         JOIN ".TABLE_CUSTOMERS."  c
                                              ON c.customers_id = '".(int)$_SESSION['customer_id']."'
                                    LEFT JOIN `customers_dsgvo` cud
                                              ON cud.content_group = cod.content_group
                                                 AND cud.customers_id = '".(int)$_SESSION['customer_id']."'
                                        WHERE cod.content_group IN ('".implode("','", $content_group_array)."')
                                          AND cod.customers_status = c.customers_status
                                          AND (cod.date_added > cud.date_confirmed
                                               OR cud.date_confirmed IS NULL)
                                     GROUP BY cod.content_group
                                     ORDER BY cod.date_added DESC");
    if (xtc_db_num_rows($check_dsgvo_query) > 0) {
      require_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/system/system_dsgvo.php');
      
      $_SESSION['dsgvo'] = array();
      
      $module_content = array();
      while ($check_dsgvo = xtc_db_fetch_array($check_dsgvo_query)) {
        $content = $main->getContentData($check_dsgvo['content_group'], (int)$_SESSION['languages_id'], $check_dsgvo['customers_status']);
        
        if (count($content) > 0) {
          $module_content[] = array(
            'TITLE' => $content['content_title'],
            'HEADING' => $content['content_heading'],
            'TEXT' => $content['content_text'],
            'LINK' => $main->getContentLink($check_dsgvo['content_group'], MORE_INFO, 'SSL'),
            'ID' => $check_dsgvo['content_group'],
            'CONDITION' => sprintf(TEXT_DSGVO_ACCEPT_CONDITIONS, (($content['content_title'] != '') ? $content['content_title'] : $content['content_heading'])),
            'CHECKBOX' => '<input type="checkbox" value="'.$check_dsgvo['content_group'].'" name="dsgvo['.$check_dsgvo['content_group'].']" id="dsgvo['.$check_dsgvo['content_group'].']" />',
          );
  
          $_SESSION['dsgvo'][$check_dsgvo['content_group']] = true;
        }
      }
      
      if (count($module_content) > 0) {
        // create smarty elements
        $smarty = new Smarty();
  
        // build breadcrumb
        $breadcrumb->add(NAVBAR_TITLE_DSGVO, xtc_href_link(basename($PHP_SELF), '', 'SSL'));
  
        // include header
        require (DIR_WS_INCLUDES.'header.php');
  
        // include boxes
        $display_mode = 'dsgvo';
        require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
  
        if ($messageStack->size('dsgvo') > 0) {
          $smarty->assign('error_message', $messageStack->output('dsgvo'));
        }
  
        $smarty->assign('FORM_ACTION', xtc_draw_form('dsgvo', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=dsgvo', 'SSL')));
        $smarty->assign('FORM_END', '</form>');
        $smarty->assign('BUTTON_CONFIRM', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_LOGOFF, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
  
        $smarty->assign('module_content', $module_content);
  
        $smarty->assign('language', $_SESSION['language']);
        $main_content = $smarty->fetch(DIR_FS_EXTERNAL . '/dsgvo/templates/dsgvo.html');
        $smarty->assign('main_content', $main_content);
        $smarty->caching = 0;
        if (!defined('RM')) $smarty->load_filter('output', 'note');
        $smarty->display(CURRENT_TEMPLATE . '/index.html');
  
        include ('includes/application_bottom.php');
        exit();
      }
    }
  }
