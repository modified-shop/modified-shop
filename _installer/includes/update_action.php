<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  include(DIR_FS_DOCUMENT_ROOT.'_installer/includes/update_data.php');
  
  
  $step = 1;
  $start = 0;
  if (isset($_POST['start']) && is_numeric($_POST['start'])) {
    $start = (int)$_POST['start'];
  }
    
  
  // update table
  for ($j=$start; $j<($start + $step); $j++) {
  
    $sql_array = array_slice($modified_sql_array, $j, $step);

    foreach ($sql_array as $table => $data) {
  
      $data['create'] = get_sql_create_data($table);

      if ($data['create'] != '') {
        if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE '".$table."'")) != '1') { 
          xtc_db_query(str_replace('_mod_', '', $data['create']));
        } else {
  
          // drop column
          $table_exists = array();
          $describe_query = xtc_db_query("DESCRIBE `".$table."`");
          while ($describe = xtc_db_fetch_array($describe_query)) {
            $table_exists[$describe['Field']] = $describe;      
          }

          if (isset($data['drop'])) {
            if (isset($data['drop']['col']) && is_array($data['drop']['col'])) {
              foreach ($data['drop']['col'] as $column) {
                if (isset($table_exists[$column])) {
                  xtc_db_query("ALTER TABLE ".$table." DROP ".$column);
                  $success .= 'Table '.$table.' Column '.$column.' DELETED<br/>';
                }
              }
            }
          }
    
          // update/add column
          $table_exists = array();
          $describe_query = xtc_db_query("DESCRIBE `".$table."`");
          while ($describe = xtc_db_fetch_array($describe_query)) {
            $table_exists[$describe['Field']] = $describe;      
          }

          xtc_db_query("DROP TABLE IF EXISTS `_mod_".$table."`");
          xtc_db_query($data['create']);
    
          $count = 0;
          $table_check = array();
          $describe_query = xtc_db_query("DESCRIBE `_mod_".$table."`");
          while ($describe = xtc_db_fetch_array($describe_query)) {
            $table_check[$describe['Field']] = $describe;
      
            $table_check_pos[$describe['Field']] = $count;
            $table_check_pos[$count] = $describe['Field'];     
            $count ++;
          }
    
          $table_idx_check = array();
          $index_query = xtc_db_query("SHOW INDEX FROM `_mod_".$table."`");
          while ($index = xtc_db_fetch_array($index_query)) {
            unset($index['Table']);
            unset($index['Cardinality']);
            $table_idx_check[$index['Key_name']][] = $index;      
          }

          xtc_db_query("DROP TABLE IF EXISTS `_mod_".$table."`");
        
          $result_table = array_diff_assoc_recursive($table_check, $table_exists);

          if (count($result_table) < 1 /*&& xtc_not_null($result_table)*/) {
            $success .= 'Table '.$table.' OK<br/>';
          } else {
            $cnt = 0;
            foreach ($result_table as $key => $value) {
              if (isset($table_exists[$key])) {
                if (isset($result_table[$key]['Type'])) {
                  $cnt ++;
                  xtc_db_query("ALTER TABLE ".$table." MODIFY ".$key." ".$table_check[$key]['Type'].(($table_check[$key]['Default'] != '') ? " DEFAULT ".$table_check[$key]['Default'] : "").((strtolower($table_check[$key]['Null']) == 'no') ? " NOT NULL" : "").(($table_check[$key]['Extra'] != '') ? " ".$table_check[$key]['Extra'] : ""));
                }
              } else {
                if (isset($data['rename']) && isset($data['rename'][$key])) {
                  $cnt ++;
                  xtc_db_query("ALTER TABLE ".$table." CHANGE ".$data['rename'][$key]. " ".$key." ".$table_check[$key]['Type'].(($table_check[$key]['Default'] != '') ? " DEFAULT ".$table_check[$key]['Default'] : "").((strtolower($table_check[$key]['Null']) == 'no') ? " NOT NULL" : "").(($table_check[$key]['Extra'] != '') ? " ".$table_check[$key]['Extra'] : ""));
                } else {
                  $cnt ++;
                  xtc_db_query("ALTER TABLE ".$table." ADD ".$key." ".$table_check[$key]['Type'].(($table_check[$key]['Default'] != '') ? " DEFAULT ".$table_check[$key]['Default'] : "").((strtolower($table_check[$key]['Null']) == 'no') ? " NOT NULL" : "").(($table_check[$key]['Extra'] != '') ? " ".$table_check[$key]['Extra'] : "").(($table_check_pos[$key] > 0) ? " AFTER ".$table_check_pos[($table_check_pos[$key] - 1)] : " FIRST"));
                }
              }
            }
            if ($cnt > 0) {
              $success .= 'Table '.$table.' UPDATED<br/>';
            } else {
              $success .= 'Table '.$table.' OK<br/>';
            }
          }
        
          // drop index
          $table_idx_exists = array();
          $index_query = xtc_db_query("SHOW INDEX FROM `".$table."`");
          while ($index = xtc_db_fetch_array($index_query)) {
            unset($index['Table']);
            unset($index['Cardinality']);
            $table_idx_exists[$index['Key_name']][] = $index;      
          }

          if (isset($data['drop'])) {
            if (isset($data['drop']['idx']) && is_array($data['drop']['idx'])) {
              foreach ($data['drop']['idx'] as $index) {
                if (isset($table_idx_exists[$index])) {
                  xtc_db_query("ALTER TABLE ".$table.((strtoupper($index) == 'PRIMARY') ? " DROP PRIMARY KEY" : " DROP INDEX ".$index));
                  $success .= 'Table '.$table.' Index '.$index.' DELETED<br/>';
                }
              }
            }
          }

          // add index
          $table_idx_exists = array();
          $index_query = xtc_db_query("SHOW INDEX FROM `".$table."`");
          while ($index = xtc_db_fetch_array($index_query)) {
            unset($index['Table']);
            unset($index['Cardinality']);
            $table_idx_exists[$index['Key_name']][] = $index;      
          }

          $result_index = array_diff_assoc_recursive($table_idx_check, $table_idx_exists);

          if (count($result_index) < 1) {
            $success .= 'Table '.$table.' Index OK<br/>';
          } else {
            foreach ($result_index as $key => $value) {
              if (!isset($table_idx_exists[$value[0]['Key_name']])) {
                $index_array = array();
                for ($i=0, $n=count($value); $i<$n; $i++) {
                  $index_array[] = $value[$i]['Column_name'];
                }
                xtc_db_query("ALTER TABLE ".$table." ADD ".((strtoupper($value[0]['Key_name']) == 'PRIMARY') ? 'PRIMARY KEY' : (($value[0]['Non_unique'] == '0') ? "UNIQUE" : "KEY")." ".$value[0]['Key_name'])." (".implode(", ", $index_array).")");
                $success .= 'Table '.$table.' Index '.$value[0]['Key_name'].' UPDATED<br/>';
              }
            }
          }        
        }
      } else {
        $error .= 'Table '.$table.' structure NOT FOUND<br/>';
      }
    }
  }
  
  // reset output
  $success = $error = '';
  
  $success .= '<form action="'.basename($_SERVER['PHP_SELF']).'?action=db_update&lg='.$lang.'" method="post" name="update">';
  $success .= '<input type="hidden" name="start" value="'.($start + $step).'">';
  $success .= '<input type="hidden" name="update" value="true" />';
  $success .= '</form>';
  
  if (count($modified_sql_array) > $start) {
    $success .= TEXT_TABLE.($start + $step).TEXT_OF.count($modified_sql_array);
    $success .= '<script language="javascript" type="text/javascript">setTimeout("document.update.submit()", 500);</script>';
  } else {
    // drop table
    foreach ($modified_drop_table_array as $table) {
      xtc_db_query("DROP TABLE IF EXISTS ".$table);
    }
    $success = '';
    clear_dir(DIR_FS_DOCUMENT_ROOT.'cache/');
    clear_dir(DIR_FS_DOCUMENT_ROOT.'templates_c/');
  }
?>