<?php
if (defined('CURRENT_TEMPLATE') === false) define('CURRENT_TEMPLATE', 'xtc5');

return array(
    'shop-system' => 'modified-shop',

    'template'    => array(
        'giropay'             => array(
            'image-buttons' => true,
            'btn-back'      => array(
                'image'  => 'templates/' . CURRENT_TEMPLATE . '/buttons/german/button_back.gif',
                'text'   => 'Andere Zahlart w&auml;hlen',
                'height' => '24px',
                'width'  => '124px',
            ),
            'btn-continue'  => array(
                'image'  => 'templates/' . CURRENT_TEMPLATE . '/buttons/german/button_continue.gif',
                'text'   => 'Weiter zu Giropay',
                'height' => '24px',
                'width'  => '124px',
            ),
        ),
        'waiting-for-approve' => array(
            'img-loading'    => DIR_WS_IMAGES . 'loadingAnimation.gif',
            'img-loading-ok' => DIR_WS_IMAGES . 'icons/arrow_accepted.jpg',
        ),
    ),
);