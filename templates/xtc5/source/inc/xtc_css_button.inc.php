<?php

function xtc_css_button($image, $alt, $parameters='', $submit) 
{
    $name           = $image;
    $buttonpath     = 'templates/'.CURRENT_TEMPLATE.'/buttons/' . $_SESSION['language'] . '/';
    $iconpath       = 'templates/'.CURRENT_TEMPLATE.'/buttons/cssbutton_ico/';
    $html           = '';
    $clear          = '';
    $customColor_0  = '';
    $customColor_1  = ''; // jedem Button andere Farben zuweisen z.B. im Array die Farben 'customColor_0' => '#ff0000', 'customColor_1' => '#0000ff', einsetzen.
    $buttonSize     = ' buttonSize';
    $textButton     = ' textButton';
    $imageButton    = ' imageButton';
    $gradient       = ' gradient_0'; // Button gradient
    $title          = xtc_parse_input_field_data($alt, array('"' => '&quot;'));
    
    if (xtc_not_null($parameters)) {
      $parameters = ' '.$parameters;
    }

    /* Buttons array */
    $buttons = array(
    'default'                       => array('Image' => '',                       'Text' => $alt,                           'icon' => '',                     'iconposition' => 'iconnone',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_add_address.gif'        => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_edit_adress.png', 'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_add_quick.gif'          => array('Image' => '',                       'Text' => IMAGE_BUTTON_IN_CART,           'icon' => 'icon_add_cart.png',    'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_admin.gif'              => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_admin.png',       'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_back.gif'               => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_back2.png',       'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_buy_now.gif'            => array('Image' => '',                       'Text' => IMAGE_BUTTON_IN_CART,           'icon' => 'icon_add_cart.png',    'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_change_address.gif'     => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_edit_adress.png', 'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_checkout.gif'           => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_checkout.png',    'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_confirm_order.gif'      => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_confirm.png',     'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_continue.gif'           => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_next2.png',       'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_continue_shopping.gif'  => array('Image' => '',                       'Text' => $alt,                           'icon' => '',                     'iconposition' => 'iconnone',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_delete.gif'             => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_delete2.png',     'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_download.gif'           => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_download.png',    'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_login.gif'              => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_login.png',       'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_logoff.gif'             => array('Image' => '',                       'Text' => $alt,                           'icon' => '',                     'iconposition' => 'iconnone',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_in_cart.gif'            => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_add_cart.png',    'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_login_newsletter.gif'   => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_checkout.png',    'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_print.gif'              => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_print.png',       'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_product_more.gif'       => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_product_more.png','iconposition' => 'iconleft',     'Size' => '1',    'color' => '1',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_quick_find.gif'         => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_search.png',      'iconposition' => 'icononly',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_redeem.gif'             => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_review.png',      'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_search.gif'             => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_search.png',      'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_send.gif'               => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_checkout.png',    'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_login_small.gif'        => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_login.png',       'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_update.gif'             => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_update.png',      'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'button_update_cart.gif'        => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_update.png',      'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'button_write_review.gif'       => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_write_review.png','iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'small_edit.gif'                => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_edit.png',        'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'small_delete.gif'              => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_delete2.png',     'iconposition' => 'iconright',    'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    'cart_del.gif'                  => array('Image' => $name,                    'Text' => $alt,                           'icon' => '',                     'iconposition' => 'iconnone',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'edit_product.gif'              => array('Image' => '',                       'Text' => $alt,                           'icon' => 'icon_admin.png',       'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => true  ),
    'print.gif'                     => array('Image' => '',                       'Text' => TEXT_PRINT,                     'icon' => 'icon_print.png',       'iconposition' => 'iconleft',     'Size' => '1',    'color' => '0',    'customColor_0' => '',    'customColor_1' => '',    'clear' => false ),
    );

    if ($buttons[$name]['customColor_0']) {
        $customColor_0 = ' style="background-color:'.$buttons[$name]['customColor_0'].'!important;"';
    }
    if ($buttons[$name]['customColor_1']) {
        $customColor_1 = ' style="background-color:'.$buttons[$name]['customColor_1'].'!important;"';
    }
    if (!array_key_exists($name, $buttons)) {$name = 'default';}
    if ($buttons[$name]['Image']) {
        $html .= '<span class="cssButton'.$imageButton.$buttonSize.$buttons[$name]['Size'].'"'.$parameters.'>';
        $html .= '<span>';
        $html .= '<img src="'.$buttonpath.$buttons[$name]['Image'].'" alt="'.$buttons[$name]['Text'].'" />';
        $html .= '</span>';
    }else {
    if ($buttons[$name]['color'] == '1') {// farben umkehren (hover-effekt)
        $html .= '<span class="cssButton color_1 '.$buttons[$name]['iconposition'].$textButton.$buttonSize.$buttons[$name]['Size'].'"'.$customColor_1.$parameters.'>';
        $html .= '<span class="background_hover color_0"'.$customColor_0.'>&nbsp;</span>';
    }else {
        $html .= '<span class="cssButton color_0 '.$buttons[$name]['iconposition'].$textButton.$buttonSize.$buttons[$name]['Size'].'"'.$customColor_0.$parameters.'>';
        $html .= '<span class="background_hover color_1"'.$customColor_1.'>&nbsp;</span>';
    }
        $html .= '<span class="animate_image'.$gradient.'">&nbsp;</span>';
    if ($buttons[$name]['iconposition'] != 'iconnone') {
        $html .= '<span class="buttonIcon"  title="'.$title.'" style="background-image: url('.$iconpath.$buttons[$name]['icon'].');">&nbsp;</span>';
    }
    if ($buttons[$name]['iconposition'] != 'icononly') {
        $html .= '<span class="buttonText" title="'.$title.'">'.$buttons[$name]['Text'].'</span>';
    }

    }

    if ($submit) {
        $html .= '<button';
        if ($submit <> true) {
            $html .= ' name="'.$submit.'"';
        }
        if ($submit == true || $submit == "submit") {
            $html .= ' type="submit"';
        }
        $html .= ' title="'.$title.'"'.$parameters.'>';
        $html .= $title.'</button>';
    }
    $html .= '</span>';

    if ($buttons[$name]['clear']) {
        $html .= '<span class="buttonclear">&nbsp;</span>';
    }

    return $html;
}

?>