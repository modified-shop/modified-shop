<?php
if (PRODUCT_LIST_FILTER == 'true') {

  // filter set
  $filter_vars_array = array();
  $filter_set_const = strtoupper(substr(basename($PHP_SELF), 0, -4));
  
  if (defined('DISPLAY_FILTER_'.$filter_set_const)) {
    $filter_vars_array = explode(',', constant('DISPLAY_FILTER_'.$filter_set_const));
    $key_all = array_search('all', $filter_vars_array);
    if ($key_all !== false && isset($filter_vars_array[$key_all])) {
      $filter_vars_array[$key_all] = '999999';
    }
    $filter_vars_array[] = '';
  }

  if (isset($_POST['filter_set'])) {
    $_SESSION['filter_set'] = (int)$_POST['filter_set'];
    $_SESSION['filter_set_id'] = array_search((int)$_POST['filter_set'], $filter_vars_array);
    
    xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), $request_type));
  }
  
  if (isset($_SESSION['filter_set']) && !in_array($_SESSION['filter_set'], $filter_vars_array) && isset($filter_vars_array[$_SESSION['filter_set_id']])) {
    $_SESSION['filter_set'] = $filter_vars_array[$_SESSION['filter_set_id']];
  }
  
  if (isset($_SESSION['filter_set']) && ($_SESSION['filter_set'] == 0 || $_SESSION['filter_set'] == '')) {
    unset($_SESSION['filter_set']);
  }
  
    
  // filter sort
  if (isset($_POST['filter_sort'])) {
    $_SESSION['filter_sort'] = intval($_POST['filter_sort']);

    switch ((int)$_POST['filter_sort']) {
      case 1:
        $sorting = ' ORDER BY pd.products_name ASC';
        break;
      case 2:
        $sorting = ' ORDER BY pd.products_name DESC';
        break;
      case 3:
        $sorting = ' ORDER BY price ASC';
        break;
      case 4:
        $sorting = ' ORDER BY price DESC';
        break;
      case 5:
        $sorting = ' ORDER BY p.products_date_added DESC';
        break;
      case 6:
        $sorting = ' ORDER BY p.products_date_added ASC';
        break;
      case 7:
        $sorting = ' ORDER BY p.products_ordered DESC';
        break;  
    }
    $_SESSION['filter_sorting'] = $sorting;

    xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), $request_type));
  }

  if (isset($_SESSION['filter_sort']) && ($_SESSION['filter_sort'] == 0 || $_SESSION['filter_sort'] == '')) {
    unset($_SESSION['filter_sort']);
    unset($_SESSION['filter_sorting']);
  }
}
?>