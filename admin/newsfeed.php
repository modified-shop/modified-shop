<?php
  /* --------------------------------------------------------------
   $Id: start.php 4738 2013-05-07 15:57:00Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project
   (c) 2002-2003 osCommerce coding standards (a typical file) www.oscommerce.com
   (c) 2003 nextcommerce (start.php,1.5 2004/03/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (start.php 1235 2005-09-21)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_NEWS_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

// update last read
xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".time()."' WHERE configuration_key = 'NEWSFEED_LAST_READ'");

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript">
    $(function() {
      $('.blog_title').click(function(e) {
        var the_block = $(this).next('.blogentry');
        var the_active_block = $(this);
        
        $('.blog_title + .blogentry').not(the_block).slideUp(300);
        $('.blog_title').not(the_active_block).removeClass('active');
        the_active_block.toggleClass('active');
        
        if (the_active_block.hasClass('active')) {
          the_block.slideDown(300);
        } else {
          the_block.slideUp(300);
        }          
      });
    });
  </script>
  <style type="text/css">
    body {
      font-family:Verdana, Arial, sans-serif;
      font-size:12px;
    }
  </style>
</head>
<body>   
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <table class="tableBody pdg5">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td width="100%" valign="top">
        <div class="admin_container cf">
          <div class="admin_headline"><a target="_blank" href="<?php echo RSS_FEED_LINK; ?>"><?php echo RSS_FEED_TITLE; ?></a></div>
          <div class="admin_contentbox">
            <?php
            $news_query_raw = "SELECT * FROM newsfeed ORDER BY news_date DESC";
            $news_split = new splitPageResults($_GET['page'], $page_max_display_results, $news_query_raw, $news_query_numrows);
            $news_query = xtc_db_query($news_query_raw);
            if (xtc_db_num_rows($news_query) > 0) {
              $i = 0;
              while ($news = xtc_db_fetch_array($news_query)) {
                $news['news_text'] = str_replace('src="http://', 'src="https://', $news['news_text']);
                ?>
                <div class="blog_title<?php echo (($i == 0) ? ' active' : ''); ?>"><?php echo $news['news_title']; ?></div>                          
                <div class="blogentry" <?php echo (($i != 0) ? ' style="display:none; margin-top:2px;"' : ''); ?>>
                  <div class="blog_date"><?php echo date('d.m.Y', $news['news_date']); ?></div>                                      
                  <div class="blog_desc"><?php echo $news['news_text']; ?></div>
                  <div class="blog_read_more"><a target="_blank" href="<?php echo $news['news_link']; ?>">weiterlesen &raquo;</a></div>
                </div>
                <?php
                $i ++;
              }
            } else {
            ?>
              <div class="blogentry">
                <div class="blog_title"><?php echo RSS_FEED_ALTERNATIVE; ?></div>                          
                <div class="blog_desc"><?php echo RSS_FEED_DESCRIPTION; ?></div>
              </div>
            <?php
            }
          ?>           
          </div>
        </div>
        <div class="smallText pdg2 flt-l"><?php echo $news_split->display_count($news_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_NEWSFEED); ?></div>
        <div class="smallText pdg2 flt-r"><?php echo $news_split->display_links($news_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
        <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>