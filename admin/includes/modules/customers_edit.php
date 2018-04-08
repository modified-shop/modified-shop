<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );


          if (isset($_GET['edit']) && $_GET['edit'] != '') {
            $check = "a.address_book_id = '". (int) $_GET['edit']."'";
            $customers_default_address_id_checkbox = xtc_draw_checkbox_field('primary', 'on', false);
          } else {
            $check = "c.customers_default_address_id = a.address_book_id";
          }

          if (!isset($cInfo) || !is_object($cInfo)) {
            $customers_query = xtc_db_query("-- admin/customers.php
                                             SELECT c.customers_id,
                                                    c.customers_cid,
                                                    c.customers_vat_id,
                                                    c.customers_vat_id_status,
                                                    c.customers_status,
                                                    -- c.customers_gender, # web28 2012-01-06 - wrong use correctly a.entry_gender AS customers_gender
                                                    -- c.customers_firstname, # web28 2012-01-06 - wrong use correctly a.entry_firstname AS customers_firstname
                                                    -- c.customers_lastname, # web28 2012-01-06 - wrong use correctly a.entry_lastname AS customers_lastname
                                                    c.customers_dob,
                                                    c.customers_email_address,
                                                    c.customers_default_address_id,
                                                    c.customers_telephone,
                                                    c.customers_fax,
                                                    c.customers_newsletter,
                                                    c.member_flag,
                                                    c.payment_unallowed,
                                                    c.shipping_unallowed,
                                                    a.address_book_id,
                                                    a.entry_gender AS customers_gender,
                                                    a.entry_firstname AS customers_firstname,
                                                    a.entry_lastname AS customers_lastname,
                                                    a.entry_company,
                                                    a.entry_street_address,
                                                    a.entry_suburb,
                                                    a.entry_postcode,
                                                    a.entry_city,
                                                    a.entry_state,
                                                    a.entry_country_id,
                                                    a.entry_zone_id,
                                                    cgc.amount
                                               FROM ".TABLE_CUSTOMERS." c
                                          LEFT JOIN ".TABLE_ADDRESS_BOOK." a
                                                    ON ".$check."
                                                       AND a.customers_id = c.customers_id
                                          LEFT JOIN ".TABLE_COUPON_GV_CUSTOMER." cgc
                                                 ON c.customers_id = cgc.customer_id
                                              WHERE c.customers_id = '".(int)$_GET['cID']."'"
                                           );
            $customers = xtc_db_fetch_array($customers_query);
            if (xtc_db_num_rows($customers_query) != 0) {
              $cInfo = new objectInfo($customers);
            }
          }
          $newsletter_array = array (array ('id' => '1', 'text' => ENTRY_NEWSLETTER_YES), array ('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));

          require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
          $xtPrice = new xtcPrice(DEFAULT_CURRENCY,$cInfo->customers_status);

        ?>

      
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_customers.png'); ?></div>
      <div class="flt-l">
        <div class="pageHeading"><?php echo $cInfo->customers_lastname.' '.$cInfo->customers_firstname; ?></div>
        <div class="main pdg2"><?php echo BOX_HEADING_CUSTOMERS; ?></div>
      </div>
      <div class="clear"></div>
      <div class="div_box mrg5">
        <div class="customers-groups">
          <div class="flt-l"><?php if ($customers_statuses_id_array[$cInfo->customers_status]['csa_image'] != '') { echo xtc_image(DIR_WS_CATALOG.DIR_WS_ICONS . $customers_statuses_id_array[$cInfo->customers_status]['csa_image'], ''); } ?></div>
          <div class="main" style="margin:12px 0;"><b><?php echo HEADING_TITLE_STATUS  .':</b> ' . $customers_statuses_id_array[$cInfo->customers_status]['text'] ; ?></div>
        </div>
        <div class="clear"></div>
        <?php echo xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')) . 'action=update', 'post', 'onSubmit="return check_form();"') .
                   xtc_draw_hidden_field('customers_default_address_id', $cInfo->customers_default_address_id) .
                   xtc_draw_hidden_field('address_book_id', $cInfo->address_book_id) .
                   xtc_draw_hidden_field('customers_status', $cInfo->customers_status); ?>
        <div class="formAreaTitle"><span class="title"><?php echo CATEGORY_PERSONAL; ?></span></div>
        <div class="formAreaC">
          <table class="tableConfig borderall">
            <?php
              if (ACCOUNT_GENDER == 'true') {
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_GENDER; ?></td>
              <td class="dataTableConfig col-single-right">
              <?php
              if ($error == true) {
                if ($entry_gender_error == true) {
                  echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.FEMALE.'&nbsp;'.ENTRY_GENDER_ERROR;
                } else {
                  echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
                  echo xtc_draw_hidden_field('customers_gender', $cInfo->customers_gender);
                }
              } else {
                echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.FEMALE;
              }
              ?>
              </td>

            </tr>
            <?php
              }
            echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';
            ?>
              <td class="dataTableConfig col-left"><?php echo ENTRY_CID; ?></td>
              <td class="dataTableConfig col-single-right bg_notice">
                <?php
                echo xtc_draw_input_field('csID', $cInfo->customers_cid, 'maxlength="32"', false);
                ?>
              </td>

            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_FIRST_NAME; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                //BOF - DokuMan - 2010-11-01 - enhance eror-reporting on firstname
                if ($error == true) {
                  if ($entry_firstname_error == true) {
                    echo xtc_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"').'&nbsp;'.ENTRY_FIRST_NAME_ERROR;
                  } else {
                    echo $cInfo->customers_firstname.xtc_draw_hidden_field('customers_firstname', $cInfo->customers_firstname);
                  }
                } else {
                  echo xtc_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"', true);
                }
                //EOF - DokuMan - 2010-11-01 - enhance eror-reporting on firstname
                ?>
              </td>

            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_LAST_NAME; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_lastname_error == true) {
                    echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"').'&nbsp;'.ENTRY_LAST_NAME_ERROR;
                  } else {
                    echo $cInfo->customers_lastname.xtc_draw_hidden_field('customers_lastname', $cInfo->customers_lastname);
                  }
                } else {
                  echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"', true);
                }
                ?>
              </td>

            </tr>
            <?php
            if (ACCOUNT_DOB == 'true') {
              echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';
            ?>
              <td class="dataTableConfig col-left"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
              <td class="dataTableConfig col-single-right">
                 <?php
                if ($error == true) {
                  if ($entry_date_of_birth_error == true) {
                    echo xtc_draw_input_field('customers_dob', xtc_date_short($cInfo->customers_dob), 'maxlength="10"').'&nbsp;'.ENTRY_DATE_OF_BIRTH_ERROR;
                  } else {
                    echo xtc_date_short($cInfo->customers_dob).xtc_draw_hidden_field('customers_dob', xtc_date_short($cInfo->customers_dob));
                  }
                } else {
                  echo xtc_draw_input_field('customers_dob', xtc_date_short($cInfo->customers_dob), 'maxlength="10"', true);
                }
                ?>
              </td>

            </tr>
            <?php
            }
             echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';
            ?>
              <td class="dataTableConfig col-left"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_email_address_error == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');" maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR;
                  } elseif ($entry_email_address_check_error == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');" maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
                  } elseif ($entry_email_address_exists == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');" maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
                  } else {
                    echo $cInfo->customers_email_address.xtc_draw_hidden_field('customers_email_address', $cInfo->customers_email_address);
                  }
                } else {
                  echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');" maxlength="96"', true);
                }
                ?>
              </td>

            </tr>
          </table>
        </div>
        <?php
          if (ACCOUNT_COMPANY == 'true') {
        ?>


        <div class="formAreaTitle"><span class="title"><?php echo CATEGORY_COMPANY; ?></span></div>
        <div class="formAreaC">
          <table class="tableConfig borderall">
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_COMPANY; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_company_error == true) {
                    echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="64"').'&nbsp;'.ENTRY_COMPANY_ERROR;
                  } else {
                    echo $cInfo->entry_company.xtc_draw_hidden_field('entry_company', $cInfo->entry_company);
                  }
                } else {
                  echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="64"');
                }
                ?>
              </td>

            </tr>
            <?php
            if(ACCOUNT_COMPANY_VAT_CHECK == 'true'){
              // BOF - Dokuman - 2011-08-26 - show error code of VAT ID check FROM DB (only in 'edit' process, not in 'update')  //web28 - 2012-04-08 - and only when customers_vat_id is not empty
              if ($action == 'edit' && $cInfo->customers_vat_id != '') {
                // BOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
                switch ($cInfo->customers_vat_id_status) {
                  case '0' :
                    $entry_vat_error_text = TEXT_VAT_FALSE;
                    break;
                  case '1' :
                    $entry_vat_error_text = TEXT_VAT_TRUE;
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
                }
                // EOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
              }
              // EOF - Dokuman - 2011-08-26 - show error code of VAT ID check FROM DB (only in 'edit' process, not in 'update')
              echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';
              ?>
                <td class="dataTableConfig col-left"><?php echo ENTRY_VAT_ID; ?></td>
                <td class="dataTableConfig col-single-right">
                  <?php
                    // BOF - Dokuman - 2011-07-28 - display correct error code of VAT ID check
                    echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"').'&nbsp;'.$entry_vat_error_text;
                    /*
                    if ($error == true) {
                      if ($entry_vat_error == true) {
                        echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"').'&nbsp;'.$entry_vat_error_text;
                      } else {
                        echo $cInfo->customers_vat_id.xtc_draw_hidden_field('customers_vat_id');
                      }
                    } else {
                      echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"');
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
          </div>
        <?php
          }
        ?>

        <div class="formAreaTitle"><span class="title"><?php echo CATEGORY_ADDRESS; ?></span></div>
        <div class="formAreaC">
         <table class="tableConfig borderall">
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_STREET_ADDRESS; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_street_address_error == true) {
                    echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"').'&nbsp;'.ENTRY_STREET_ADDRESS_ERROR;
                  } else {
                    echo $cInfo->entry_street_address.xtc_draw_hidden_field('entry_street_address', $cInfo->entry_street_address);
                  }
                } else {
                  echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"', true);
                }
                ?>
              </td>

            </tr>
            <?php
              if (ACCOUNT_SUBURB == 'true') {
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_SUBURB; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_suburb_error == true) {
                    echo xtc_draw_input_field('suburb', $cInfo->entry_suburb, 'maxlength="32"').'&nbsp;'.ENTRY_SUBURB_ERROR;
                  } else {
                    echo $cInfo->entry_suburb.xtc_draw_hidden_field('entry_suburb', $cInfo->entry_suburb);
                  }
                } else {
                  echo xtc_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32"');
                }
                ?>
              </td>

            </tr>
            <?php
              }
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_POST_CODE; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_post_code_error == true) {
                    echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"').'&nbsp;'.ENTRY_POST_CODE_ERROR;
                  } else {
                    echo $cInfo->entry_postcode.xtc_draw_hidden_field('entry_postcode', $cInfo->entry_postcode);
                  }
                } else {
                  echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"', true);
                }
              ?>
              </td>

            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_CITY; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_city_error == true) {
                    echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"').'&nbsp;'.ENTRY_CITY_ERROR;
                  } else {
                    echo $cInfo->entry_city.xtc_draw_hidden_field('entry_city', $cInfo->entry_city);
                  }
                } else {
                  echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"', true);
                }
                ?>
              </td>

            </tr>
            
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_COUNTRY; ?></td>
              <td class="dataTableConfig col-single-right">
                <?php
                if ($error == true) {
                  if ($entry_country_error == true) {
                    echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries('',1), $cInfo->entry_country_id, 'style="width:250px"').'&nbsp;'.ENTRY_COUNTRY_ERROR; //Web28 - 2012-04-17 - NEW: show only active language
                  } else {
                    echo xtc_get_country_name($cInfo->entry_country_id).xtc_draw_hidden_field('entry_country_id', $cInfo->entry_country_id);
                  }
                } else {
                  echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries('',1), $cInfo->entry_country_id, 'style="width:250px"'); //Web28 - 2012-04-17 - NEW: show only active language
                }
                ?>
              </td>
            </tr>
            <?php
            if (ACCOUNT_STATE == 'true') {
            ?>
            <tr id="states">
              <td class="dataTableConfig col-left"><?php echo ENTRY_STATE; ?></td>
              <td class="dataTableConfig col-single-right" id="states_container">
                <?php
                $entry_state = xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
                if ($error == true) {
                  if ($entry_state_error == true) {
                    if ($entry_state_has_zones == true) {
                      $zones_array = array ();
                      $zones_query = xtc_db_query("SELECT zone_name FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($cInfo->entry_country_id)."' order by zone_name");
                      while ($zones_values = xtc_db_fetch_array($zones_query)) {
                        $zones_array[] = array ('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
                      }
                      echo xtc_draw_pull_down_menu('entry_state', $zones_array ,'', 'style="width:250px"').'&nbsp;'.ENTRY_STATE_ERROR;
                    } else {
                      echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)).'&nbsp;'.ENTRY_STATE_ERROR;
                    }
                  } else {
                    echo $entry_state.xtc_draw_hidden_field('entry_zone_id', $cInfo->entry_zone_id).xtc_draw_hidden_field('entry_state', $cInfo->entry_state);
                  }
                } else {
                  echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state));
                }
                ?>
              </td>
            </tr>
            <?php
            }
            ?>

          </table>
        </div>
        <?php
        if ($cInfo->customers_default_address_id == $cInfo->address_book_id) {
        ?>

        <div class="formAreaTitle"><span class="title"><?php echo CATEGORY_CONTACT; ?></span></div>

        <?php
        }
        $style = ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? ' style="display:none;"' : '';
        ?>
        <div class="formAreaC"<?php $style;?>>
          <table class="tableConfig borderall">
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
              <td class="dataTableConfig col-single-right">
              <?php
                if ($error == true) {
                  if ($entry_telephone_error == true) {
                    echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"').'&nbsp;'.ENTRY_TELEPHONE_NUMBER_ERROR;
                  } else {
                    echo $cInfo->customers_telephone.xtc_draw_hidden_field('customers_telephone', $cInfo->customers_telephone);
                  }
                } else {
                  echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"', (ACCOUNT_TELEPHONE_OPTIONAL == 'false'));
                }
              ?>
              </td>

            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_FAX_NUMBER; ?></td>
              <td class="dataTableConfig col-single-right">
              <?php
                if ($processed == true) {
                  echo $cInfo->customers_fax.xtc_draw_hidden_field('customers_fax', $cInfo->customers_fax);
                } else {
                  echo xtc_draw_input_field('customers_fax', $cInfo->customers_fax, 'maxlength="32"');
                }
              ?>
              </td>

            </tr>
          </table>
        </div>
        <?php
        if ($cInfo->customers_default_address_id == $cInfo->address_book_id) {
        ?>

        <div class="formAreaTitle"><span class="title"><?php echo CATEGORY_OPTIONS; ?></span></div>
        <div class="formAreaC">
          <table class="tableConfig borderall">
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_PAYMENT_UNALLOWED; ?></td>
              <td class="dataTableConfig col-single-right">
              <?php
                echo xtc_cfg_checkbox_unallowed_module('payment', 'payment_unallowed', $cInfo->payment_unallowed);
              ?>
              </td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_SHIPPING_UNALLOWED; ?></td>
              <td class="dataTableConfig col-single-right">
              <?php
                echo xtc_cfg_checkbox_unallowed_module('shipping', 'shipping_unallowed', $cInfo->shipping_unallowed);
              ?>
              </td>
           </tr>
           <tr>
              <td class="dataTableConfig col-left"><?php echo ENTRY_NEW_PASSWORD; ?></td>
              <td class="dataTableConfig col-single-right bg_notice">
              <?php
                if ($error == true) {
                  if ($entry_password_error == true) {
                    echo xtc_draw_password_field('entry_password', $cInfo->customers_password, false, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');"').'&nbsp;'.ENTRY_PASSWORD_ERROR;
                  } else {
                    echo xtc_draw_password_field('entry_password', $cInfo->customers_password, false, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');"');
                  }
                } else {
                  echo xtc_draw_password_field('entry_password', '', false, 'autocomplete="off" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');" onblur="this.setAttribute(\'readonly\', \'readonly\');"');
                }
                ?>
              </td>
           </tr>
           <?php
           if (ACTIVATE_GIFT_SYSTEM=='true') {
           ?>
           <tr>
            <td class="dataTableConfig col-left"><?php echo TABLE_HEADING_AMOUNT; ?></td>
            <td class="dataTableConfig col-single-right">
            <?php  echo $xtPrice->xtcFormat($cInfo->amount, true);
              /*
              if ($processed == true) {
                echo $cInfo->amount.xtc_draw_hidden_field('amount', $cInfo->amount);
              } else {
                echo xtc_draw_input_field('amount', $cInfo->amount);
              }
              */
              ?>
            </td>
           <tr>
           <?php
           }
           ?>
           <tr>
             <?php
             include(DIR_WS_MODULES . FILENAME_CUSTOMER_MEMO);
             ?>
           </tr>
          </table>
        </div>
        <?php
        }
        ?>

        <div class="main mrg5"><input type="submit" class="button" onclick="this.blur();" value="<?php echo BUTTON_UPDATE; ?>"><?php echo ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action', 'edit'))) .'">' . BUTTON_CANCEL . '</a>'; ?></div>

      </form>
    </div>
    
    <?php require(DIR_WS_INCLUDES . 'javascript/jquery.entry_state.js.php'); ?>                                                                             