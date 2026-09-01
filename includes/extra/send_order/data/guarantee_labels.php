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
  require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

  // always assigned, so a mail template can place the variables without asking whether the
  // module is installed. Both variants follow AGB_HTML and AGB_TXT of the shop.
  $smarty->assign('GUARANTEE_NOTICE_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_TXT', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_HTML', '');
  $smarty->assign('GUARANTEE_NOTICE_HEADING_TXT', '');

  // An inactive module sends nothing, not even from a snapshot that is still there. A manually
  // created order carries its notice from the moment it is created, so whether it holds goods
  // at all is decided here and not when the snapshot was written.
  $guarantee_labels_notice = (guarantee_labels_active() && guarantee_labels_order_physical($order->info['order_id']))
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
  $guarantee_labels_terms = guarantee_labels_active() ? guarantee_labels_order_terms($order->info['order_id']) : array();

  if (count($guarantee_labels_terms) > 0) {
    $email_attachments = is_array($email_attachments)
                       ? array_merge($email_attachments, $guarantee_labels_terms)
                       : trim($email_attachments.','.implode(',', $guarantee_labels_terms), ',');
  }

  unset($guarantee_labels_notice, $guarantee_labels_terms, $guarantee_labels_url, $guarantee_labels_texts);
