<?php
// listing
if (isset($_GET['show'])) {
  $_SESSION['listbox'] = (($_GET['show'] == 'box') ? 'true' : 'false');
}

// load Template config
if (Template::findPath('config/config.php') !== null) {
  require Template::path('config/config.php');
}
?>