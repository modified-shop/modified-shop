<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
     Example-Interface-Software for the transmission of legal texts
     Script version: Draft V0.2 - 26. April 2012
     Contact: Max-Lion Keller LL.M. m.keller@it-recht-kanzlei.de
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


class it_recht_kanzlei {
  
  var $modulversion = '1.4';
  var $api_action_flag, 
      $api_version_flag, 
      $api_username_flag, 
      $user_password_flag, 
      $user_auth_token_flag, 
      $action, 
      $post_xml;
  
  function __construct($post_xml) {
  
    $this->set_shopversion();
    
    // Catch errors - no data sent
    (string)$post_xml = $post_xml;
    
    // read POST-XML and remove form slashes
    if(get_magic_quotes_gpc()){
      $post_xml = stripslashes($post_xml);
    }
    // Post XML from other system
    if(trim($post_xml) == ''){
      $this->return_error('12');
    }
    // create xml object
    $xml = simplexml_load_string($post_xml, null, LIBXML_NOCDATA);
    
    // Catch errors - error creating xml object
    if(!is_object($xml)){
      $this->return_error('12');
    }
    
    $api_action_flag = $this->check_api_action($xml->action);
    $api_version_flag = $this->check_api_version($xml->api_version);
    $user_auth_token_flag = $this->check_token($xml->user_auth_token);
    $action = $this->action('push', $xml);
    
    // return general error
    $this->return_error('99');
    exit();
  }
  
  function check_api_action($api_action) {
    if ($api_action == '') {
      $this->return_error('10');
    }
    $local_supported_actions = array('push');
    // Catch errors - action not supported
    if (!in_array($api_action, $local_supported_actions)) {
      $this->return_error('10');
    }
  }
  
  function check_api_version($api_version) {
    // Check api-version
    if($api_version != MODULE_API_IT_RECHT_KANZLEI_VERSION){
      $this->return_error('1');
    }
  }
  
  function check_token($user_auth_token) {
    // Check token
    if($user_auth_token != MODULE_API_IT_RECHT_KANZLEI_TOKEN){
      $this->return_error('3');
    } 
  }
  
  function action($action, $xml) {
    // action 'push'
    if ($action == 'push') {
          
      // Catch errors - rechtstext_text
      if (strlen($xml->rechtstext_text) < 50) {
        $this->return_error('5');
      }
      // Catch errors - rechtstext_html
      if (strlen($xml->rechtstext_html) < 50) {
        $this->return_error('6');
      }
      // Catch errors - rechtstext_language
      if ($xml->rechtstext_language == '') {
        $this->return_error('9');
      } else {
        require_once(DIR_WS_CLASSES.'language.php');
        $lng = new language($xml->rechtstext_language);
        $languages_id = $lng->language['id'];
        if ($lng->language['code'] != $xml->rechtstext_language) {
          $this->return_error('9');
        }
      }
      
      $local_dir_for_pdf_storage = 'media/content/';
      if (MODULE_API_IT_RECHT_KANZLEI_PDF_FILE != '') {
        $local_dir_for_pdf_storage = trim(MODULE_API_IT_RECHT_KANZLEI_PDF_FILE, '/').'/';
      }
      
      // Check PDF files required
      $local_rechtstext_pdf_type = array();
      if (MODULE_API_IT_RECHT_KANZLEI_PDF_AGB == 'true') {
        $local_rechtstext_pdf_type[] = 'agb';
      }
      if (MODULE_API_IT_RECHT_KANZLEI_PDF_DSE == 'true') {
        $local_rechtstext_pdf_type[] = 'datenschutz';
      }
      if (MODULE_API_IT_RECHT_KANZLEI_PDF_WRB == 'true') {
        $local_rechtstext_pdf_type[] = 'widerruf';
      }

      // Catch errors - rechtstext_type
      $allowed_rechtstext_type = array('agb', 'datenschutz', 'widerruf', 'impressum');
      if (!in_array($xml->rechtstext_type, $allowed_rechtstext_type)) {
        $this->return_error('4');
      }
      
      $pdf_file_stored = false;
      if (count($local_rechtstext_pdf_type) > 0 && in_array($xml->rechtstext_type, $local_rechtstext_pdf_type)) {        
        if (in_array($xml->rechtstext_type, $local_rechtstext_pdf_type)) {
          // Catch errors - element 'rechtstext_pdf_url' empty or URL invalid
          if ($xml->rechtstext_pdf_url == '' || $this->url_valid($xml->rechtstext_pdf_url) !== true) {
            $this->return_error('7');
          }
          // Download pdf file
          $file_pdf_targetfilename = $xml->rechtstext_type .'.pdf';
          $file_pdf_target = DIR_FS_CATALOG.$local_dir_for_pdf_storage.$file_pdf_targetfilename;
          $file_pdf_target_temp = DIR_FS_CATALOG.$local_dir_for_pdf_storage.md5($xml->rechtstext_type).'.pdf';
          
          // include needed function
          require_once(DIR_FS_INC.'get_external_content.inc.php');
          
          $file_pdf = fopen($file_pdf_target_temp, 'w+');
          if ($file_pdf === false) { // catch errors
            $this->return_error('7');
          }
          $retval = fwrite($file_pdf, get_external_content($xml->rechtstext_pdf_url, 5, false)); 
          if ($retval === false) { // catch errors
            $this->return_error('7');
          }
          $retval = fclose($file_pdf);
          if ($retval === false) { // catch errors
            $this->return_error('7');
          }
          // Catch errors - downloaded file was not properly saved
          if (!is_file($file_pdf_target_temp)) {
            $this->return_error('7');
          }
          // verify that file is a pdf
          if ($this->check_if_pdf_file($file_pdf_target_temp) !== true) {
            @unlink($file_pdf_target_temp);
            $this->return_error('7');
          }
          // verify md5-hash, delete file if hash is not equal
          if (md5_file($file_pdf_target_temp) != $xml->rechtstext_pdf_md5hash) {
            @unlink($file_pdf_target_temp);
            $this->return_error('8');
          } else {
            @unlink($file_pdf_target);
            @copy($file_pdf_target_temp, $file_pdf_target);
            if (!is_file($file_pdf_target)) {
              $this->return_error('7');
            }
          }
          $pdf_file_stored = true;
        }
      } else {
        if (is_file(DIR_FS_CATALOG.$local_dir_for_pdf_storage.$xml->rechtstext_type .'.pdf')) {
          @unlink(DIR_FS_CATALOG.$local_dir_for_pdf_storage.$xml->rechtstext_type .'.pdf');
        }
      }
      
      // text type
      $pdf_file_text = '';
      $content_group = '';
      if ($xml->rechtstext_type == 'agb') {
        $content_group = MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB;
        if ($pdf_file_stored === true) {
          $pdf_file_text = '<br /><br /><a href="'.DIR_WS_CATALOG.$local_dir_for_pdf_storage.$file_pdf_targetfilename.'" target="_blank">AGB - PDF download!</a>';
        }
      } elseif ($xml->rechtstext_type == 'datenschutz') {
        $content_group = MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE;
        if ($pdf_file_stored === true) {
          $pdf_file_text = '<br /><br /><a href="'.DIR_WS_CATALOG.$local_dir_for_pdf_storage.$file_pdf_targetfilename.'" target="_blank">Datenschutz - PDF download!</a>';
        }
      } elseif ($xml->rechtstext_type == 'widerruf') {
        $content_group = MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB;
        if ($pdf_file_stored === true) {
          $pdf_file_text = '<br /><br /><a href="'.DIR_WS_CATALOG.$local_dir_for_pdf_storage.$file_pdf_targetfilename.'" target="_blank">Widerruf - PDF download!</a>';
        }
      } elseif ($xml->rechtstext_type == 'impressum') {
        $content_group = MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP;
      }
      
      if ($content_group != '') {
        $check_query = xtc_db_query("SELECT content_text 
                                       FROM ".TABLE_CONTENT_MANAGER." 
                                      WHERE content_group = '".$content_group."' 
                                        AND languages_id = '".$languages_id."' 
                                      LIMIT 1");
        $check = xtc_db_fetch_array($check_query);
        if ($check['content_text'] == $this->charset_decode_utf_8($xml->rechtstext_html.$pdf_file_text)) {
          $this->return_success();
        } else {
          $sql_data_array = array('content_text' => $this->charset_decode_utf_8($xml->rechtstext_html.$pdf_file_text));
          xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group = '".$content_group."' AND languages_id = '".$languages_id."'");
          if (xtc_db_affected_rows() < 1) {
            $check_content_query = xtc_db_query("SELECT content_text 
                                                   FROM ".TABLE_CONTENT_MANAGER." 
                                                  WHERE content_group = '".$content_group."' 
                                                    AND languages_id = '".$languages_id."'");
            $check_content = xtc_db_fetch_array($check_content_query);
            if ($check_content['content_text'] != $sql_data_array['content_text']) {
              $this->return_error('99');
            }
          }
        }
      } else {
        $this->return_error('99');
      }  
          
      $this->return_success();
    } else {
      $this->return_error('99');
    }
  }
  
  function url_valid($url) {
    $urlregex  = "((https?|ftp)\:\/\/)?"; // SCHEME
    $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
    $urlregex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
    $urlregex .= "(\:[0-9]{2,5})?"; // Port
    $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
    $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
    $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor
    
    $array_url = parse_url($url);
    $limit_to_host = array('it-recht-kanzlei.de', 
                           'www.it-recht-kanzlei.de', 
                           'itrelaunch.blickreif.com', 
                           'www.itrelaunch.blickreif.com');
    if (!in_array(strtolower($array_url['host']), $limit_to_host)) {
      return false;
    }
  
    // check
    if (preg_match("/^$urlregex$/", $url)) {
      return true;
    } else {
      return false;
    }
  }
  
  // check if a file is a pdf
  function check_if_pdf_file($filename) {
    $handle = fopen($filename, "r");
    $contents = fread($handle, 4);
    fclose($handle);
    if ($contents == '%PDF') {
      return true;
    } else {
      return false;
    }
  }
  
  function set_shopversion() {
    if (!$this->shopversion) {
      require_once(DIR_FS_INC.'get_database_version.inc.php');
      $db_version = get_database_version();
      $this->shopversion = $db_version['full'];
    }
  }

  function charset_decode_utf_8($string) {
    if (!preg_match("/[\200-\237]/", $string) && !preg_match("/[\241-\377]/", $string)) {
      return $string;
    }

    // decode three byte unicode characters
    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e", "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string);

    // decode two byte unicode characters
    $string = preg_replace("/([\300-\337])([\200-\277])/e", "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string);

    return decode_htmlentities($string);
  }
    
  // return error and end script
  function return_error($errorcode) {
    // output error
    header('Content-type: application/xml; charset=utf-8');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<response>\n";
    echo "  <status>error</status>\n";
    echo "  <error>".$errorcode."</error>\n";
    echo "  <meta_shopversion>".$this->shopversion."</meta_shopversion>\n";
    echo "  <meta_modulversion>".$this->modulversion."</meta_modulversion>\n";
    echo "</response>";
    exit();
  }
  
  // return success and end script
  function return_success() {
    // output success
    header('Content-type: application/xml; charset=utf-8');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<response>\n";
    echo "  <status>success</status>\n";
    echo "  <meta_shopversion>".$this->shopversion."</meta_shopversion>\n";
    echo "  <meta_modulversion>".$this->modulversion."</meta_modulversion>\n";
    echo "</response>";
    exit();
  }
}
?>