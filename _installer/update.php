<?php
/* -----------------------------------------------------------------------------------------
   $Id: update.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   Stand 04.03.2012
   ---------------------------------------------------------------------------------------*/
error_reporting(0);
chdir('../');
include ('includes/application_top.php');

// set all files to be deleted
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/delete_files.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/delete_files.php');
}

// set all directories to be deleted                     
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/delete_dir.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/delete_dir.php');
}

// set all update sql files to be executed and deleted afterwards
//$sql_update = array('update_1.0.6.0_to_1.0.7.0.sql');

$error='';
$success='';
$clean = false;
if (isset($_POST['update']) && $_POST['update']=='true') {

  switch ($_GET['action']) {
  
    case 'sql_update':
      foreach ($_POST['sql'] as $sql_update) {
        sql_update($sql_update);
      }    
      break;
    
    case 'sql_manual':
      sql_update($_POST['sql_manual'], true);
      break;
      
    case 'unlink':
      foreach ($unlink_file as $unlink) {
        if (is_file(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
          @unlink(DIR_FS_DOCUMENT_ROOT.$unlink) ? $success.=$unlink.'<br/>' : $error.=$unlink.'<br/>';
        }
      }
      foreach ($unlink_dir as $unlink) {
        if (is_dir(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
          rrmdir(DIR_FS_DOCUMENT_ROOT.$unlink);
        }
      }
      break;  
  
  }

  if (empty($error)) {
    $clean = true;
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>modified eCommerce Shopsoftware Updater</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<style type="text/css">
body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
a {text-decoration: none;}
</style>
</head>

<body>
<table width="800" style="border:30px solid #fff;" border="0" align="center" cellpadding="20" cellspacing="0">
  <tr>
    <td height="95" colspan="2">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td><img src="http://www.modified-shop.org/forum/Themes/modified/images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td colspan="2" height="20px" style="border-top:1px solid #ccc; width:100%;"></td></tr>
  <tr>
    <td colspan="2">
      <table width="100%" border="0" cellpadding="10" cellspacing="0">
        <?php
        switch ($_GET['action']) {
          case 'unlink':
            if (!empty($success)) {
            ?>
            <tr>
              <td valign="top">Erfolgreich gel&ouml;scht:</td>
              <td><?php echo $success; ?></td>
            </tr>
            <?php } elseif ($clean === false && !$_POST) { ?>
            <form name="update" method="post">
            <tr>
              <td valign="top">Diese Dateien m&uuml;ssen gel&ouml;scht werden:</td>
              <td><?php echo implode('<br/>', $unlink_file); ?></td>
            </tr>
            <?php }
            if (!empty($error)) {
            ?>
            <tr>
              <td valign="top">Bitte diese Dateien und Verzeichnisse manuell l&ouml;schen:</td>
              <td><?php echo $error; ?></td>
            </tr>
            <?php } elseif ($clean === false && !$_POST) { ?>
            <tr>
              <td valign="top">Diese Verzeichnisse m&uuml;ssen gel&ouml;scht werden:</td>
              <td><?php echo implode('<br/>', $unlink_dir); ?></td>
            </tr>
            <?php } elseif ($clean === true) { ?>
            <tr>
              <td valign="top" colspan="2" align="center" style="border:1px solid green; width:100%;">Es wurden die Dateien und Verzeichnisse erfolgreich gel&ouml;scht.<br/>Bitte stellen Sie sicher, dass auch die Datei &quot;update.php&quot; vom Server entfernt wurde.</td>
            </tr>
            <?php } 
            break;

          case 'sql_update':
            if (!empty($success)) {
              ?>
              <tr>
                <td valign="top">Erfolgreich ausgef&uuml;hrt:</td>
                <td><?php echo $success; ?></td>
              </tr>
              <?php 
            } else {
             
              echo '<form name="update" method="post">';
              $d = opendir(DIR_FS_DOCUMENT_ROOT.'_installer/');
              while($f = readdir($d)) {
                if (strpos($f, '.sql') !== false && $f != 'modified.sql') {
                  echo '<input type="checkbox" name="sql[]" value="'.DIR_FS_DOCUMENT_ROOT.'_installer/'.$f.'"> '.$f.'<br>';
                }
              }
            }
            break;
          
          case 'sql_manual':
            if (!empty($success)) {
              unset($_POST['sql_manual']);
              ?>
              <tr>
                <td valign="top">Erfolgreich ausgef&uuml;hrt:</td>
                <td><?php echo $success; ?></td>
              </tr>
              <?php 
            }
            echo '<form name="update" method="post">';
            echo '<tr><td colspan="2"><div style="width:100%; color:red; text-align:center">SQL Befehle m&uuml;ssen mit einem  ;  abgeschlossen werden !</div><br/><textarea name="sql_manual" style="width:100%; height:300px;">'.(isset($_POST['sql_manual']) ? $_POST['sql_manual'] : '').'</textarea></td></tr>';
            break;
              
          default:
            echo '<form name="update" method="get">' .
                 '<input type="radio" name="action" value="unlink"> Dateien und Verzeichnise l&ouml;schen<br>' .
                 '<input type="radio" name="action" value="sql_update"> Datenbank Update<br>' .
                 '<input type="radio" name="action" value="sql_manual"> Manuelle SQL';
          break;
        }
        ?>     
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php
      switch ($_GET['action']) {
        case 'unlink':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          if ($clean === false && !$_POST) {
            echo '<input type="hidden" name="update" value="true" />' .
                 '<input type="submit" value="Ausf&uuml;hren" />' .
                 '</form>';
          }
          break;
          
        case 'sql_update':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          if (!$clean) {
            echo '<input type="hidden" name="update" value="true" />' .
                 '<input type="submit" value="Ausf&uuml;hren" />' .
                 '</form>';
          }
          break;

        case 'sql_manual':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          echo '<input type="hidden" name="update" value="true" />' .
               '<input type="submit" value="Ausf&uuml;hren" />' .
               '</form>';
          break;
            
        default:
          echo '<input type="submit" value="Ausf&uuml;hren" />' .
               '</form>';
        
          break;
      }
      ?>   
    </td>
  </tr>
</table>
<br />
<div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo '<a href="http://www.modified-shop.org" target="_blank"><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">' . '&nbsp;' . '&copy;2009-' . date('Y'); ?></div>
</body>
</html>

<?php
function rrmdir($dir) {
  global $error, $success;
  
  print_r($dir);
  
  foreach(glob($dir . '/*') as $file) {
    if(is_dir($file)) {
      rrmdir($file);
    } else {
      @unlink($file) ? $success.=$file.'<br/>' : $error.=$file.'<br/>';
    }
  }
  @rmdir($dir) ? $success.=$dir.'<br/>' : $error.=$dir.'<br/>';
}

function remove_comments($sql, $remark) {
  $lines = explode("\n", $sql);
  $sql = '';
        
  $linecount = count($lines);
  $output = '';

  for ($i = 0; $i < $linecount; $i++)  {
    if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
      if ($lines[$i][0] != $remark) {
        $output .= $lines[$i] . "\n";
      } else {
        $output .= "\n";
      }
      $lines[$i] = '';
    }
  }      
  return $output;
}

function split_sql_file($sql, $delimiter) {

  //first remove comments
  $sql = remove_comments($sql, '#');
  
  // Split up our string into "possible" SQL statements.
  $tokens = explode($delimiter, $sql);

  $sql = '';
  $output = array();
  $matches = array();
  
  $token_count = count($tokens);
  for ($i = 0; $i < $token_count; $i++) {
  
    // Don't wanna add an empty string as the last thing in the array.
    if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
          
      // This is the total number of single quotes in the token.
      $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
      // Counts single quotes that are preceded by an odd number of backslashes, 
      // which means they're escaped quotes.
      $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
       
      $unescaped_quotes = $total_quotes - $escaped_quotes;
      
      // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
      if (($unescaped_quotes % 2) == 0) {
        // It's a complete sql statement.
        $output[] = $tokens[$i];
        $tokens[$i] = '';
      } else {
        // incomplete sql statement. keep adding tokens until we have a complete one.
        // $temp will hold what we have so far.
        $temp = $tokens[$i] . $delimiter;
        $tokens[$i] = '';
        
        $complete_stmt = false;
        
        for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
          // This is the total number of single quotes in the token.
          $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
          // Counts single quotes that are preceded by an odd number of backslashes, 
          // which means they're escaped quotes.
          $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
         
          $unescaped_quotes = $total_quotes - $escaped_quotes;
         
          if (($unescaped_quotes % 2) == 1) {
            // odd number of unescaped quotes. In combination with the previous incomplete
            // statement(s), we now have a complete statement. (2 odds always make an even)
            $output[] = $temp . $tokens[$j];
      
            $tokens[$j] = '';
            $temp = '';
            
            $complete_stmt = true;
            $i = $j;
          } else {
            // even number of unescaped quotes. We still don't have a complete statement. 
            // (1 odd and 1 even always make an odd)
            $temp .= $tokens[$j] . $delimiter;
            $tokens[$j] = '';
          }
        }
      }
    }
  }
  return $output;
}

function sql_update($file, $plain=false) {
  global $success;
  
  if ($plain === false) {
    $sql_file = file_get_contents($file);
  } else {
    $sql_file = $file;
  }
  $sql_array = (split_sql_file($sql_file, ';'));
    
  foreach ($sql_array as $sql) {
    $success .= $sql;
    $exists = false;
    if (preg_match("|[\z\s]?(?:ALTER TABLE?){1}[\z\s]+([^ ]*)[\z\s]+(?:ADD?){1}[\z\s]+([^ ]*)[\z\s]+([^ ]*)|", $sql, $matches)) {
      if ($matches[2] == strtoupper('INDEX')) {
        $check_query = xtc_db_query("SHOW KEYS FROM ".$matches[1]." WHERE Key_name='".$matches[3]."'");
        if (xtc_db_num_rows($check_query)>0) {
          xtc_db_query("ALTER TABLE ".$matches[1]." DROP INDEX ".$matches[3]);
        }
      } else {
        $check_query = xtc_db_query("SHOW COLUMNS FROM " . $matches[1]);
        while ($check = xtc_db_fetch_array($check_query)) {
          if ($check['Field']==$matches[2]) { 
            $exists = true;
          }
        }
      }
    }
    if (!$exists) {
      xtc_db_query($sql);
    }
    $success .= ' - <span style="color:red;">Success!</span><br/>';
  }
}
?>