<?php
  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      )
  {
    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language(DEFAULT_LANGUAGE);
    
    $customers_status_query = xtc_db_query("SELECT customers_status_id
                                              FROM ".TABLE_CUSTOMERS_STATUS."
                                          GROUP BY customers_status_id");
    while ($customers_status = xtc_db_fetch_array($customers_status_query)) {
      $content_array = array();      
      $content_group_array = explode(',', MODULE_SYSTEM_DSGVO_CONTENT);
      foreach ($content_group_array as $content_group) {
        foreach ($lng->catalog_languages as $language) {
          $content = $main->getContentData($content_group, $language['id'], $customers_status['customers_status_id']);
          $content_array[$language['code']] = ((isset($content['content_text'])) ? $content['content_text'] : '');
        }
        ksort($content_array);
        
        $hash = md5(serialize($content_array));
        $filename = $content_group.'_'.$customers_status['customers_status_id'].'_'.$hash.'.txt';
      
        if (!is_file(DIR_FS_CATALOG.DIR_ADMIN.'archives/content/'.$filename)) {
          file_put_contents(DIR_FS_CATALOG.DIR_ADMIN.'archives/content/'.$filename, serialize($content_array));

          $sql_data_array = array(
            'customers_status' => $customers_status['customers_status_id'],
            'content_group' => $content_group,
            'hash' => $hash,
            'date_added' => 'now()',
          );
          xtc_db_perform('content_dsgvo', $sql_data_array);
        }

        $check_dsgvo_query = xtc_db_query("SELECT cod.date_added
                                             FROM `content_dsgvo` cod
                                            WHERE cod.content_group = '".(int)$content_group."'
                                              AND cod.customers_status = '".(int)$customers_status['customers_status_id']."'
                                         ORDER BY cod.date_added DESC
                                            LIMIT 1");
        if (xtc_db_num_rows($check_dsgvo_query) > 0) {
          $check_dsgvo = xtc_db_fetch_array($check_dsgvo_query);  
          $check_customers_query = xtc_db_query("SELECT c.customers_id,
                                                        c.customers_date_added
                                                   FROM ".TABLE_CUSTOMERS." c
                                              LEFT JOIN `customers_dsgvo` cd
                                                        ON c.customers_id = cd.customers_id
                                                           AND cd.content_group = '".(int)$content_group."'
                                                  WHERE cd.customers_id IS NULL
                                                    AND c.customers_date_added >= '".xtc_db_input($check_dsgvo['date_added'])."'");
          if (xtc_db_num_rows($check_customers_query) > 0) {
            while ($check_customers = xtc_db_fetch_array($check_customers_query)) {
              $sql_data_array = array(
                'customers_id' => (int)$check_customers['customers_id'],
                'content_group' => (int)$content_group,
                'date_confirmed' => $check_customers['customers_date_added'],
              );
              xtc_db_perform('customers_dsgvo', $sql_data_array);
            }
          }
        }
      }
    }
  }
