<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // always assigned, so a mail template can place the variables without asking whether the
  // module is installed. Both variants follow AGB_HTML and AGB_TXT of the shop.
  $smarty->assign('GUARANTEE_NOTICE_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_TXT', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_TXT', '');

  // The module status first: these files ship with the module, the configuration does not.
  // The mail variables are assigned either way, only the module code stays unloaded.
  if (!defined('MODULE_GUARANTEE_LABELS_STATUS') || MODULE_GUARANTEE_LABELS_STATUS != 'true') {
    return;
  }

  require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');
  require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

  // An inactive module sends nothing, not even from a snapshot that is still there. Whether the
  // order holds goods at all is decided by the reader, so mail and order view agree.
  //
  // Only the wording and the link travel with the mail. The official notice is an svg, and mail
  // programs do not render svg reliably: an attachment the recipient cannot open informs worse
  // than the text does. See the roadmap for the full reasoning.
  $guarantee_labels_notice = guarantee_labels_order_active()
                           ? guarantee_labels_order_notice($order->info['order_id'])
                           : false;

  if ($guarantee_labels_notice !== false) {
    $guarantee_labels_url = encode_htmlspecialchars($guarantee_labels_notice['url']);

    $smarty->assign('GUARANTEE_NOTICE_HTML', $guarantee_labels_notice['text'].
                                             '<br /><a href="'.$guarantee_labels_url.'">'.$guarantee_labels_notice['link'].'</a>');

    $smarty->assign('GUARANTEE_NOTICE_TXT', decode_htmlentities($guarantee_labels_notice['text'])."\n".
                                            decode_htmlentities($guarantee_labels_notice['link']).': '.$guarantee_labels_notice['url']);

    // The heading comes from the snapshot like text and link, otherwise a repeated mail would
    // carry the historical wording under a current heading. An archive whose notice.json holds
    // no title falls back to the language file of the order, which the administration never
    // loads by itself. The mail templates hold no umlauts of their own, so it arrives as a
    // variable in both encodings.
    $guarantee_labels_title = $guarantee_labels_notice['title'];

    if ($guarantee_labels_title === '') {
      $guarantee_labels_texts = guarantee_labels_notice_texts($order->info['language']);
      $guarantee_labels_title = ($guarantee_labels_texts === false) ? '' : $guarantee_labels_texts['title'];
    }

    if ($guarantee_labels_title !== '') {
      $smarty->assign('GUARANTEE_NOTICE_HEADING_HTML', $guarantee_labels_title);
      $smarty->assign('GUARANTEE_NOTICE_HEADING_TXT', decode_htmlentities($guarantee_labels_title));
    }
  }

  // The archived guarantee conditions travel with the confirmation. Neither the notice nor a
  // GARAN label is attached or embedded, they stay text and link in the mail.
  $guarantee_labels_terms = guarantee_labels_order_active() ? guarantee_labels_order_terms($order->info['order_id']) : array();

  if (count($guarantee_labels_terms) > 0) {
    if (is_array($email_attachments)) {
      $email_attachments = array_merge($email_attachments, $guarantee_labels_terms);
    } else {
      // EMAIL_BILLING_ATTACHMENTS may carry one variant per language, as "de::a.pdf||en::b.pdf".
      // xtc_php_mail() resolves that only later, so appending to the raw value would push the
      // guarantee conditions into the last variant and every other language would lose them.
      if (strpos($email_attachments, '::') !== false) {
        // both, the same way xtc_php_mail() loads them
        require_once(DIR_FS_INC.'xtc_not_null.inc.php');
        require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');

        $email_attachments = parse_multi_language_value($email_attachments,
                                                        guarantee_labels_order_language_code($order->info['order_id']));
      }

      $email_attachments = trim($email_attachments.','.implode(',', $guarantee_labels_terms), ',');
    }
  }

  unset($guarantee_labels_notice, $guarantee_labels_terms, $guarantee_labels_url, $guarantee_labels_texts);
