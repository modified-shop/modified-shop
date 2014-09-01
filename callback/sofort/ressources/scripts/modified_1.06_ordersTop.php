<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="<?php echo '../callback/sofort/ressources/style/sofort.css';?>">
<?php //BOF web28 2010-12-09 add table style ?>
<style type="text/css">
<!--
.table{width: 850px; border: 1px solid #a3a3a3; margin-bottom:20px; background: #f3f3f3; padding:2px;}
.heading{font-family: Verdana, Arial, sans-serif; font-size: 12px; font-weight: bold; padding:2px; }    
.last_row{background-color: #D9E9FF;}  
-->
</style>
<?php //EOF web28 2010-12-09 add table style ?> 
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php
require (DIR_WS_INCLUDES.'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
        <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        <!-- left_navigation_eof //-->
      </table>
    </td>
<!-- body_text //-->
    <td  class="boxCenter" width="100%" valign="top">
  
<table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
if (($action == 'edit') && ($order_exists)) {
  //    $order = new order($oID);
?>
      <tr>
      <td width="100%">
<?php
}
?>