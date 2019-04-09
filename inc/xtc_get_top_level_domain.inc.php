<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_top_level_domain.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

function xtc_get_top_level_domain($url) {  
  if (strpos($url, '://')) {
    $url = parse_url($url);
    $url = $url['host'];
  }
  
  $cookie_domain = $url;
  $domains = get_cookie_domains($url);
  if (count($domains) > 0) {
    $cookie_domain = array_shift($domains);
  }
  
  // set array
  $return_array = array(
    'delete' => $domains, 
    'domain' => $cookie_domain,
  );  
  
  return $return_array;
}


function get_cookie_domains($domain, &$domain_array = array()) {
  static $tld_domain_array;
    
  if (!is_array($tld_domain_array)) {
    $tld_domain_array = array();
  }
  
  if (is_file(DIR_FS_CATALOG.'includes/data/public_suffix_list.dat')
      && count($tld_domain_array) < 1
      )
  {
    $public_suffix_list = explode("\n", file_get_contents(DIR_FS_CATALOG.'includes/data/public_suffix_list.dat'));

    foreach ($public_suffix_list as $data) {
      if ($data != '' && strpos($data, '/') === false) {
        $tld_domain_array[] = $data;
      }
    }
  }
  
  if (count($tld_domain_array) > 0
      && strpos($domain, '.') !== false
      && !in_array($domain, $tld_domain_array)
      )
  {  
    $domain_array[] = $domain;
    return get_cookie_domains(substr($domain, strpos($domain, '.') + 1), $domain_array);
  }
  
  return $domain_array;
}
?>