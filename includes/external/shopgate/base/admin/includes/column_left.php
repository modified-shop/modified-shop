<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

if (empty($_SESSION['customer_id'])) {
    return;
}

if (MODULE_PAYMENT_SHOPGATE_STATUS == 'True' && $admin_access['shopgate'] == 1) {
    
    // determine configuration language: $_GET > $_SESSION > global
    $sg_language_get = (!empty($_GET['sg_language']) ? '&sg_language=' . $_GET['sg_language'] : '');
    $displayCssClass = 'menuBoxContentLink';
    $linkNamePrefix  = ' -';
    
    if (defined('NEW_ADMIN_STYLE') && defined('PROJECT_MAJOR_VERSION')) {
        $surroundingHtml = array(
            'start' => '<li>' .
                '<a class="menuBoxContentLinkSub" href="#">-' . BOX_SHOPGATE . '</a>' .
                '<ul>',
            'end'   => '</ul></li>',
        );
    } else {
        $surroundingHtml = array(
            'start' => '<li>' .
                '<div class="dataTableHeadingContent"><strong>' . BOX_SHOPGATE . '</strong></div>' .
                '<ul>',
            'end'   => '</ul></li>',
        );
    }
    $surroundingTags = array(
        'start' => '<li>',
        'end'   => '</li>',
    );
    $hrefIdList      = array(
        'basic' => '',
        'merchant' => '',
    );
    
    
    echo(
        $surroundingHtml['start'] .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=info{$sg_language_get}", '', 'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix . BOX_SHOPGATE_INFO . '</a>' .
        $surroundingTags['end']
        .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=help{$sg_language_get}", '', 'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix . BOX_SHOPGATE_HELP . '</a>' .
        $surroundingTags['end']
        .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=config{$sg_language_get}", '', 'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix . BOX_SHOPGATE_CONFIG . '</a>' .
        $surroundingTags['end'] .
        $surroundingHtml['end']
    );
    
}


