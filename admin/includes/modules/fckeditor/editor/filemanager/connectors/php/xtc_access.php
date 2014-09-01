<?php
/*-----------------------------------------------/
$Id: xtc_access.php 3336 2012-07-27 11:38:05Z web28 $

FCKEditor Filemanger xtc_access v.0.95 (c)2012 by web28 - www.rpa-com.de
/-----------------------------------------------*/

require_once(DIR_FS_INC . 'xtc_db_connect.inc.php');

xtc_db_connect();// or die('Unable to connect to database server!');

$Config['Enabled'] = false;

$secure_id = preg_replace('/[^0-9a-zA-Z]/','',$_GET['sid']);

if (!empty($secure_id)) {
  $secure_id = mysql_real_escape_string($secure_id);
  $secure_id = strip_tags($secure_id);
  $result = mysql_query('SELECT flag
                           FROM sessions s
                          WHERE s.sesskey = "'. $secure_id .'"
                          LIMIT 1
                        ');
  if(mysql_num_rows($result) > 0) {
    $result_array = mysql_fetch_array($result);
    if (isset($result_array['flag']) && $result_array['flag'] == 'admin') {
      $Config['Enabled'] = true;
    }
  }
}

?>