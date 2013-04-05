<?php
include("klarna_settings.php");
$eid = $_POST['eid'];
$secret =  $_POST['secret'];
$file = fopen("estore.cfg.php", "w");
fwrite($file, "<?php\n\$eid=$eid;\n\$secret=\"$secret\";\n\$host=\"{$KLARNA_HOSTS[$_POST['host']]['host']}\";\n\$mode={$KLARNA_HOSTS[$_POST['host']]['mode']};\n?>");
fclose($file);
echo"Saved";
?>
