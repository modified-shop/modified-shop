<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_box_content_bullet.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(output.php,v 1.3 2002/06/01); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_draw_box_content_bullet.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_draw_box_content_bullet($bullet_text, $bullet_link = '') {
    global $page_file;

    $bullet = '      <tr>' . CR .
              '        <td><table border="0" cellspacing="0" cellpadding="0">' . CR .
              '          <tr>' . CR .
              '            <td width="12" class="boxText"><img src="images/icon_pointer.gif" border="0" alt=""></td>' . CR .
              '            <td class="infoboxText">';
    if ($bullet_link) {
      if ($bullet_link == $page_file) {
        $bullet .= '<font color="#0033cc"><strong>' . $bullet_text . '</strong></font>';
      } else {
        $bullet .= '<a href="' . $bullet_link . '">' . $bullet_text . '</a>';
      }
    } else {
      $bullet .= $bullet_text;
    }

    $bullet .= '</td>' . CR .
               '         </tr>' . CR .
               '       </table></td>' . CR .
               '     </tr>' . CR;

    return $bullet;
  }
 ?>