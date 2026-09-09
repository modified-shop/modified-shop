<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Load the shared error-log page controller (like Otto) so the router's
// class_exists('MagnaCompatibleErrorLog') check passes and it runs process(),
// which loads TemuErrorView and renders the log exactly once. Previously this
// file echoed the view directly on load while MagnaCompatibleErrorLog stayed
// undefined, so the router fell through to its "This is not supported" fallback,
// printed right after the rendered log.
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog.php');
