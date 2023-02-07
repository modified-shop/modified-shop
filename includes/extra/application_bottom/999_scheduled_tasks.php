<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  if (defined('CRONJOB_NEXT_EVENT_TIME') 
      && time() >= (int)CRONJOB_NEXT_EVENT_TIME
      )
  {
    ?>
    <script type="text/javascript">
      $(document).ready(function() {
        $.ajax({
          dataType: "json",
          type: 'get',
          url: '<?php echo DIR_WS_BASE; ?>ajax.php?speed=1&ext=scheduled_tasks',
          cache: false,
          async: true,
        });
      });
    </script>
    <?php
  }