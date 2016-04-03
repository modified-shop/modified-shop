<?php
// ECONDA TRACKING
if (TRACKING_ECONDA_ACTIVE=='true') {
    echo '<script type="text/javascript"><!--'.PHP_EOL.
        '  var emos_kdnr="'. TRACKING_ECONDA_ID. '";'.PHP_EOL.
        '//--></script>'.PHP_EOL.
        '<a name="emos_sid" rel="'. xtc_session_id().'"></a>'.PHP_EOL.
        '<a name="emos_name" title="siteid" rel="'. $_SESSION['languages_id'].'" rev=""></a>'.PHP_EOL;
}
?>