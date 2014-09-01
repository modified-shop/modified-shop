<?php

/* -----------------------------------------------------------------------------------------
   $Id: reviews.php 842 2005-03-24 14:35:02Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.9 2003/02/12); www.oscommerce.com 
   (c) 2003	 nextcommerce (reviews.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
?>
<table border="0" cellspacing="0" cellpadding="2">
<?php

if (sizeof($reviews_array) < 1) {
?>
  <tr>
    <td class="main"><?php echo TEXT_NO_REVIEWS; ?></td>
  </tr>
<?php

} else {
	for ($i = 0, $n = sizeof($reviews_array); $i < $n; $i ++) {
?>
  <tr>
    <td valign="top" class="main"><a href="<?php echo xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews_array[$i]['products_id'] . '&reviews_id=' . $reviews_array[$i]['reviews_id']) . '">' . xtc_image(DIR_WS_THUMBNAIL_IMAGES . $reviews_array[$i]['products_image'], $reviews_array[$i]['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a></td>
    <td valign="top" class="main"><a href="<?php echo xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews_array[$i]['products_id'] . '&reviews_id=' . $reviews_array[$i]['reviews_id']) . '"><strong><u>' . $reviews_array[$i]['products_name'] . '</u></strong></a> (' . sprintf(TEXT_REVIEW_BY, $reviews_array[$i]['authors_name']) . ', ' . sprintf(TEXT_REVIEW_WORD_COUNT, $reviews_array[$i]['word_count']) . ')<br />' . $reviews_array[$i]['review'] . '<br /><br /><i>' . sprintf(TEXT_REVIEW_RATING, xtc_image(DIR_WS_IMAGES . 'stars_' . $reviews_array[$i]['rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $reviews_array[$i]['rating'])), sprintf(TEXT_OF_5_STARS, $reviews_array[$i]['rating'])) . '<br />' . sprintf(TEXT_REVIEW_DATE_ADDED, $reviews_array[$i]['date_added']) . '</i>'; ?></td>
  </tr>
<?php

		if (($i +1) != $n) {
?>
  <tr>
    <td colspan="2" class="main">&nbsp;</td>
  </tr>
<?php

		}
	}
}
?>
</table>