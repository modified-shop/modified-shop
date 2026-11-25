<?php
// ECONDA TRACKING
if (TRACKING_ECONDA_ACTIVE=='true') {
    // econda groups the page views of a visit by this id; the session id of the shop must not leave the shop
    if (!isset($_SESSION['econda_visit_id']) || !is_string($_SESSION['econda_visit_id'])) {
        $_SESSION['econda_visit_id'] = bin2hex(random_bytes(16));
    }
    echo '<script type="text/javascript"><!--'.PHP_EOL.
        '  var emos_kdnr="'. TRACKING_ECONDA_ID. '";'.PHP_EOL.
        '//--></script>'.PHP_EOL.
        '<a name="emos_sid" rel="'. $_SESSION['econda_visit_id'].'"></a>'.PHP_EOL.
        '<a name="emos_name" title="siteid" rel="'. $_SESSION['languages_id'].'" rev=""></a>'.PHP_EOL;
}
?>