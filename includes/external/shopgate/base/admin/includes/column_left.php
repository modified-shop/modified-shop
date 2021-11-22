<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */

if (empty($_SESSION['customer_id'])) {
    return;
}

if (MODULE_PAYMENT_SHOPGATE_STATUS == 'True'
    && $admin_access['shopgate'] == 1
) {

    // determine configuration language: $_GET > $_SESSION > global
    $sg_language_get = (!empty($_GET['sg_language']) ? '&sg_language='
        . $_GET['sg_language'] : '');
    $displayCssClass = 'menuBoxContentLink';
    $linkNamePrefix  = ' -';

    if (defined('NEW_ADMIN_STYLE') && defined('PROJECT_MAJOR_VERSION')) {
        $surroundingHtml = array(
            'start' => '<li>' .
                '<a class="menuBoxContentLinkSub" href="#">-' . BOX_SHOPGATE
                . '</a>' .
                '<ul>',
            'end'   => '</ul></li>',
        );
    } else {
        $surroundingHtml = array(
            'start' => '<li>' .
                '<div class="dataTableHeadingContent"><strong>' . BOX_SHOPGATE
                . '</strong></div>' .
                '<ul>',
            'end'   => '</ul></li>',
        );
    }
    $surroundingTags = array(
        'start' => '<li>',
        'end'   => '</li>',
    );
    $hrefIdList      = array(
        'basic'    => '',
        'merchant' => '',
    );

    echo(
        $surroundingHtml['start'] .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=info{$sg_language_get}", '',
            'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix
        . BOX_SHOPGATE_INFO . '</a>' .
        $surroundingTags['end']
        .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=help{$sg_language_get}", '',
            'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix
        . BOX_SHOPGATE_HELP . '</a>' .
        $surroundingTags['end']
        .
        $surroundingTags['start'] .
        '<a ' . $hrefIdList['basic'] . 'href="' . xtc_href_link(
            FILENAME_SHOPGATE . "?sg_option=config{$sg_language_get}", '',
            'NONSSL'
        ) . '" class="' . $displayCssClass . '">' . $linkNamePrefix
        . BOX_SHOPGATE_CONFIG . '</a>' .
        $surroundingTags['end'] .
        $surroundingHtml['end']
    );
}
