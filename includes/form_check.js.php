<?php
/* -----------------------------------------------------------------------------------------
   $Id: form_check.js.php 1296 2005-10-08 17:52:26Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(form_check.js.php,v 1.9 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (form_check.js.php,v 1.3 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

?>
<script type="text/javascript">
<!--//

  var form = "";
  var submitted = false;
  var error = false;
  var error_message = "";
  var selected;
  var submitter = null;

  function submitFunction() {
      submitter = 1;
  }

  function check_email(field_name_1, field_name_2, field_size, message_1, message_2) {
    if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var email_address = form.elements[field_name_1].value;
    var confirm_email_address = form.elements[field_name_2].value;

    if (email_address == '' || email_address.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (email_address != confirm_email_address) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    }
    }
  } 

  function check_input(field_name, field_size, message) {
    if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
      var field_value = form.elements[field_name].value;

      if (field_value == '' || field_value.length < field_size) {
        error_message = error_message + "* " + message + "\n";
        error = true;
      }
    }
  }

  function check_radio(field_name, message) {
    var isChecked = false;

    if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
      var radio = form.elements[field_name];

      for (var i=0; i<radio.length; i++) {
        if (radio[i].checked == true) {
          isChecked = true;
          break;
        }
      }

      if (isChecked == false) {
        error_message = error_message + "* " + message + "\n";
        error = true;
      }
    }
  }

  function check_select(field_name, field_default, message) {
    if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
      var field_value = form.elements[field_name].value;

      if (field_value == field_default) {
        error_message = error_message + "* " + message + "\n";
        error = true;
      }
    }
  }

  function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
    if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
      var password = form.elements[field_name_1].value;
      var confirmation = form.elements[field_name_2].value;

      if (password == '' || password.length < field_size) {
        error_message = error_message + "* " + message_1 + "\n";
        error = true;
      } else if (password != confirmation) {
        error_message = error_message + "* " + message_2 + "\n";
        error = true;
      }
    }
  }

  function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
    if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
      var password_current = form.elements[field_name_1].value;
      var password_new = form.elements[field_name_2].value;
      var password_confirmation = form.elements[field_name_3].value;

      if (password_current == '' || password_current.length < field_size) {
        error_message = error_message + "* " + message_1 + "\n";
        error = true;
      } else if (password_new == '' || password_new.length < field_size) {
        error_message = error_message + "* " + message_2 + "\n";
        error = true;
      } else if (password_new != password_confirmation) {
        error_message = error_message + "* " + message_3 + "\n";
        error = true;
      }
    }
  }

  function check_form(form_name) {
    if (submitted == true) {
      alert(unescape("<?php echo xtc_js_lang(JS_ERROR_SUBMITTED); ?>"));
      return false;
    }
  
    error = false;
    form = form_name;
    error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");

  <?php if (ACCOUNT_GENDER == 'true') { ?>
    if ($('input[name=gender]').prop("type") == 'radio') {
      <?php echo '  check_radio("gender", "' . xtc_js_lang(ENTRY_GENDER_ERROR) . '");' . "\n"; ?>
    } else {
      check_select("gender", '', "<?php echo xtc_js_lang(ENTRY_GENDER_ERROR); ?>");
    }
  <?php } ?>

    check_input("firstname", <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_FIRST_NAME_ERROR); ?>");
    check_input("lastname", <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_LAST_NAME_ERROR); ?>");

  <?php if (ACCOUNT_DOB == 'true') echo '  check_input("dob", ' . ENTRY_DOB_MIN_LENGTH . ', "' . xtc_js_lang(ENTRY_DATE_OF_BIRTH_ERROR) . '");' . "\n"; ?>

    check_email("email_address", "confirm_email_address", <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_EMAIL_ADDRESS_ERROR); ?>", "<?php echo xtc_js_lang(ENTRY_EMAIL_ERROR_NOT_MATCHING); ?>");
    check_input("street_address", <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_STREET_ADDRESS_ERROR); ?>");
    check_input("postcode", <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_POST_CODE_ERROR); ?>");
    check_input("city", <?php echo ENTRY_CITY_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_CITY_ERROR); ?>");

  <?php if (ACCOUNT_STATE == 'true') echo '  check_input("state", ' . ENTRY_STATE_MIN_LENGTH . ', "' . xtc_js_lang(ENTRY_STATE_ERROR) . '");' . "\n"; ?>

    check_select("country", "", "<?php echo xtc_js_lang(ENTRY_COUNTRY_ERROR); ?>");
  <?php if (ACCOUNT_TELEPHONE_OPTIONAL == 'false') { ?>
    check_input("telephone", <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_TELEPHONE_NUMBER_ERROR); ?>");
  <?php } ?>
    check_password("password", "confirmation", <?php echo ENTRY_PASSWORD_MIN_LENGTH; ?>, "<?php echo xtc_js_lang(ENTRY_PASSWORD_ERROR); ?>", "<?php echo xtc_js_lang(ENTRY_PASSWORD_ERROR_NOT_MATCHING); ?>");
    check_password_new("password_current", "password_new", "password_confirmation", <?php echo xtc_js_lang(ENTRY_PASSWORD_MIN_LENGTH); ?>, "<?php echo xtc_js_lang(ENTRY_PASSWORD_ERROR); ?>", "<?php echo xtc_js_lang(ENTRY_PASSWORD_NEW_ERROR); ?>", "<?php echo xtc_js_lang(ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING); ?>");

    if (error == true) {
      alert(unescape(error_message));
      return false;
    } else {
      submitted = true;
      return true;
    }
  }

  function check_form_review() {
    var error = 0;
    var error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");
    var review = document.getElementById("product_reviews_write").review.value;
    if (review.length < <?php echo REVIEW_TEXT_MIN_LENGTH; ?>) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_REVIEW_TEXT); ?>");
      error = 1;
    }
    var author = document.getElementById("product_reviews_write").author.value;
    if (author.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_REVIEW_AUTHOR); ?>");
      error = 1;
    }
    if (!((document.getElementById("product_reviews_write").rating[0].checked) || (document.getElementById("product_reviews_write").rating[1].checked) || (document.getElementById("product_reviews_write").rating[2].checked) || (document.getElementById("product_reviews_write").rating[3].checked) || (document.getElementById("product_reviews_write").rating[4].checked))) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_REVIEW_RATING); ?>");
      error = 1;
    }
    if (error == 1) {
      alert(error_message);
      return false;
    } else {
      return true;
    }
  }

  function check_form_search() {
    var error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");
    var error_found = false;
    var error_field;
    var keywords = document.getElementById("advanced_search").keywords.value;
    var pfrom = document.getElementById("advanced_search").pfrom.value;
    var pto = document.getElementById("advanced_search").pto.value;
    var pfrom_float;
    var pto_float;
    if ( (keywords == '' || keywords.length < 1) && (pfrom == '' || pfrom.length < 1) && (pto == '' || pto.length < 1) ) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_AT_LEAST_ONE_INPUT); ?>");
      error_field = document.getElementById("advanced_search").keywords;
      error_found = true;
    }
    if (pfrom.length > 0) {
      pfrom_float = parseFloat(pfrom);
      if (isNaN(pfrom_float)) {
        error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_FROM_MUST_BE_NUM); ?>");
        error_field = document.getElementById("advanced_search").pfrom;
        error_found = true;
      }
    } else {
      pfrom_float = 0;
    }
    if (pto.length > 0) {
      pto_float = parseFloat(pto);
      if (isNaN(pto_float)) {
        error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_MUST_BE_NUM); ?>");
        error_field = document.getElementById("advanced_search").pto;
        error_found = true;
      }
    } else {
      pto_float = 0;
    }
    if ( (pfrom.length > 0) && (pto.length > 0) ) {
      if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
        error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_LESS_THAN_PRICE_FROM); ?>");
        error_field = document.getElementById("advanced_search").pto;
        error_found = true;
      }
    }
    if (error_found == true) {
      alert(error_message);
      error_field.focus();
      return false;
    }
  }

  function check_form_optional(form_name) {
    var form = form_name;
    var firstname = form.elements['firstname'].value;
    var lastname = form.elements['lastname'].value;
    var street_address = form.elements['street_address'].value;
    if (firstname == '' && lastname == '' && street_address == '') {
      return true;
    } else {
      return check_form(form_name);
    }
  }

//-->
</script>