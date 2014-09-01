<?php
  /* -----------------------------------------------------------------------------------------
   $Id: create_account.php 3198 2012-07-11 09:41:52Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(customers.php,v 1.76 2003/05/04); www.oscommerce.com
   (c) 2003 nextcommerce (create_account.php,v 1.17 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require ('includes/application_top.php');
  require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
  require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'class.phpmailer.php');
  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
  require_once (DIR_FS_INC.'xtc_create_password.inc.php');
  require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');

  // initiate template engine for mail
  $smarty = new Smarty;

  $customers_statuses_array = xtc_get_customers_statuses();
  if (!isset($customers_password)) {
    $customers_password_encrypted = xtc_RandomString(8);
    $customers_password = xtc_encrypt_password($customers_password_encrypted);
  }
  if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $customers_firstname = xtc_db_prepare_input($_POST['customers_firstname']);
    $customers_cid = xtc_db_prepare_input($_POST['csID']);
    $customers_vat_id = xtc_db_prepare_input($_POST['customers_vat_id']);
    $customers_vat_id_status = xtc_db_prepare_input($_POST['customers_vat_id_status']);
    $customers_lastname = xtc_db_prepare_input($_POST['customers_lastname']);
    $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);
    $customers_telephone = xtc_db_prepare_input($_POST['customers_telephone']);
    $customers_fax = xtc_db_prepare_input($_POST['customers_fax']);
    $customers_status_c = xtc_db_prepare_input($_POST['status']);

    $customers_gender = xtc_db_prepare_input($_POST['customers_gender']);
    $customers_dob = xtc_db_prepare_input($_POST['customers_dob']);

    $default_address_id = xtc_db_prepare_input($_POST['default_address_id']);
    $entry_street_address = xtc_db_prepare_input($_POST['entry_street_address']);
    $entry_suburb = xtc_db_prepare_input($_POST['entry_suburb']);
    $entry_postcode = xtc_db_prepare_input($_POST['entry_postcode']);
    $entry_city = xtc_db_prepare_input($_POST['entry_city']);
    $entry_country_id = xtc_db_prepare_input($_POST['entry_country_id']);

    $entry_company = xtc_db_prepare_input($_POST['entry_company']);
    $entry_state = xtc_db_prepare_input($_POST['entry_state']);
    $entry_zone_id = xtc_db_prepare_input($_POST['entry_zone_id']);

    $customers_send_mail = xtc_db_prepare_input($_POST['customers_mail']);
    $customers_password_encrypted = xtc_db_prepare_input($_POST['entry_password']);
    $customers_password = xtc_encrypt_password($customers_password_encrypted);

    $customers_mail_comments = xtc_db_prepare_input($_POST['mail_comments']);

    $payment_unallowed = xtc_db_prepare_input($_POST['payment_unallowed']);
    $shipping_unallowed = xtc_db_prepare_input($_POST['shipping_unallowed']);

    if ($customers_password == '') {
      $customers_password_encrypted =  xtc_RandomString(8);
      $customers_password = xtc_encrypt_password($customers_password_encrypted);
    }
    $error = false; // reset error flag

    if (ACCOUNT_GENDER == 'true') {
      if (($customers_gender != 'm') && ($customers_gender != 'f')) {
        $error = true;
        $entry_gender_error = true;
      } else {
        $entry_gender_error = false;
      }
    }

    if (strlen($customers_password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;
      $entry_password_error = true;
    } else {
      $entry_password_error = false;
    }

    if (($customers_send_mail != 'yes') && ($customers_send_mail != 'no')) {
      $error = true;
      $entry_mail_error = true;
    } else {
      $entry_mail_error = false;
    }

    if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $entry_firstname_error = true;
    } else {
      $entry_firstname_error = false;
    }

    if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $entry_lastname_error = true;
    } else {
      $entry_lastname_error = false;
    }

    if (ACCOUNT_DOB == 'true') {
      if (checkdate(substr(xtc_date_raw($customers_dob), 4, 2), substr(xtc_date_raw($customers_dob), 6, 2), substr(xtc_date_raw($customers_dob), 0, 4))) {
        $entry_date_of_birth_error = false;
      } else {
        $error = true;
        $entry_date_of_birth_error = true;
      }
    }

    // New VAT Check
    if (xtc_get_geo_zone_code($entry_country_id) != '6') {
      require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'vat_validation.php');
      $vatID = new vat_validation($customers_vat_id, '', '', $entry_country_id);
      $customers_vat_id_status = isset($vatID->vat_info['vat_id_status']) ? $vatID->vat_info['vat_id_status'] : '';

      // BOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
      switch ($customers_vat_id_status) {
        // 0 = 'VAT invalid'
        // 1 = 'VAT valid'
        // 2 = 'SOAP ERROR: Connection to host not possible, europe.eu down?'
        // 8 = 'unknown country'
        //94 = 'INVALID_INPUT'       => 'The provided CountryCode is invalid or the VAT number is empty',
        //95 = 'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
        //96 = 'MS_UNAVAILABLE'      => 'The Member State service is unavailable, try again later or with another Member State',
        //97 = 'TIMEOUT'             => 'The Member State service could not be reached in time, try again later or with another Member State',
        //98 = 'SERVER_BUSY'         => 'The service cannot process your request. Try again later.'
        //99 = 'no PHP5 SOAP support'
        case '0' :
          $entry_vat_error_text = TEXT_VAT_FALSE;
          break;
        case '1' :
          $entry_vat_error_text = TEXT_VAT_TRUE;
          break;
        case '2' :
          $entry_vat_error_text = TEXT_VAT_CONNECTION_NOT_POSSIBLE;
          break;
        case '8' :
          $entry_vat_error_text = TEXT_VAT_UNKNOWN_COUNTRY;
          break;
        case '94' :
          $entry_vat_error_text = TEXT_VAT_INVALID_INPUT;
          break;
        case '95' :
          $entry_vat_error_text = TEXT_VAT_SERVICE_UNAVAILABLE;
          break;
        case '96' :
          $entry_vat_error_text = TEXT_VAT_MS_UNAVAILABLE;
          break;
        case '97' :
          $entry_vat_error_text = TEXT_VAT_TIMEOUT;
          break;
        case '98' :
          $entry_vat_error_text = TEXT_VAT_SERVER_BUSY;
          break;
        case '99' :
          $entry_vat_error_text = TEXT_VAT_NO_PHP5_SOAP_SUPPORT;
          break;
        default:
          $entry_vat_error_text = '';
          break;
      }
      // EOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check

      if($vatID->vat_info['error']==1){
        $entry_vat_error = true;
        $error = true;
      }
    }
    // New VAT CHECK END

    if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $entry_email_address_error = true;
    } else {
      $entry_email_address_error = false;
    }

    if (!xtc_validate_email($customers_email_address)) {
      $error = true;
      $entry_email_address_check_error = true;
    } else {
      $entry_email_address_check_error = false;
    }

    if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;
      $entry_street_address_error = true;
    } else {
      $entry_street_address_error = false;
    }

    if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $entry_post_code_error = true;
    } else {
      $entry_post_code_error = false;
    }

    if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $entry_city_error = true;
    } else {
      $entry_city_error = false;
    }

    if ($entry_country_id == false) {
      $error = true;
      $entry_country_error = true;
    } else {
      $entry_country_error = false;
    }

    if (ACCOUNT_STATE == 'true') {
      if ($entry_country_error == true) {
        $entry_state_error = true;
      } else {
        $zone_id = 0;
        $entry_state_error = false;
        $check_query = xtc_db_query("-- /admin/create_account.php
                                     SELECT count(*) as total
                                       FROM ".TABLE_ZONES."
                                      WHERE zone_country_id = '".xtc_db_input($entry_country_id)."'");
        $check_value = xtc_db_fetch_array($check_query);
        $entry_state_has_zones = ($check_value['total'] > 0);
        if ($entry_state_has_zones == true) {
          $zone_query = xtc_db_query("-- /admin/create_account.php
                                      SELECT zone_id
                                        FROM ".TABLE_ZONES."
                                       WHERE zone_country_id = '".xtc_db_input($entry_country_id)."'
                                         AND zone_name = '".xtc_db_input($entry_state)."'");
          if (xtc_db_num_rows($zone_query) == 1) {
            $zone_values = xtc_db_fetch_array($zone_query);
            $entry_zone_id = $zone_values['zone_id'];
          } else {
            $zone_query = xtc_db_query("-- /admin/create_account.php
                                        SELECT zone_id
                                          FROM ".TABLE_ZONES."
                                         WHERE zone_country_id = '".xtc_db_input($entry_country)."'
                                           AND zone_code = '".xtc_db_input($entry_state)."'");
            if (xtc_db_num_rows($zone_query) >= 1) {
              $zone_values = xtc_db_fetch_array($zone_query);
              $zone_id = $zone_values['zone_id'];
            } else {
              $error = true;
              $entry_state_error = true;
            }
          }
        } else {
          if ($entry_state == false) {
            $error = true;
            $entry_state_error = true;
          }
        }
      }
    }

    if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;
      $entry_telephone_error = true;
    } else {
      $entry_telephone_error = false;
    }

    $check_email = xtc_db_query("-- /admin/create_account.php
                                 SELECT customers_email_address
                                   FROM ".TABLE_CUSTOMERS."
                                  WHERE customers_email_address = '".xtc_db_input($customers_email_address)."'
                                    AND customers_id <> '".xtc_db_input($customers_id)."'");
    if (xtc_db_num_rows($check_email)) {
      $error = true;
      $entry_email_address_exists = true;
    } else {
      $entry_email_address_exists = false;
    }

    if ($error == false) {
      $sql_data_array = array (
                               'customers_status' => $customers_status_c,
                               'customers_cid' => $customers_cid,
                               'customers_vat_id' => $customers_vat_id,
                               'customers_vat_id_status' => $customers_vat_id_status,
                               'customers_firstname' => $customers_firstname,
                               'customers_lastname' => $customers_lastname,
                               'customers_email_address' => $customers_email_address,
                               'customers_telephone' => $customers_telephone,
                               'customers_fax' => $customers_fax,
                               'payment_unallowed' => $payment_unallowed,
                               'shipping_unallowed' => $shipping_unallowed,
                               'customers_password' => $customers_password,
                               'customers_date_added' => 'now()',
                               'customers_last_modified' => 'now()'
                              );

      if (ACCOUNT_GENDER == 'true')
        $sql_data_array['customers_gender'] = $customers_gender;
      if (ACCOUNT_DOB == 'true')
        $sql_data_array['customers_dob'] = xtc_date_raw($customers_dob);

      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);
      $cc_id = xtc_db_insert_id();
      $sql_data_array = array ('customers_id' => $cc_id,
                                'entry_firstname' => $customers_firstname,
                                'entry_lastname' => $customers_lastname,
                                'entry_street_address' => $entry_street_address,
                                'entry_postcode' => $entry_postcode,
                                'entry_city' => $entry_city,
                                'entry_country_id' => $entry_country_id,
                                'address_date_added' => 'now()',
                                'address_last_modified' => 'now()');

      if (ACCOUNT_GENDER == 'true')
        $sql_data_array['entry_gender'] = $customers_gender;
      if (ACCOUNT_COMPANY == 'true')
        $sql_data_array['entry_company'] = $entry_company;
      if (ACCOUNT_SUBURB == 'true')
        $sql_data_array['entry_suburb'] = $entry_suburb;
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = $entry_zone_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = '0';
          $sql_data_array['entry_state'] = $entry_state;
        }
      }
      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
      $address_id = xtc_db_insert_id();
      xtc_db_query("UPDATE ".TABLE_CUSTOMERS." SET customers_default_address_id = '".$address_id."' WHERE customers_id = '".$cc_id."'");
      xtc_db_query("INSERT INTO ".TABLE_CUSTOMERS_INFO." (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) VALUES ('".$cc_id."', '0', now())");
      // Create insert into admin access table if admin is created.
      if ($customers_status_c == '0') {
        xtc_db_query("INSERT INTO ".TABLE_ADMIN_ACCESS." (customers_id,start) VALUES ('".$cc_id."','1')");
      }
      // Create eMail
      if (($customers_send_mail == 'yes')) {

        //BOF - DokuMan - 2011-02-02 - Fix for more personalized e-mails to the customer (show salutation and surname)
        if ($customers_gender =='f') {
          $smarty->assign('GENDER', FEMALE);
        } elseif ($customers_gender =='m') {
          $smarty->assign('GENDER', MALE);
        } else {
          $smarty->assign('GENDER', '');
        }
        $smarty->assign('LASTNAME',$customers_lastname);
        //EOF - DokuMan - 2011-02-02 - Fix for more personalized e-mails to the customer (show salutation and surname)

        // assign language to template for caching
        $smarty->assign('language', $_SESSION['language']);
        // set dirs manual
        $smarty->template_dir = DIR_FS_CATALOG.'templates';
        $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
        $smarty->config_dir = DIR_FS_CATALOG.'lang';
        //BOF - GTB - 2010-08-03 - Security Fix - Base
        $smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
        //$smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
        //EOF - GTB - 2010-08-03 - Security Fix - Base
        $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
        $smarty->assign('NAME', $customers_lastname.' '.$customers_firstname);
        $smarty->assign('EMAIL', $customers_email_address);
        $smarty->assign('COMMENTS', $customers_mail_comments);
        $smarty->assign('PASSWORD', $customers_password_encrypted);
        $smarty->caching = 0;

        $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/create_account_mail.html');
        $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/create_account_mail.txt');

        xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     $customers_email_address,
                     $customers_lastname.' '.$customers_firstname,
                     EMAIL_SUPPORT_FORWARDING_STRING,
                     EMAIL_SUPPORT_REPLY_ADDRESS,
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     EMAIL_SUPPORT_SUBJECT,
                     $html_mail,
                     $txt_mail);
      }
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'cID='.$cc_id, 'SSL'));
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
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
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="middle" class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <?php echo xtc_draw_form('customers', FILENAME_CREATE_ACCOUNT, xtc_get_all_get_params(array('action')) . 'action=edit', 'post', 'onSubmit="return check_form();"') . xtc_draw_hidden_field('default_address_id', isset($customers_default_address_id)?$customers_default_address_id:''); ?>
              <tr>
                <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
              </tr>
              <tr>
                <td class="formArea">
                  <table border="0" cellspacing="2" cellpadding="2">
                    <?php
                    if (ACCOUNT_GENDER == 'true') {
                      ?>
                      <tr>
                        <td class="main"><?php echo ENTRY_GENDER; ?></td>
                        <td class="main">
                          <?php
                          if (isset($error) && $error == true) {
                            if (isset($entry_gender_error) && $entry_gender_error == true) {
                              echo xtc_draw_radio_field('customers_gender', 'm', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.FEMALE.'&nbsp;'.ENTRY_GENDER_ERROR;
                            } else {
                              //echo ($customers_gender == 'm') ? MALE : FEMALE; //web28 2012-12-31 - fix twice display
                              echo xtc_draw_radio_field('customers_gender', 'm', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.FEMALE;
                            }
                          } else {
                            echo xtc_draw_radio_field('customers_gender', 'm', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, isset($customers_gender)?$customers_gender:'').'&nbsp;&nbsp;'.FEMALE;
                          }
                          ?>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr>
                      <td class="main"><?php echo ENTRY_CID; ?></td>
                      <td class="main"><?php echo xtc_draw_input_field('csID', isset($customers_cid)?$customers_cid:'', 'maxlength="32"'); ?></td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_firstname_error) && $entry_firstname_error == true) {
                            echo xtc_draw_input_field('customers_firstname', isset($customers_firstname)?$customers_firstname:'', 'maxlength="64"').'&nbsp;'.ENTRY_FIRST_NAME_ERROR;
                          } else {
                            echo xtc_draw_input_field('customers_firstname', isset($customers_firstname)?$customers_firstname:'', 'maxlength="64"');
                          }
                        } else {
                          echo xtc_draw_input_field('customers_firstname', isset($customers_firstname)?$customers_firstname:'', 'maxlength="64"');
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_lastname_error) && $entry_lastname_error == true) {
                            echo xtc_draw_input_field('customers_lastname', isset($customers_lastname)?$customers_lastname:'', 'maxlength="64"').'&nbsp;'.ENTRY_LAST_NAME_ERROR;
                          } else {
                            echo xtc_draw_input_field('customers_lastname', isset($customers_lastname)?$customers_lastname:'', 'maxlength="64"');
                          }
                        } else {
                          echo xtc_draw_input_field('customers_lastname', isset($customers_lastname)?$customers_lastname:'', 'maxlength="64"');
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                    if (ACCOUNT_DOB == 'true') {
                      ?>
                      <tr>
                        <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
                        <td class="main">
                          <?php
                          if (isset($error) && $error == true) {
                            if (isset($entry_date_of_birth_error) && $entry_date_of_birth_error == true) {
                              echo xtc_draw_input_field('customers_dob', xtc_date_short(isset($customers_dob)?$customers_dob:''), 'maxlength="10"').'&nbsp;'.ENTRY_DATE_OF_BIRTH_ERROR;
                            } else {
                              echo xtc_draw_input_field('customers_dob', xtc_date_short(isset($customers_dob)?$customers_dob:''), 'maxlength="10"');
                            }
                          } else {
                            echo xtc_draw_input_field('customers_dob', xtc_date_short(isset($customers_dob)?$customers_dob:''), 'maxlength="10"');
                          }
                          ?>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr>
                      <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
                      <td class="main">
                        <?php
                          if (isset($error) && $error == true) {
                            if (isset($entry_email_address_error) && $entry_email_address_error == true) {
                              echo xtc_draw_input_field('customers_email_address', isset($customers_email_address)?$customers_email_address:'', 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR;
                            } elseif ($entry_email_address_check_error == true) {
                              echo xtc_draw_input_field('customers_email_address', isset($customers_email_address)?$customers_email_address:'', 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
                            } elseif ($entry_email_address_exists == true) {
                              echo xtc_draw_input_field('customers_email_address', isset($customers_email_address)?$customers_email_address:'', 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
                            } else {
                              echo xtc_draw_input_field('customers_email_address', isset($customers_email_address)?$customers_email_address:'', 'maxlength="96"');
                            }
                          } else {
                            echo xtc_draw_input_field('customers_email_address', isset($customers_email_address)?$customers_email_address:'', 'maxlength="96"');
                          }
                        ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php
              if (ACCOUNT_COMPANY == 'true') {
                ?>
                <tr>
                  <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
                </tr>
                <tr>
                  <td class="formArea">
                    <table border="0" cellspacing="2" cellpadding="2">
                      <tr>
                        <td class="main"><?php echo ENTRY_COMPANY; ?></td>
                        <td class="main">
                          <?php
                            if (isset($error) && $error == true) {
                              if (isset($entry_company_error) && $entry_company_error == true) {
                                echo xtc_draw_input_field('entry_company', isset($entry_company)?$entry_company:'', 'maxlength="64"').'&nbsp;'.ENTRY_COMPANY_ERROR;
                              } else {
                                echo xtc_draw_input_field('entry_company', isset($entry_company)?$entry_company:'', 'maxlength="64"');
                              }
                            } else {
                              echo xtc_draw_input_field('entry_company', isset($entry_company)?$entry_company:'', 'maxlength="64"');
                            }
                          ?>
                        </td>
                      </tr>
                      <?php
                        if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
                          ?>
                          <tr>
                            <td class="main"><?php echo ENTRY_VAT_ID; ?></td>
                            <td class="main">
                              <?php
                              // BOF - Dokuman - 2011-07-28 - display correct error code of VAT ID check
                              echo xtc_draw_input_field('customers_vat_id', isset($customers_vat_id)?$customers_vat_id:'', 'maxlength="32"').'&nbsp;'.(isset($entry_vat_error_text)?$entry_vat_error_text:'');
                              /*
                              if ($error == true) {
                                if ($entry_vat_error == true) {
                                  echo xtc_draw_input_field('customers_vat_id', $customers_vat_id, 'maxlength="32"').'&nbsp;'.$entry_vat_error_text;
                                } else {
                                  echo xtc_draw_input_field('customers_vat_id', $customers_vat_id, 'maxlength="32"');
                                }
                              } else {
                                echo xtc_draw_input_field('customers_vat_id', $customers_vat_id, 'maxlength="32"');
                              }
                              */
                              // EOF - Dokuman - 2011-07-28 - display correct error code of VAT ID check
                              ?>
                            </td>
                          </tr>
                          <?php
                        }
                      ?>
                    </table>
                  </td>
                </tr>
                <?php
              }
              ?>
              <tr>
                <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
              </tr>
              <tr>
                <td class="formArea">
                  <table border="0" cellspacing="2" cellpadding="2">
                    <tr>
                      <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_street_address_error) && $entry_street_address_error == true) {
                            echo xtc_draw_input_field('entry_street_address', isset($entry_street_address)?$entry_street_address:'', 'maxlength="64"').'&nbsp;'.ENTRY_STREET_ADDRESS_ERROR;
                          } else {
                            echo xtc_draw_input_field('entry_street_address', isset($entry_street_address)?$entry_street_address:'', 'maxlength="64"');
                          }
                        } else {
                          echo xtc_draw_input_field('entry_street_address', isset($entry_street_address)?$entry_street_address:'', 'maxlength="64"');
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                    if (ACCOUNT_SUBURB == 'true') {
                      ?>
                      <tr>
                        <td class="main"><?php echo ENTRY_SUBURB; ?></td>
                        <td class="main">
                          <?php
                          if (isset($error) && $error == true) {
                            if (isset($entry_suburb_error) && $entry_suburb_error == true) {
                              echo xtc_draw_input_field('suburb', isset($entry_suburb)?$entry_suburb:'', 'maxlength="32"').'&nbsp;'.ENTRY_SUBURB_ERROR;
                            } else {
                              echo xtc_draw_input_field('entry_suburb', isset($entry_suburb)?$entry_suburb:'', 'maxlength="32"');
                            }
                          } else {
                            echo xtc_draw_input_field('entry_suburb', isset($entry_suburb)?$entry_suburb:'', 'maxlength="32"');
                          }
                          ?>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr>
                      <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_post_code_error) && $entry_post_code_error == true) {
                            echo xtc_draw_input_field('entry_postcode', isset($entry_postcode)?$entry_postcode:'', 'maxlength="8"').'&nbsp;'.ENTRY_POST_CODE_ERROR;
                          } else {
                            echo xtc_draw_input_field('entry_postcode', isset($entry_postcode)?$entry_postcode:'', 'maxlength="8"');
                          }
                        } else {
                          echo xtc_draw_input_field('entry_postcode', isset($entry_postcode)?$entry_postcode:'', 'maxlength="8"');
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_CITY; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_city_error) && $entry_city_error == true) {
                            echo xtc_draw_input_field('entry_city', isset($entry_city)?$entry_city:'', 'maxlength="64"').'&nbsp;'.ENTRY_CITY_ERROR;
                          } else {
                            echo xtc_draw_input_field('entry_city', isset($entry_city)?$entry_city:'', 'maxlength="64"');
                          }
                        } else {
                          echo xtc_draw_input_field('entry_city', isset($entry_city)?$entry_city:'', 'maxlength="64"');
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                    if (ACCOUNT_STATE == 'true') {
                      ?>
                      <tr>
                        <td class="main"><?php echo ENTRY_STATE; ?></td>
                        <td class="main">
                          <?php
                          $entry_state = xtc_get_zone_name(isset($entry_country_id)?$entry_country_id:'',
                                                           isset($entry_zone_id)?$entry_zone_id:'',
                                                           isset($entry_state)?$entry_state:'');
                          if (isset($error) && $error == true) {
                            if (isset($entry_state_error) && $entry_state_error == true) {
                              if ($entry_state_has_zones == true) {
                                $zones_array = array ();
                                $zones_query = xtc_db_query("select zone_name from ".TABLE_ZONES." where zone_country_id = '".xtc_db_input($entry_country_id)."' order by zone_name");
                                while ($zones_values = xtc_db_fetch_array($zones_query)) {
                                  $zones_array[] = array ('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
                                }
                                echo xtc_draw_pull_down_menu('entry_state', $zones_array).'&nbsp;'.ENTRY_STATE_ERROR;
                              } else {
                                echo xtc_draw_input_field('entry_state', xtc_get_zone_name(isset($entry_country_id)?$entry_country_id:'',
                                                                                           isset($entry_zone_id)?$entry_zone_id:'',
                                                                                           isset($entry_state)?$entry_state:'')).'&nbsp;'.ENTRY_STATE_ERROR;
                              }
                            } else {
                              echo xtc_draw_input_field('entry_state', xtc_get_zone_name(isset($entry_country_id)?$entry_country_id:'',
                                                                                         isset($entry_zone_id)?$entry_zone_id:'',
                                                                                         isset($entry_state)?$entry_state:''));
                            }
                          } else {
                            echo xtc_draw_input_field('entry_state', xtc_get_zone_name(isset($entry_country_id)?$entry_country_id:'',
                                                                                       isset($entry_zone_id)?$entry_zone_id:'',
                                                                                       isset($entry_state)?$entry_state:''));
                          }
                          ?>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr>
                      <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_country_error) && $entry_country_error == true) {
                            echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries(xtc_get_country_name(STORE_COUNTRY)), isset($entry_country_id)?$entry_country_id:'').'&nbsp;'.ENTRY_COUNTRY_ERROR;
                          } else {
                            echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries(xtc_get_country_name(STORE_COUNTRY)), isset($entry_country_id)?$entry_country_id:'');
                          }
                        } else {
                          echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries(xtc_get_country_name(STORE_COUNTRY)), isset($entry_country_id)?$entry_country_id:'');
                        }
                        ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
              </tr>
              <tr>
                <td class="formArea">
                  <table border="0" cellspacing="2" cellpadding="2">
                    <tr>
                      <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_telephone_error) && $entry_telephone_error == true) {
                            echo xtc_draw_input_field('customers_telephone', isset($customers_telephone)?$customers_telephone:'').'&nbsp;'.ENTRY_TELEPHONE_NUMBER_ERROR;
                          } else {
                            echo xtc_draw_input_field('customers_telephone', isset($customers_telephone)?$customers_telephone:'');
                          }
                        } else {
                          echo xtc_draw_input_field('customers_telephone', isset($customers_telephone)?$customers_telephone:'');
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
                      <td class="main"><?php echo xtc_draw_input_field('customers_fax'); ?></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
              </tr>
              <tr>
                <td class="formArea">
                  <table border="0" cellspacing="2" cellpadding="2">
                    <tr>
                      <td class="main"><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
                      <td class="main">
                        <?php
                        if (isset($processed) && $processed == true) {
                          echo xtc_draw_hidden_field('status');
                        } else {
                          // BOF - Tomcraft - 2009-10-27 - Set default customers-group to default customers group
                          //  echo xtc_draw_pull_down_menu('status', $customers_statuses_array);
                          echo xtc_draw_pull_down_menu('status', $customers_statuses_array, DEFAULT_CUSTOMERS_STATUS_ID);
                          // EOF - Tomcraft - 2009-10-27 - Set default customers-group to default customers group
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_MAIL; ?></td>
                      <td class="main">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_mail_error) && $entry_mail_error == true) {
                            echo xtc_draw_radio_field('customers_mail', 'yes', true, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.YES.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_mail', 'no', false, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.NO.'&nbsp;'.ENTRY_MAIL_ERROR;
                          } else {
                            echo xtc_draw_radio_field('customers_mail', 'yes', true, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.YES.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_mail', 'no', false, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.NO;
                          }
                        } else {
                          echo xtc_draw_radio_field('customers_mail', 'yes', true, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.YES.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_mail', 'no', false, isset($customers_send_mail)?$customers_send_mail:'').'&nbsp;&nbsp;'.NO;
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_PAYMENT_UNALLOWED; ?></td>
                      <td class="main"><?php echo xtc_draw_input_field('payment_unallowed'); ?></td>
                    </tr>
                    <tr>
                      <td class="main"><?php echo ENTRY_SHIPPING_UNALLOWED; ?></td>
                      <td class="main"><?php echo xtc_draw_input_field('shipping_unallowed'); ?></td>
                    </tr>
                    <tr>
                      <td class="main" bgcolor="#FFCC33"><?php echo ENTRY_PASSWORD; ?></td>
                      <td class="main" bgcolor="#FFCC33">
                        <?php
                        if (isset($error) && $error == true) {
                          if (isset($entry_password_error) && $entry_password_error == true) {
                            echo xtc_draw_password_field('entry_password', isset($customers_password_encrypted)?$customers_password_encrypted:'').'&nbsp;'.ENTRY_PASSWORD_ERROR;
                          } else {
                            echo xtc_draw_password_field('entry_password', isset($customers_password_encrypted)?$customers_password_encrypted:'');
                          }
                        } else {
                          echo xtc_draw_password_field('entry_password', isset($customers_password_encrypted)?$customers_password_encrypted:'');
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="main" valign="top"><?php echo ENTRY_MAIL_COMMENTS; ?></td>
                      <td class="main"><?php echo xtc_draw_textarea_field('mail_comments', 'soft', '60', '5', isset($mail_comments)?$mail_comments:''); ?></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td align="right" class="main"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action'))) .'">' . BUTTON_CANCEL . '</a>'; ?></td>
              </tr>
            </form>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>