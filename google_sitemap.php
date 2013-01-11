<?php 
/* 
osCommerce, Open Source E-Commerce Solutions 
http://www.oscommerce.com 

Copyright (c) 2005 osCommerce 

Released under the GNU General Public License 

@Author: Raphael Vullriede (osc@rvdesign.de) 

Port to xtCommerce 

@Author: Winfried Kaiser (w.kaiser@fortune.de) 
*/ 

require('includes/application_top.php'); 

// if the customer is not logged on, redirect them to the login page 
if (!isset($_SESSION['customer_id'])) { 

    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'NONSSL')); 
} 
// XML-Specification: https://www.google.com/webmasters/sitemaps/docs/de/protocol.html 

define('CHANGEFREQ_CATEGORIES', 'weekly');  // Valid values are "always", "hourly", "daily", "weekly", "monthly", "yearly" and "never". 
define('CHANGEFREQ_PRODUCTS', 'daily'); // Valid values are "always", "hourly", "daily", "weekly", "monthly", "yearly" and "never". 

define('PRIORITY_CATEGORIES', '1.0'); 
define('PRIORITY_PRODUCTS', '0.5'); 

define('MAX_ENTRYS', 50000); 
define('MAX_SIZE', 10000000); 
define('GOOGLE_URL', 'http://www.google.com/webmasters/sitemaps/ping?sitemap='); 
// BOF - Tomcraft - 2010-02-09 - Changed LIVE_URL
//define('LIVE_URL', 'http://webmaster.live.com/webmaster/ping.aspx?siteMap=');      
define('LIVE_URL', 'http://www.bing.com/webmaster/ping.aspx?siteMap=');
// EOF - Tomcraft - 2010-02-09 - Changed LIVE_URL
define('ASK_URL', 'http://submissions.ask.com/ping?sitemap=');     
$SEO_DOMAINS = array(LIVE_URL,ASK_URL,GOOGLE_URL); 

define('SITEMAPINDEX_HEADER', "<?xml version='1.0' encoding='UTF-8'?>"."\n".' 
          <sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84"'."\n".' 
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n".' 
              xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84'."\n".' 
              http://www.google.com/schemas/sitemap/0.84/siteindex.xsd">'."\n" 
); 
define('SITEMAPINDEX_FOOTER', '</sitemapindex>'); 
define('SITEMAPINDEX_ENTRY', "\t".'<sitemap>'."\n\t\t".'<loc>%s</loc>'."\n\t\t".'<lastmod>%s</lastmod>'."\n\t".'</sitemap>'."\n"); 

define('SITEMAP_HEADER', "<?xml version='1.0' encoding='UTF-8'?>"."\n".' 
          <urlset xmlns="http://www.google.com/schemas/sitemap/0.84"'."\n".' 
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n".' 
              xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84'."\n".' 
              http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'."\n" 
); 
define('SITEMAP_FOOTER', '</urlset>'); 
define('SITEMAP_ENTRY', "\t".'<url>'."\n\t\t".'<loc>%s</loc>'."\n\t\t".'<priority>%s</priority>'."\n\t\t".'<lastmod>%s</lastmod>'."\n\t\t".'<changefreq>%s</changefreq>'."\n\t".'</url>'."\n"); 

$smarty = new Smarty; 

$breadcrumb->add('Google Sitemap', xtc_href_link(FILENAME_GOOGLE_SITEMAP, xtc_get_all_get_params(), 'NONSSL')); 

// include boxes 
require(DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/boxes.php'); 

require(DIR_WS_INCLUDES . 'header.php'); 
include (DIR_WS_MODULES . 'default.php'); 

define('SITEMAP_CATALOG', HTTP_SERVER.DIR_WS_CATALOG); 

$usegzip        = false; 
$autogenerate   = false; 
$output_to_file = false; 
$notify_google  = false; 
$notify_url     = ''; 

// request over http or command line? 
if (!isset($_SERVER['SERVER_PROTOCOL'])) { 

    if (count($_SERVER['argv'] > 1)) { 

        // option p ist only possible of min 1 more option isset 
        if ( (strlen($_SERVER['argv'][1]) >= 2) && strpos($_SERVER['argv'][1], 'p') !== true) { 
            $notify_google = true; 
            $_SERVER['argv'][1] = str_replace('p', '', $_SERVER['argv'][1]); 
        } 

        switch($_SERVER['argv'][1]) { 

            // dump to file 
            case '-f': 
            $output_to_file = true; 
            $filename = $_SERVER['argv'][2]; 
            break; 

            // dump to compressed file 
            case '-zf': 
            $usegzip        = true; 
            $output_to_file = true; 
            $filename = $_SERVER['argv'][2]; 
            break; 

            // autogenerate sitemaps. useful for sites with more the 500000 Urls 
            case '-a': 
            $autogenerate = true; 
            break; 

            // autogenerate sitemaps and use gzip 
            case '-za': 
            $autogenerate   = true; 
            $usegzip        = true; 
            break; 
        } 
    } 
} else { 

    if (count($_GET) > 0) { 

        // dump to file 
        if (isset($_GET['f'])) { 
            $output_to_file = true; 
            $filename = $_GET['f']; 
        } 
        // use gzip 
        $usegzip = (isset($_GET['gzip']) && $_GET['gzip'] == true) ? true : false; 

        // autogenerate sitemaps 
        $autogenerate = (isset($_GET['auto']) && $_GET['auto'] == true) ? true : false; 

        // notify google 
        $notify_google = (isset($_GET['ping']) && $_GET['ping'] == true) ? true : false; 
    } 
} 

// use gz... functions for compressed files 
if ($usegzip) { 
    $function_open  = 'gzopen'; 
    $function_close = 'gzclose'; 
    $function_write = 'gzwrite'; 

    $file_extension = '.xml.gz'; 
} else { 
    $function_open  = 'fopen'; 
    $function_close = 'fclose'; 
    $function_write = 'fwrite'; 

    $file_extension = '.xml'; 
} 

$c = 0;
//BOF - Dokuman - 2009-11-09 - Create "sitemap.xml" instead of "sitemap1.xml"
//$i = 1; 
$i = ''; 
//EOF - Dokuman - 2009-11-09 - Create "sitemap.xml" instead of "sitemap1.xml"

$sitemap_filename = 'sitemap'.$i.$file_extension; 
if ($autogenerate) { 
    $filename = $sitemap_filename; 
} 
$autogenerate = $autogenerate || $output_to_file; 
if ($autogenerate) { 
    $fp = $function_open($filename, 'w'); 
    $main_content = "Sitemap-Datei '<b>" . $filename . "</b>' erstellt."; 
} 
$notify_url = SITEMAP_CATALOG.$sitemap_filename; 

output(SITEMAP_HEADER); 
$strlen = strlen(SITEMAP_HEADER); 

$cat_result = xtc_db_query(" 
    SELECT 
      c.*, 
      cd.*, 
      UNIX_TIMESTAMP(c.date_added) as date_added, 
      UNIX_TIMESTAMP(c.last_modified) as last_modified, 
      l.code 
    FROM  
      ".TABLE_CATEGORIES." c, 
      ".TABLE_CATEGORIES_DESCRIPTION." cd, 
      ".TABLE_LANGUAGES." l 
    WHERE 
      c.categories_id = cd.categories_id AND 
      cd.language_id = l.languages_id AND 
      c.categories_status = 1 
    ORDER by  
      cd.categories_id 
  "); 

$cat_array = array(); 
if (xtc_db_num_rows($cat_result) > 0) { 
    while($cat_data = xtc_db_fetch_array($cat_result)) { 
        $cat_array[$cat_data['categories_id']][$cat_data['code']] = $cat_data; 
    } 
} 
reset($cat_array); 

foreach($cat_array as $lang_array) { 
    foreach($lang_array as $cat_id => $cat_data) { 
        $lang_param = ($cat_data['code'] != DEFAULT_LANGUAGE) ? '&language='.$cat_data['code'] : ''; 
        $date = ($cat_data['last_modified'] != NULL) ? $cat_data['last_modified'] : $cat_data['date_added']; 
         
        /** 
         * @author Timo Paul (mail[at]timopaul.biz) 
         * @since Saturday, 16-th May 2009 
         *  
         * generate seo-frendly uri's 
         */ 
        $cPath_new = xtc_category_link($cat_data['categories_id'], $cat_data['categories_name']); 
        $string = sprintf(SITEMAP_ENTRY, xtc_href_link(FILENAME_DEFAULT, $cPath_new), PRIORITY_CATEGORIES, iso8601_date($date), CHANGEFREQ_CATEGORIES); 
             
        $c_cat_total++; 
        output_entry(); 
    } 
} 

$stmt = " 
    SELECT 
      p.*, 
      pd.*, 
      UNIX_TIMESTAMP(p.products_date_added) as products_date_added, 
      UNIX_TIMESTAMP(p.products_last_modified) as products_last_modified, 
      l.* 
    FROM 
      ".TABLE_PRODUCTS." p,  
      ".TABLE_PRODUCTS_DESCRIPTION." pd, 
      ".TABLE_LANGUAGES." l 
    WHERE 
      p.products_status='1' AND 
      p.products_id = pd.products_id AND 
      pd.language_id = l.languages_id 
    ORDER BY 
      p.products_id 
  "; 

$product_result = xtc_db_query($stmt); 
if (xtc_db_num_rows($product_result) > 0) { 
    while($product_data = xtc_db_fetch_array($product_result)) { 

        /** 
         * @author Timo Paul (mail[at]timopaul.biz) 
         * @since Saturday, 16-th May 2009 
         *  
         * generate article-array with valid seo-uri's 
         */ 
        $pArray = $product->buildDataArray($product_data); 
         
        $lang_param = ($product_data['code'] != DEFAULT_LANGUAGE) ? '&language='.$product_data['code'] : ''; 
        $date = ($product_data['products_last_modified'] != NULL) ? $product_data['products_last_modified'] : $product_data['products_date_added']; 
        $string = sprintf(SITEMAP_ENTRY, $pArray['PRODUCTS_LINK'], PRIORITY_PRODUCTS, iso8601_date($date), CHANGEFREQ_PRODUCTS); 

        $c_prod_total++; 
        output_entry(); 
    } 
} 


output(SITEMAP_FOOTER); 
if ($autogenerate) { 
    $function_close($fp); 
} 

$main_content .= "<br><br>" . $c_cat_total . " <b>Kategorien</b> und " . $c_prod_total . " <b>Produkte</b> exportiert."; 
// generates sitemap-index file 
if ($autogenerate && $i > 1) { 
    $sitemap_index_file = 'sitemap_index'.$file_extension; 
    $main_content = $main_content . "<br><br>Sitemap-Index-Datei '<b>" . $sitemap_index_file . "</b>' erstellt."; 
    $notify_url = SITEMAP_CATALOG.$sitemap_index_file; 
    $fp = $function_open('sitemap_index'.$file_extension, 'w'); 
    $function_write($fp, SITEMAPINDEX_HEADER); 
    for($ii=1; $ii<=$i; $ii++) { 
        $function_write($fp, sprintf(SITEMAPINDEX_ENTRY, SITEMAP_CATALOG.'sitemap'.$ii.$file_extension, iso8601_date(time()))); 
    } 
    $function_write($fp, SITEMAPINDEX_FOOTER); 
    $function_close($fp); 
} 

if ($notify_google) { 
    foreach (sitemap_curl($notify_url, $SEO_DOMAINS) as $value) { 
        $main_content .= $value.'<hr />'; 
    } 
} 

$smarty->caching = 0; 
$smarty->assign('language', $_SESSION['language']); 
$smarty->assign('CONTENT_BODY',$main_content); 
$smarty->assign('BUTTON_CONTINUE','<a href="' . xtc_href_link(FILENAME_START) . '">' . xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'); 
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/google_sitemap.html'); 
$smarty->assign('main_content',$main_content); 
if (!defined(RM)) $smarty->load_filter('output', 'note'); 
$smarty->display(CURRENT_TEMPLATE . '/index.html'); 


// < PHP5 
function iso8601_date($timestamp) { 

    if (PHP_VERSION < 5) { 
        $tzd = date('O',$timestamp); 
        $tzd = substr(chunk_split($tzd, 3, ':'),0,6); 
        return date('Y-m-d\TH:i:s', $timestamp) . $tzd; 
    } else { 
        return date('c', $timestamp); 
    } 
} 

// generates cPath with helper array 
function rv_get_path($cat_id, $code) { 
    global $cat_array; 

    $my_cat_array = array($cat_id); 

    while($cat_array[$cat_id][$code]['parent_id'] != 0) { 
        $my_cat_array[] = $cat_array[$cat_id][$code]['parent_id']; 
        $cat_id = $cat_array[$cat_id][$code]['parent_id']; 
    } 

    return 'cPath='.implode('_', array_reverse($my_cat_array)); 
} 


function output($string) { 
    global $function_open, $function_close, $function_write, $fp, $autogenerate; 

    if ($autogenerate) { 
        $function_write($fp, $string); 
    } else { 
        echo $string; 
    } 
} 

function output_entry() 
{ 
    global $string, $strlen, $c, $autogenerate, $fp, $function_open, $function_close, $main_content, $strlen; 
     
    output($string); 
    $strlen += strlen($string); 
    $c++; 
    if ($autogenerate) { 
        // 500000 entrys or filesize > 10,485,760 - some space for the last entry 
        if ( $c == MAX_ENTRYS || $strlen >= MAX_SIZE) { 
            output(SITEMAP_FOOTER); 
            $function_close($fp); 
            $c = 0; 
            $i++; 
            $filename = 'sitemap'.$i.$file_extension; 
            $fp = $function_open($filename, 'w'); 
            $main_content = $main_content . "<br>Sitemap-Datei '<b>" . $filename . "</b>' erstellt."; 
            output(SITEMAP_HEADER); 
            $strlen = strlen(SITEMAP_HEADER); 
        } 
    } 
} 

// function made by Mathis Klooss (www.gunah.eu) 
function sitemap_curl( $notify_url , $mixed=array() ) { 
    $result = ''; 
    $allow_url_fopen = ini_get("allow_url_fopen"); 
    foreach ($mixed as $value) { 
        if($allow_url_fopen == 0 || function_exists('curl_exec') == true) { 
            ob_start(); 
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $value . urlencode($notify_url)); 
            $user_agent = 'Mozilla/4.0 (compatible; xtc; sitemap-submitter) xt:commerce sitemap-submitter'; 
            curl_setopt ( $ch , CURLOPT_USERAGENT, $user_agent); 
            $test = curl_exec($ch); 
            curl_close($ch); 
            $ob_get_contents = ob_get_contents(); 
            ob_end_clean(); 
            $out = sitemap_replace($ob_get_contents); 
            $result[] = '<div>'.$value.encode_htmlentities($notify_url).'</div>'.$out; 
        } elseif($allow_url_fopen == 1) { 
            fopen($value.urlencode($notify_url), 'r'); 
            $response = file_get_contents($value . urlencode($notify_url)); 
            $result[] = '<div>'.$value.encode_htmlentities($notify_url).'</div>'.sitemap_replace($response); 
        } 
    } 
    return $result; 
} 

function sitemap_replace($result) { 
    preg_match('/<body>(.*?)<\/body>/si', $result, $result); 
     
    $out = preg_replace( '/<img(.*?)>/si'  , ''  , $result['1']); 
    $out = preg_replace("/<br(.*?)>/si", "<br />", $out); 
    $out = preg_replace("/<h(.*?)>(.*?)<\/h(.*?)>/si", "<h2>\\2</h2>", $out); 
    $out = str_replace("<br>", "<br />", $out);   
    $out = preg_replace("/<div(.*?)>(.*?)<\/div>/si", "<div>\\2</div>", $out); 
    $out = preg_replace("/<br(.*?)>(.*?)<br(.*?)>(.*?)<br(.*?)>(.*?)<br(.*?)>/si", "", $out);     
    $out = strip_tags($out,'<a>,<p>,<br>,<h2>,<div>'); 
    return $out;   
} 
require(DIR_WS_INCLUDES . 'application_bottom.php');  
?> 
