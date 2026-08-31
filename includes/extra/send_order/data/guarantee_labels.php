<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');

  // always assigned, so a mail template can place the variables without asking whether the
  // module is installed. Both variants follow AGB_HTML and AGB_TXT of the shop.
  $smarty->assign('GUARANTEE_NOTICE_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_TXT', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_TXT', '');

  // the wording of the order, not the one the language files hold today
  $guarantee_labels_notice = guarantee_labels_order_notice($order->info['order_id']);

  if ($guarantee_labels_notice !== false) {
    $guarantee_labels_url = encode_htmlspecialchars($guarantee_labels_notice['url']);

    $smarty->assign('GUARANTEE_NOTICE_HTML', $guarantee_labels_notice['text'].
                                             '<br /><a href="'.$guarantee_labels_url.'">'.$guarantee_labels_notice['link'].'</a>');

    $smarty->assign('GUARANTEE_NOTICE_TXT', decode_htmlentities($guarantee_labels_notice['text'])."\n".
                                            decode_htmlentities($guarantee_labels_notice['link']).': '.$guarantee_labels_notice['url']);

    // The heading only labels the block and is therefore not part of the snapshot. It is read
    // for the language of the order: a repeated mail runs in the administration, which never
    // loads the storefront language files. The mail templates hold no umlauts of their own, so
    // it arrives as a variable in both encodings.
    $guarantee_labels_texts = guarantee_labels_notice_texts($order->info['language']);

    if ($guarantee_labels_texts !== false) {
      $smarty->assign('GUARANTEE_NOTICE_HEADING_HTML', $guarantee_labels_texts['title']);
      $smarty->assign('GUARANTEE_NOTICE_HEADING_TXT', decode_htmlentities($guarantee_labels_texts['title']));
    }
  }

  // The archived guarantee conditions travel with the confirmation. Neither the notice nor a
  // GARAN label is attached or embedded, they stay text and link in the mail.
  $guarantee_labels_terms = guarantee_labels_order_terms($order->info['order_id']);

  if (count($guarantee_labels_terms) > 0) {
    $email_attachments = is_array($email_attachments)
                       ? array_merge($email_attachments, $guarantee_labels_terms)
                       : trim($email_attachments.','.implode(',', $guarantee_labels_terms), ',');
  }

  unset($guarantee_labels_notice, $guarantee_labels_terms, $guarantee_labels_url, $guarantee_labels_texts);
