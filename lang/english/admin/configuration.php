<?php
/* -----------------------------------------------------------------------------------------
   $Id: configuration.php 3130 2012-06-28 11:17:12Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.8 2002/01/04); www.oscommerce.com
   (c) 2003 nextcommerce (configuration.php,v 1.16 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


define('TABLE_HEADING_CONFIGURATION_TITLE', 'Title');
define('TABLE_HEADING_CONFIGURATION_VALUE', 'Value');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');

// language definitions for config
define('STORE_NAME_TITLE' , 'Store Name');
define('STORE_NAME_DESC' , 'The name of this store');
define('STORE_OWNER_TITLE' , 'Store Owner');
define('STORE_OWNER_DESC' , 'The name of the store owner');
define('STORE_OWNER_EMAIL_ADDRESS_TITLE' , 'E-Mail Address');
define('STORE_OWNER_EMAIL_ADDRESS_DESC' , 'The e-mail address of the store owner');

define('EMAIL_FROM_TITLE' , 'E-Mail From');
define('EMAIL_FROM_DESC' , 'The e-mail address used to send e-mails.');

define('STORE_COUNTRY_TITLE' , 'Country');
define('STORE_COUNTRY_DESC' , 'The country my store is located in <br /><br /><b>Note: Please remember to update the store district.</b>');
define('STORE_ZONE_TITLE' , 'District');
define('STORE_ZONE_DESC' , 'The district my store is located in.');

define('EXPECTED_PRODUCTS_SORT_TITLE' , 'Expected Products Sort Order');
define('EXPECTED_PRODUCTS_SORT_DESC' , 'This is the sort order used in the expected products box.');
define('EXPECTED_PRODUCTS_FIELD_TITLE' , 'Expexted Products Sort Field');
define('EXPECTED_PRODUCTS_FIELD_DESC' , 'The column to sort by in the expected products box.');

define('USE_DEFAULT_LANGUAGE_CURRENCY_TITLE' , 'Switch to language specific currency');
define('USE_DEFAULT_LANGUAGE_CURRENCY_DESC' , 'Automatically switch to language specific currency when language is changed.');

define('SEND_EXTRA_ORDER_EMAILS_TO_TITLE' , 'Send Copies of Order E-Mails to:');
define('SEND_EXTRA_ORDER_EMAILS_TO_DESC' , 'Send copies of order e-mails to the following e-mail addresses, like: Name1 &lt;e-mail@address1&gt;, Name2 &lt;e-mail@address2&gt;');

define('SEARCH_ENGINE_FRIENDLY_URLS_TITLE' , 'Use Search-Engine Safe URLs?');
define('SEARCH_ENGINE_FRIENDLY_URLS_DESC' , 'Use search-engine friendly URLs for all site links.<br /><br /><strong>For search-engine optimized URLs, the file _.htaccess in the root directory has to be activated, i.e. renamed to .htaccess! Moreover, your webserver has to support the <a href="http://www.modrewrite.com/" target="_blank">mod_rewrite</a> module!</strong> (Please ask your webhoster if you are unsure how to check that.)');

define('DISPLAY_CART_TITLE' , 'Display Cart After Adding a Product?');
define('DISPLAY_CART_DESC' , 'Display the shopping cart after adding a product or return back to product?');

define('ALLOW_GUEST_TO_TELL_A_FRIEND_TITLE' , 'Allow Guests To Tell a Friend?');
define('ALLOW_GUEST_TO_TELL_A_FRIEND_DESC' , 'Allow guests to tell a friend about a product?');

define('ADVANCED_SEARCH_DEFAULT_OPERATOR_TITLE' , 'Default Search Operator');
define('ADVANCED_SEARCH_DEFAULT_OPERATOR_DESC' , 'Default search operators.');

define('STORE_NAME_ADDRESS_TITLE' , 'Store Address and Phone');
define('STORE_NAME_ADDRESS_DESC' , 'Store details used for display and printing');

define('SHOW_COUNTS_TITLE' , 'Display Number of Products after Category Name');
define('SHOW_COUNTS_DESC' , 'Display number of products after each category name, counting products recursively');

define('DISPLAY_PRICE_WITH_TAX_TITLE' , 'Display Prices with Tax');
define('DISPLAY_PRICE_WITH_TAX_DESC' , 'Display prices with tax included (true) or add the tax at the end (false)');

define('DEFAULT_CUSTOMERS_STATUS_ID_ADMIN_TITLE' , 'Customer Status of Administrators');
define('DEFAULT_CUSTOMERS_STATUS_ID_ADMIN_DESC' , 'Choose the default customer status for administrators');
define('DEFAULT_CUSTOMERS_STATUS_ID_GUEST_TITLE' , 'Customer Status of Guests');
define('DEFAULT_CUSTOMERS_STATUS_ID_GUEST_DESC' , 'Choose the default customer status for guest accounts');
define('DEFAULT_CUSTOMERS_STATUS_ID_TITLE' , 'Customer Status of New Customers');
define('DEFAULT_CUSTOMERS_STATUS_ID_DESC' , 'Choose the default customer status for a new customer');

define('ALLOW_ADD_TO_CART_TITLE' , 'Allow add to cart');
define('ALLOW_ADD_TO_CART_DESC' , 'Allow customers to add products into cart even if group setting "show prices" is disabled');
define('ALLOW_DISCOUNT_ON_PRODUCTS_ATTRIBUTES_TITLE' , 'Allow discount on products attribute?');
define('ALLOW_DISCOUNT_ON_PRODUCTS_ATTRIBUTES_DESC' , 'Allow customers to get discount on attribute price (if main product is not a "specials" product)');
define('CURRENT_TEMPLATE_TITLE' , 'Template Set (Theme)');
define('CURRENT_TEMPLATE_DESC' , 'Choose a template set (theme). The theme must have been saved before in the following folder: www.Your-Domain.com/templates/');
define('CC_KEYCHAIN_TITLE','CC String');
define('CC_KEYCHAIN_DESC','String to encrypt CC number (please change!)');

define('ENTRY_FIRST_NAME_MIN_LENGTH_TITLE' , 'First Name');
define('ENTRY_FIRST_NAME_MIN_LENGTH_DESC' , 'Minimum length of first name');
define('ENTRY_LAST_NAME_MIN_LENGTH_TITLE' , 'Last Name');
define('ENTRY_LAST_NAME_MIN_LENGTH_DESC' , 'Minimum length of last name');
define('ENTRY_DOB_MIN_LENGTH_TITLE' , 'Date of Birth');
define('ENTRY_DOB_MIN_LENGTH_DESC' , 'Minimum length of date of birth');
define('ENTRY_EMAIL_ADDRESS_MIN_LENGTH_TITLE' , 'E-Mail Address');
define('ENTRY_EMAIL_ADDRESS_MIN_LENGTH_DESC' , 'Minimum length of e-mail address');
define('ENTRY_STREET_ADDRESS_MIN_LENGTH_TITLE' , 'Street Address');
define('ENTRY_STREET_ADDRESS_MIN_LENGTH_DESC' , 'Minimum length of street address');
define('ENTRY_COMPANY_MIN_LENGTH_TITLE' , 'Company');
define('ENTRY_COMPANY_MIN_LENGTH_DESC' , 'Minimum length of company name');
define('ENTRY_POSTCODE_MIN_LENGTH_TITLE' , 'Postcode');
define('ENTRY_POSTCODE_MIN_LENGTH_DESC' , 'Minimum length of postcode');
define('ENTRY_CITY_MIN_LENGTH_TITLE' , 'City');
define('ENTRY_CITY_MIN_LENGTH_DESC' , 'Minimum length of city');
define('ENTRY_STATE_MIN_LENGTH_TITLE' , 'State');
define('ENTRY_STATE_MIN_LENGTH_DESC' , 'Minimum length of state');
define('ENTRY_TELEPHONE_MIN_LENGTH_TITLE' , 'Telephone Number');
define('ENTRY_TELEPHONE_MIN_LENGTH_DESC' , 'Minimum length of telephone number');
define('ENTRY_PASSWORD_MIN_LENGTH_TITLE' , 'Password');
define('ENTRY_PASSWORD_MIN_LENGTH_DESC' , 'Minimum length of password');

define('CC_OWNER_MIN_LENGTH_TITLE' , 'Credit Card Owner Name');
define('CC_OWNER_MIN_LENGTH_DESC' , 'Minimum length of credit card owner name');
define('CC_NUMBER_MIN_LENGTH_TITLE' , 'Credit Card Number');
define('CC_NUMBER_MIN_LENGTH_DESC' , 'Minimum length of credit card number');

define('REVIEW_TEXT_MIN_LENGTH_TITLE' , 'Reviews');
define('REVIEW_TEXT_MIN_LENGTH_DESC' , 'Minimum length of review text');

define('MIN_DISPLAY_BESTSELLERS_TITLE' , 'Best Sellers');
define('MIN_DISPLAY_BESTSELLERS_DESC' , 'Minimum number of best sellers to display');
define('MIN_DISPLAY_ALSO_PURCHASED_TITLE' , 'Also Purchased');
define('MIN_DISPLAY_ALSO_PURCHASED_DESC' , 'Minimum number of products to display in the "This Customer Also Purchased" box');

define('MAX_ADDRESS_BOOK_ENTRIES_TITLE' , 'Address Book Entries');
define('MAX_ADDRESS_BOOK_ENTRIES_DESC' , 'Maximum address book entries a customer is allowed to have');
define('MAX_DISPLAY_SEARCH_RESULTS_TITLE' , 'Search Results');
define('MAX_DISPLAY_SEARCH_RESULTS_DESC' , 'Amount of products to list');
define('MAX_DISPLAY_PAGE_LINKS_TITLE' , 'Page Links');
define('MAX_DISPLAY_PAGE_LINKS_DESC' , 'Number of "number" links use for page-sets');
define('MAX_DISPLAY_SPECIAL_PRODUCTS_TITLE' , 'Specials');
define('MAX_DISPLAY_SPECIAL_PRODUCTS_DESC' , 'Maximum number of products to display on special offer');
define('MAX_DISPLAY_NEW_PRODUCTS_TITLE' , 'New Products Module');
define('MAX_DISPLAY_NEW_PRODUCTS_DESC' , 'Maximum number of new products to display in a category');
define('MAX_DISPLAY_UPCOMING_PRODUCTS_TITLE' , 'Upcoming Products');
define('MAX_DISPLAY_UPCOMING_PRODUCTS_DESC' , 'Maximum number of upcoming products to display');
define('MAX_DISPLAY_MANUFACTURERS_IN_A_LIST_TITLE' , 'Manufacturers List');
define('MAX_DISPLAY_MANUFACTURERS_IN_A_LIST_DESC' , 'Used in manufacturers box; when the number of manufacturers exceeds this number, a drop-down list will be displayed instead of the default list');
define('MAX_MANUFACTURERS_LIST_TITLE' , 'Manufacturers Select Size');
define('MAX_MANUFACTURERS_LIST_DESC' , 'Used in manufacturers box; when this value is "1" the classic drop-down list will be used for the manufacturers box. Otherwise, a list-box with the specified number of rows will be displayed.');
define('MAX_DISPLAY_MANUFACTURER_NAME_LEN_TITLE' , 'Length of Manufacturers Name');
define('MAX_DISPLAY_MANUFACTURER_NAME_LEN_DESC' , 'Used in manufacturers box; maximum length of manufacturers name to display');
define('MAX_DISPLAY_NEW_REVIEWS_TITLE' , 'New Reviews');
define('MAX_DISPLAY_NEW_REVIEWS_DESC' , 'Maximum number of new reviews to display');
define('MAX_RANDOM_SELECT_REVIEWS_TITLE' , 'Selection of Random Reviews');
define('MAX_RANDOM_SELECT_REVIEWS_DESC' , 'How many records to select from to choose one random product review');
define('MAX_RANDOM_SELECT_NEW_TITLE' , 'Selection of Random New Products');
define('MAX_RANDOM_SELECT_NEW_DESC' , 'How many records to select from to choose one random new product to display');
define('MAX_RANDOM_SELECT_SPECIALS_TITLE' , 'Selection of Products on Special');
define('MAX_RANDOM_SELECT_SPECIALS_DESC' , 'How many records to select from to choose one random product special to display');
define('MAX_DISPLAY_CATEGORIES_PER_ROW_TITLE' , 'Categories To List Per Row');
define('MAX_DISPLAY_CATEGORIES_PER_ROW_DESC' , 'How many categories to list per row');
define('MAX_DISPLAY_PRODUCTS_NEW_TITLE' , 'New Products Listing');
define('MAX_DISPLAY_PRODUCTS_NEW_DESC' , 'Maximum number of new products to display in new products page');
define('MAX_DISPLAY_BESTSELLERS_TITLE' , 'Best Sellers');
define('MAX_DISPLAY_BESTSELLERS_DESC' , 'Maximum number of best sellers to display');
define('MAX_DISPLAY_ALSO_PURCHASED_TITLE' , 'Also Purchased');
define('MAX_DISPLAY_ALSO_PURCHASED_DESC' , 'Maximum number of products to display in the "This Customer Also Purchased" box');
define('MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_TITLE' , 'Customer Order History Box');
define('MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_DESC' , 'Maximum number of products to display in the customer order history box');
define('MAX_DISPLAY_ORDER_HISTORY_TITLE' , 'Order History');
define('MAX_DISPLAY_ORDER_HISTORY_DESC' , 'Maximum number of orders to display in the order history page');
define('MAX_PRODUCTS_QTY_TITLE', 'Maximum Quantity');
define('MAX_PRODUCTS_QTY_DESC', 'Maximum quantity per product in cart');
define('MAX_DISPLAY_NEW_PRODUCTS_DAYS_TITLE' , 'Maximum Age (days) for New Products');
define('MAX_DISPLAY_NEW_PRODUCTS_DAYS_DESC' , 'Maximum age (in days) for products to be displayed as "new products"');

define('PRODUCT_IMAGE_THUMBNAIL_WIDTH_TITLE' , 'Width of Product Thumbnails');
define('PRODUCT_IMAGE_THUMBNAIL_WIDTH_DESC' , 'Maximum width of product thumbnails (in pixels) (Standard: 120). For larger values possibly "productPreviewImage" is adjusted in the templates stylesheet.css file.');
define('PRODUCT_IMAGE_THUMBNAIL_HEIGHT_TITLE' , 'Height of Product Thumbnails');
define('PRODUCT_IMAGE_THUMBNAIL_HEIGHT_DESC' , 'Maximum height of product thumbnails (in pixels) (Standard: 80).');

define('PRODUCT_IMAGE_INFO_WIDTH_TITLE' , 'Width of Product Info Images');
define('PRODUCT_IMAGE_INFO_WIDTH_DESC' , 'Maximum width of product info images (in pixels) (Standard: 200).');
define('PRODUCT_IMAGE_INFO_HEIGHT_TITLE' , 'Height of Product Info Images');
define('PRODUCT_IMAGE_INFO_HEIGHT_DESC' , 'Maximum height of product info images (in pixels) (Standard: 160).');

define('PRODUCT_IMAGE_POPUP_WIDTH_TITLE' , 'Width of Popup Images');
define('PRODUCT_IMAGE_POPUP_WIDTH_DESC' , 'Maximum width of popup images (in pixels) (Standard: 800).');
define('PRODUCT_IMAGE_POPUP_HEIGHT_TITLE' , 'Height of Popup Images');
define('PRODUCT_IMAGE_POPUP_HEIGHT_DESC' , 'Maximum height of popup images (in pixels) (Standard: 640).');

define('SMALL_IMAGE_WIDTH_TITLE' , 'Small Image Width');
define('SMALL_IMAGE_WIDTH_DESC' , 'Width of small images (in pixels)');
define('SMALL_IMAGE_HEIGHT_TITLE' , 'Small Image Height');
define('SMALL_IMAGE_HEIGHT_DESC' , 'Height of small images (in pixels)');

define('HEADING_IMAGE_WIDTH_TITLE' , 'Heading Image Width');
define('HEADING_IMAGE_WIDTH_DESC' , 'Width of heading images (in pixels)');
define('HEADING_IMAGE_HEIGHT_TITLE' , 'Heading Image Height');
define('HEADING_IMAGE_HEIGHT_DESC' , 'Height of heading images (in pixels)');

define('SUBCATEGORY_IMAGE_WIDTH_TITLE' , 'Subcategory Image Width');
define('SUBCATEGORY_IMAGE_WIDTH_DESC' , 'Width of subcategory images (in pixels)');
define('SUBCATEGORY_IMAGE_HEIGHT_TITLE' , 'Subcategory Image Height');
define('SUBCATEGORY_IMAGE_HEIGHT_DESC' , 'Height of subcategory images (in pixels)');

define('CONFIG_CALCULATE_IMAGE_SIZE_TITLE' , 'Calculate Image Size');
define('CONFIG_CALCULATE_IMAGE_SIZE_DESC' , 'Calculate the size of images?');

define('IMAGE_REQUIRED_TITLE' , 'Image Required');
define('IMAGE_REQUIRED_DESC' , 'Enable to display broken images. Good for development.');

define('MO_PICS_TITLE','Number of product images');
define('MO_PICS_DESC','if this number is set > 0 , you will be able to upload/display more images per product');

//This is for the Images showing your products for preview. All the small stuff.

define('PRODUCT_IMAGE_THUMBNAIL_BEVEL_TITLE' , 'Product Thumbnails:Bevel<br /><img src="images/config_bevel.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_BEVEL_DESC' , 'Product Thumbnails:Bevel<br /><br />Default-values: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Usage:<br />(edge width,hex light colour,hex dark colour)');

define('PRODUCT_IMAGE_THUMBNAIL_GREYSCALE_TITLE' , 'Product Thumbnails:Greyscale<br /><img src="images/config_greyscale.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_GREYSCALE_DESC' , 'Product Thumbnails:Greyscale<br /><br />Default-values: (32,22,22)<br /><br />basic black n white<br />Usage:<br />(int red,int green,int blue)');

define('PRODUCT_IMAGE_THUMBNAIL_ELLIPSE_TITLE' , 'Product Thumbnails:Ellipse<br /><img src="images/config_eclipse.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_ELLIPSE_DESC' , 'Product Thumbnails:Ellipse<br /><br />Default-values: (FFFFFF)<br /><br />ellipse on bg colour<br />Usage:<br />(hex background colour)');

define('PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES_TITLE' , 'Product Thumbnails:Round-edges<br /><img src="images/config_edge.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES_DESC' , 'Product Thumbnails:Round-edges<br /><br />Default-values: (5,FFFFFF,3)<br /><br />corner trimming<br />Usage:<br />(edge_radius,background colour,anti-alias width)');

define('PRODUCT_IMAGE_THUMBNAIL_MERGE_TITLE' , 'Product Thumbnails:Merge<br /><img src="images/config_merge.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_MERGE_DESC' , 'Product Thumbnails:Merge<br /><br />Default-values: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Usage:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity, transparent colour on merge image)');

define('PRODUCT_IMAGE_THUMBNAIL_FRAME_TITLE' , 'Product Thumbnails:Frame<br /><img src="images/config_frame.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_FRAME_DESC' , 'Product Thumbnails:Frame<br /><br />Default-values: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Usage:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW_TITLE' , 'Product Thumbnails:Drop-Shadow<br /><img src="images/config_shadow.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW_DESC' , 'Product Thumbnails:Drop-Shadow<br /><br />Default-values: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Usage:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR_TITLE' , 'Product Thumbnails:Motion-Blur<br /><img src="images/config_motion.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR_DESC' , 'Product Thumbnails:Motion-Blur<br /><br />Default-values: (4,FFFFFF)<br /><br />fading parallel lines<br />Usage:<br />(int number of lines,hex background colour)');

//And this is for the Images showing your products in single-view

define('PRODUCT_IMAGE_INFO_BEVEL_TITLE' , 'Product Images:Bevel');
define('PRODUCT_IMAGE_INFO_BEVEL_DESC' , 'Product Images:Bevel<br /><br />Default-values: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Usage:<br />(edge width, hex light colour, hex dark colour)');

define('PRODUCT_IMAGE_INFO_GREYSCALE_TITLE' , 'Product Images:Greyscale');
define('PRODUCT_IMAGE_INFO_GREYSCALE_DESC' , 'Product Images:Greyscale<br /><br />Default-values: (32,22,22)<br /><br />basic black n white<br />Usage:<br />(int red, int green, int blue)');

define('PRODUCT_IMAGE_INFO_ELLIPSE_TITLE' , 'Product Images:Ellipse');
define('PRODUCT_IMAGE_INFO_ELLIPSE_DESC' , 'Product Images:Ellipse<br /><br />Default-values: (FFFFFF)<br /><br />ellipse on bg colour<br />Usage:<br />(hex background colour)');

define('PRODUCT_IMAGE_INFO_ROUND_EDGES_TITLE' , 'Product Images:Round-edges');
define('PRODUCT_IMAGE_INFO_ROUND_EDGES_DESC' , 'Product Images:Round-edges<br /><br />Default-values: (5,FFFFFF,3)<br /><br />corner trimming<br />Usage:<br />( edge_radius, background colour, anti-alias width)');

define('PRODUCT_IMAGE_INFO_MERGE_TITLE' , 'Product Images:Merge');
define('PRODUCT_IMAGE_INFO_MERGE_DESC' , 'Product Images:Merge<br /><br />Default-values: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Usage:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity,transparent colour on merge image)');

define('PRODUCT_IMAGE_INFO_FRAME_TITLE' , 'Product Images:Frame');
define('PRODUCT_IMAGE_INFO_FRAME_DESC' , 'Product Images:Frame<br /><br />Default-values: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Usage:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_INFO_DROP_SHADOW_TITLE' , 'Product Images:Drop-Shadow');
define('PRODUCT_IMAGE_INFO_DROP_SHADOW_DESC' , 'Product Images:Drop-Shadow<br /><br />Default-values: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Usage:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_INFO_MOTION_BLUR_TITLE' , 'Product Images:Motion-Blur');
define('PRODUCT_IMAGE_INFO_MOTION_BLUR_DESC' , 'Product Images:Motion-Blur<br /><br />Default-values: (4,FFFFFF)<br /><br />fading parallel lines<br />Usage:<br />(int number of lines,hex background colour)');

define('PRODUCT_IMAGE_POPUP_BEVEL_TITLE' , 'Product Popup Images:Bevel');
define('PRODUCT_IMAGE_POPUP_BEVEL_DESC' , 'Product Popup Images:Bevel<br /><br />Default-values: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Usage:<br />(edge width,hex light colour,hex dark colour)');

define('PRODUCT_IMAGE_POPUP_GREYSCALE_TITLE' , 'Product Popup Images:Greyscale');
define('PRODUCT_IMAGE_POPUP_GREYSCALE_DESC' , 'Product Popup Images:Greyscale<br /><br />Default-values: (32,22,22)<br /><br />basic black n white<br />Usage:<br />(int red,int green,int blue)');

define('PRODUCT_IMAGE_POPUP_ELLIPSE_TITLE' , 'Product Popup Images:Ellipse');
define('PRODUCT_IMAGE_POPUP_ELLIPSE_DESC' , 'Product Popup Images:Ellipse<br /><br />Default-values: (FFFFFF)<br /><br />ellipse on bg colour<br />Usage:<br />(hex background colour)');

define('PRODUCT_IMAGE_POPUP_ROUND_EDGES_TITLE' , 'Product Popup Images:Round-edges');
define('PRODUCT_IMAGE_POPUP_ROUND_EDGES_DESC' , 'Product Popup Images:Round-edges<br /><br />Default-values: (5,FFFFFF,3)<br /><br />corner trimming<br />Usage:<br />(edge_radius,background colour,anti-alias width)');

define('PRODUCT_IMAGE_POPUP_MERGE_TITLE' , 'Product Popup Images:Merge');
define('PRODUCT_IMAGE_POPUP_MERGE_DESC' , 'Product Popup Images:Merge<br /><br />Default-values: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Usage:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity,transparent colour on merge image)');

define('PRODUCT_IMAGE_POPUP_FRAME_TITLE' , 'Product Popup Images:Frame');
define('PRODUCT_IMAGE_POPUP_FRAME_DESC' , 'Product Popup Images:Frame<br /><br />Default-values: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Usage:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_POPUP_DROP_SHADOW_TITLE' , 'Product Popup Images:Drop-Shadow');
define('PRODUCT_IMAGE_POPUP_DROP_SHADOW_DESC' , 'Product Popup Images:Drop-Shadow<br /><br />Default-values: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Usage:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_POPUP_MOTION_BLUR_TITLE' , 'Product Popup Images:Motion-Blur');
define('PRODUCT_IMAGE_POPUP_MOTION_BLUR_DESC' , 'Product Popup Images:Motion-Blur<br /><br />Default-values: (4,FFFFFF)<br /><br />fading parallel lines<br />Usage:<br />(int number of lines,hex background colour)');

define('IMAGE_MANIPULATOR_TITLE','GDlib processing');
define('IMAGE_MANIPULATOR_DESC','Image Manipulator for GD2 or GD1<br /><br /><b>NOTE:</b> image_manipulator_GD2_advanced.php support transparent PNG\s');


define('ACCOUNT_GENDER_TITLE' , 'Salutation');
define('ACCOUNT_GENDER_DESC' , 'Display salutation in customer account');
define('ACCOUNT_DOB_TITLE' , 'Date of Birth');
define('ACCOUNT_DOB_DESC' , 'Display date of birth in customer account');
define('ACCOUNT_COMPANY_TITLE' , 'Company');
define('ACCOUNT_COMPANY_DESC' , 'Display company in customer account');
define('ACCOUNT_SUBURB_TITLE' , 'Suburb');
define('ACCOUNT_SUBURB_DESC' , 'Display suburb in customer account');
define('ACCOUNT_STATE_TITLE' , 'District');
define('ACCOUNT_STATE_DESC' , 'Display district in customer account');

define('DEFAULT_CURRENCY_TITLE' , 'Default Currency');
define('DEFAULT_CURRENCY_DESC' , 'Currency to be used as default');
define('DEFAULT_LANGUAGE_TITLE' , 'Default Language');
define('DEFAULT_LANGUAGE_DESC' , 'Language to be used as default');
define('DEFAULT_ORDERS_STATUS_ID_TITLE' , 'Default Order Status');
define('DEFAULT_ORDERS_STATUS_ID_DESC' , 'Default order status when a new order is placed.');

define('SHIPPING_ORIGIN_COUNTRY_TITLE' , 'Country of Origin');
define('SHIPPING_ORIGIN_COUNTRY_DESC' , 'Select the country of origin to be used in shipping quotes.');
define('SHIPPING_ORIGIN_ZIP_TITLE' , 'Postal Code');
define('SHIPPING_ORIGIN_ZIP_DESC' , 'Enter the Postal Code (ZIP) of the Store to be used in shipping quotes.');
define('SHIPPING_MAX_WEIGHT_TITLE' , 'Enter the Maximum Package Weight you will ship');
define('SHIPPING_MAX_WEIGHT_DESC' , 'Carriers have a max weight limit for a single package. This is a common one for all.');
define('SHIPPING_BOX_WEIGHT_TITLE' , 'Package Tare weight.');
define('SHIPPING_BOX_WEIGHT_DESC' , 'What is the weight of typical packaging of small to medium packages?');
define('SHIPPING_BOX_PADDING_TITLE' , 'Larger packages - percentage increase.');
define('SHIPPING_BOX_PADDING_DESC' , 'For 10% enter 10');
define('SHOW_SHIPPING_TITLE' , 'Shipping Costs in Shopping Cart');
define('SHOW_SHIPPING_DESC' , 'Show link to shipping costs in shopping cart');
define('SHIPPING_INFOS_TITLE' , 'Group ID');
define('SHIPPING_INFOS_DESC' , 'Group ID of shipping costs content');
define('SHIPPING_DEFAULT_TAX_CLASS_METHOD_TITLE' , 'Calculation method of default tax class');
define('SHIPPING_DEFAULT_TAX_CLASS_METHOD_DESC' , 'none: do not show shipping tax<br />auto proportional: show shipping tax proportional to order<br />auto max: show shipping tax, use tax rate of biggest turnover group');

define('PRODUCT_LIST_FILTER_TITLE' , 'Display Category/Manufacturer Filter (false=disable; true=enable)');
define('PRODUCT_LIST_FILTER_DESC' , 'Do you want to display the Category/Manufacturer Filter?');

define('STOCK_CHECK_TITLE' , 'Check Stock Level');
define('STOCK_CHECK_DESC' , 'Check to see if sufficent stock is available');

define('ATTRIBUTE_STOCK_CHECK_TITLE' , 'Check Attribute Stock Level');
define('ATTRIBUTE_STOCK_CHECK_DESC' , 'Check to see if sufficent attribute stock is available');
define('STOCK_LIMITED_TITLE' , 'Subtract stock');
define('STOCK_LIMITED_DESC' , 'Subtract product quantity in order from quantity of products in stock');
define('STOCK_ALLOW_CHECKOUT_TITLE' , 'Allow Checkout');
define('STOCK_ALLOW_CHECKOUT_DESC' , 'Allow customer to checkout even if there is insufficient stock');
define('STOCK_MARK_PRODUCT_OUT_OF_STOCK_TITLE' , 'Mark product out of stock');
define('STOCK_MARK_PRODUCT_OUT_OF_STOCK_DESC' , 'Display on-screen message so customers can see which product has insufficient stock');
define('STOCK_REORDER_LEVEL_TITLE' , 'Stock re-order level');
define('STOCK_REORDER_LEVEL_DESC' , 'Define when stock needs to be re-ordered (planned function)');
define('STORE_PAGE_PARSE_TIME_TITLE' , 'Store page parse time');
define('STORE_PAGE_PARSE_TIME_DESC' , 'Store the time it takes to parse a page');
define('STORE_PAGE_PARSE_TIME_LOG_TITLE' , 'Log file destination');
define('STORE_PAGE_PARSE_TIME_LOG_DESC' , 'Enter storage location/filename, where the page parse time as well as the database queries will be stored.<br/><strong>Caution: File can get very big in size in long logging sessions !</strong>.<br/><br/>Default value "page_parse_time.log":<br/><strong>Frontend:</strong> Parse time and database queries in the shop frontend will be stored in the root directory in the file "<strong>page_parse_time.log</strong>".<br/><strong>Administration (Backend):</strong> Parse time and database queries in the shop backend will be stored in the file "<strong>admin/page_parse_time.log</strong>".');
define('STORE_PARSE_DATE_TIME_FORMAT_TITLE' , 'Log file date format');
define('STORE_PARSE_DATE_TIME_FORMAT_DESC' , 'The date format (Default: %d/%m/%Y %H:%M:%S)');

define('DISPLAY_PAGE_PARSE_TIME_TITLE' , 'Display The Page Parse Time');
define('DISPLAY_PAGE_PARSE_TIME_DESC' , 'Display the page parse time (store page parse time must be enabled)');
define('STORE_DB_TRANSACTIONS_TITLE' , 'Store Database Queries');
define('STORE_DB_TRANSACTIONS_DESC' , 'Store the database queries in the page parse time log file');

define('USE_CACHE_TITLE' , 'Use Cache');
define('USE_CACHE_DESC' , 'Use caching features');

define('DB_CACHE_TITLE','Database Caching');
define('DB_CACHE_DESC','Cache SELECT query results in files to gain more speed for slow databases.');

define('DB_CACHE_EXPIRE_TITLE','DB Cache lifetime');
define('DB_CACHE_EXPIRE_DESC','Time in seconds to rebuild cached result.');

define('DIR_FS_CACHE_TITLE' , 'Cache Directory');
define('DIR_FS_CACHE_DESC' , 'The directory where cached files are saved');

define('ACCOUNT_OPTIONS_TITLE','Account Options');
define('ACCOUNT_OPTIONS_DESC','How do you want to configure the login procedure of your store?<br />You can choose between regular customer accounts and "One-Off Orders" without creating a customer account (an account will be created but the customer won\'t be informed about that)');

define('EMAIL_TRANSPORT_TITLE' , 'E-Mail Transport Method');
define('EMAIL_TRANSPORT_DESC' , 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running on Windows and MacOS should change this setting to SMTP.');

define('EMAIL_LINEFEED_TITLE' , 'E-Mail Linefeeds');
define('EMAIL_LINEFEED_DESC' , 'Defines the character sequence used to separate mail headers.');
define('EMAIL_USE_HTML_TITLE' , 'Use MIME HTML When Sending E-Mails');
define('EMAIL_USE_HTML_DESC' , 'Send E-Mails in HTML format');
define('ENTRY_EMAIL_ADDRESS_CHECK_TITLE' , 'Verify E-Mail Address Through DNS');
define('ENTRY_EMAIL_ADDRESS_CHECK_DESC' , 'Verify e-mail address through a DNS server');
define('SEND_EMAILS_TITLE' , 'Send E-Mails');
define('SEND_EMAILS_DESC' , 'Send out E-Mails');
define('SENDMAIL_PATH_TITLE' , 'The Path to sendmail');
define('SENDMAIL_PATH_DESC' , 'If you use sendmail, please give the right path (default: /usr/bin/sendmail):');
define('SMTP_MAIN_SERVER_TITLE' , 'Address of the SMTP Server');
define('SMTP_MAIN_SERVER_DESC' , 'Please enter the address of your main SMTP Server.');
define('SMTP_BACKUP_SERVER_TITLE' , 'Address of the SMTP Backup Server');
define('SMTP_BACKUP_SERVER_DESC' , 'Please enter the address of your Backup SMTP Server.');
define('SMTP_USERNAME_TITLE' , 'SMTP Username');
define('SMTP_USERNAME_DESC' , 'Please enter the username of your SMTP Account.');
define('SMTP_PASSWORD_TITLE' , 'SMTP Password');
define('SMTP_PASSWORD_DESC' , 'Please enter the password of your SMTP Account.');
define('SMTP_AUTH_TITLE' , 'SMTP AUTH');
define('SMTP_AUTH_DESC' , 'Enable secure authentication for your SMTP Server');
define('SMTP_PORT_TITLE' , 'SMTP Port');
define('SMTP_PORT_DESC' , 'Please enter the SMTP port of your SMTP server(default: 25)?');

//DokuMan - 2011-09-20 - E-Mail SQL errors
define('EMAIL_SQL_ERRORS_TITLE','Send SQL error messages to shop owner via email');
define('EMAIL_SQL_ERRORS_DESC','When "true" an email will be sent to the shop owner\'s email address containing the appropriate SQL error message. The SQL error message itself will be hidden from the customer.<br />When "false" the SQL error message will be displayed directly and visible for everybody (default).');

//Constants for contact_us
define('CONTACT_US_EMAIL_ADDRESS_TITLE' , 'Contact Us - E-Mail Address');
define('CONTACT_US_EMAIL_ADDRESS_DESC' , 'Please enter an e-mail address used for "Contact Us" messages');
define('CONTACT_US_NAME_TITLE' , 'Contact Us - E-Mail Name');
define('CONTACT_US_NAME_DESC' , 'Please enter a name to be used for "Contact Us" messages');
define('CONTACT_US_FORWARDING_STRING_TITLE' , 'Contact Us - Forwarding-To');
define('CONTACT_US_FORWARDING_STRING_DESC' , 'Please enter e-mail addresses (separated by ",") where "Contact Us" messages should be forwarded to.');
define('CONTACT_US_REPLY_ADDRESS_TITLE' , 'Contact Us - Reply-To');
define('CONTACT_US_REPLY_ADDRESS_DESC' , 'Please enter an e-mail address where customers can reply to.');
define('CONTACT_US_REPLY_ADDRESS_NAME_TITLE' , 'Contact Us - Reply-To Name');
define('CONTACT_US_REPLY_ADDRESS_NAME_DESC' , 'Please enter a name to be used in the reply-to field of "Contact Us" meesages.');
define('CONTACT_US_EMAIL_SUBJECT_TITLE' , 'Contact Us - E-Mail Subject');
define('CONTACT_US_EMAIL_SUBJECT_DESC' , 'Please enter an e-mail subject for "Contact Us" messages.');

//Constants for support system
define('EMAIL_SUPPORT_ADDRESS_TITLE' , 'Technical Support - E-Mail address');
define('EMAIL_SUPPORT_ADDRESS_DESC' , 'Please enter an e-mail address for sending e-mails over the <b>Support System</b> (account creation, lost password).');
define('EMAIL_SUPPORT_NAME_TITLE' , 'Technical Support - E-Mail Name');
define('EMAIL_SUPPORT_NAME_DESC' , 'Please enter a name for sending E-Mails over the <b>Support System</b> (account creation, lost password).');
define('EMAIL_SUPPORT_FORWARDING_STRING_TITLE' , 'Technical Support - Forwarding-To');
define('EMAIL_SUPPORT_FORWARDING_STRING_DESC' , 'Please enter forwarding addresses for mails of the <b>Support System</b> (seperated by , )');
define('EMAIL_SUPPORT_REPLY_ADDRESS_TITLE' , 'Technical Support - Reply-To');
define('EMAIL_SUPPORT_REPLY_ADDRESS_DESC' , 'Please enter an e-mail address for replies of your customers.');
define('EMAIL_SUPPORT_REPLY_ADDRESS_NAME_TITLE' , 'Technical Support - Reply-To Name');
define('EMAIL_SUPPORT_REPLY_ADDRESS_NAME_DESC' , 'Please enter a name to be used in the reply-to field of support e-mails.');
define('EMAIL_SUPPORT_SUBJECT_TITLE' , 'Technical Support - E-Mail Subject');
define('EMAIL_SUPPORT_SUBJECT_DESC' , 'Please enter an e-mail subject for <b>Support System</b> messages.');

//Constants for Billing system
define('EMAIL_BILLING_ADDRESS_TITLE' , 'Billing - E-Mail address');
define('EMAIL_BILLING_ADDRESS_DESC' , 'Please enter an E-Mail address for sending e-mails over the <b>Billing System</b> (order confirmations, status changes, ...).');
define('EMAIL_BILLING_NAME_TITLE' , 'Billing - E-Mail Name');
define('EMAIL_BILLING_NAME_DESC' , 'Please enter a name for sending e-mails over the <b>Billing System</b> (order confirmations, status changes, ...).');
define('EMAIL_BILLING_FORWARDING_STRING_TITLE' , 'Billing - Forwarding-To');
define('EMAIL_BILLING_FORWARDING_STRING_DESC' , 'Please enter forwarding addresses for mails of the <b>Billing System</b> (seperated by , )');
define('EMAIL_BILLING_REPLY_ADDRESS_TITLE' , 'Billing - Reply-To');
define('EMAIL_BILLING_REPLY_ADDRESS_DESC' , 'Please enter an e-mail address for replies of your customers.');
define('EMAIL_BILLING_REPLY_ADDRESS_NAME_TITLE' , 'Billing - Reply-To Name');
define('EMAIL_BILLING_REPLY_ADDRESS_NAME_DESC' , 'Please enter a name to be used in the reply-to field of billing e-mails.');
define('EMAIL_BILLING_SUBJECT_TITLE' , 'Billing - E-Mail Subject');
define('EMAIL_BILLING_SUBJECT_DESC' , 'Please enter an e-mail subject for <b>Billing</b> messages.');
define('EMAIL_BILLING_SUBJECT_ORDER_TITLE','Billing - Order Mail Subject');
define('EMAIL_BILLING_SUBJECT_ORDER_DESC','Please enter a subject for order mails generated by xtcm. (like <b>our order {$nr},{$date}</b>). You can use, {$nr},{$date},{$firstname},{$lastname}');

define('DOWNLOAD_ENABLED_TITLE' , 'Enable Download');
define('DOWNLOAD_ENABLED_DESC' , 'Enable the products download functions.');
define('DOWNLOAD_BY_REDIRECT_TITLE' , 'Download by Redirect');
define('DOWNLOAD_BY_REDIRECT_DESC' , 'Use browser redirection for download. Disabled on non-Unix systems.');
define('DOWNLOAD_MAX_DAYS_TITLE' , 'Expiry Delay (Days)');
define('DOWNLOAD_MAX_DAYS_DESC' , 'Set number of days before the download link expires. 0 means no limit.');
define('DOWNLOAD_MAX_COUNT_TITLE' , 'Maximum Number of Downloads');
define('DOWNLOAD_MAX_COUNT_DESC' , 'Set the maximum number of downloads. 0 means no download authorized.');

define('GZIP_COMPRESSION_TITLE' , 'Enable GZip Compression');
define('GZIP_COMPRESSION_DESC' , 'Enable HTTP gzip compression.');
define('GZIP_LEVEL_TITLE' , 'Compression Level');
define('GZIP_LEVEL_DESC' , 'Set a compression level from 0-9 (0 = minimum, 9 = maximum).');

define('SESSION_WARNING', '<br /><br /><font color="#FF0000"><strong>ATTENTION:</strong></font>
This feature might reduce the operability of the shop system. Change it only when you are aware of the following consequences and your webserver supports the corresponding feature.');

define('SESSION_WRITE_DIRECTORY_TITLE' , 'Session Directory');
define('SESSION_WRITE_DIRECTORY_DESC' , 'If sessions are file based, store them in this directory.');
define('SESSION_FORCE_COOKIE_USE_TITLE' , 'Force Cookie Use');
define('SESSION_FORCE_COOKIE_USE_DESC' , 'Force the use of sessions when cookies are only enabled (Default &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_SSL_SESSION_ID_TITLE' , 'Check SSL Session ID');
define('SESSION_CHECK_SSL_SESSION_ID_DESC' , 'Validate the SSL_SESSION_ID on every secure HTTPS page request. (Default &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_USER_AGENT_TITLE' , 'Check User Agent');
define('SESSION_CHECK_USER_AGENT_DESC' , 'Validate the client\'s browser user agent on every page request. (Default &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_IP_ADDRESS_TITLE' , 'Check IP Address');
define('SESSION_CHECK_IP_ADDRESS_DESC' , 'Validate the client\'s IP address on every page request. (Default &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_RECREATE_TITLE' , 'Recreate Session');
define('SESSION_RECREATE_DESC' , 'Recreate the session to generate a new session ID when a customer logs on or creates an account (PHP >=4.1 needed). (Default &quot;false&quot;)'.SESSION_WARNING);

define('DISPLAY_CONDITIONS_ON_CHECKOUT_TITLE' , 'Display Conditions on Checkout');
define('DISPLAY_CONDITIONS_ON_CHECKOUT_DESC' , 'Display terms and conditions and request approval on checkout');

define('META_MIN_KEYWORD_LENGTH_TITLE' , 'Min. Meta-Keyword Length');
define('META_MIN_KEYWORD_LENGTH_DESC' , 'min. length of a single keyword (generated from products description)');
define('META_KEYWORDS_NUMBER_TITLE' , 'Number of Meta-Keywords');
define('META_KEYWORDS_NUMBER_DESC' , 'number of keywords');
define('META_AUTHOR_TITLE' , 'Author');
define('META_AUTHOR_DESC' , '<meta name="author">');
define('META_PUBLISHER_TITLE' , 'Publisher');
define('META_PUBLISHER_DESC' , '<meta name="publisher">');
define('META_COMPANY_TITLE' , 'Company');
define('META_COMPANY_DESC' , '<meta name="company">');
define('META_TOPIC_TITLE' , 'page-topic');
define('META_TOPIC_DESC' , '<meta name="page-topic">');
define('META_REPLY_TO_TITLE' , 'Reply-To');
define('META_REPLY_TO_DESC' , '<meta name="reply-to">');
define('META_REVISIT_AFTER_TITLE' , 'Revisit-After');
define('META_REVISIT_AFTER_DESC' , '<meta name="revisit-after">');
define('META_ROBOTS_TITLE' , 'Robots');
define('META_ROBOTS_DESC' , '<meta name="robots">');
define('META_DESCRIPTION_TITLE' , 'Description');
define('META_DESCRIPTION_DESC' , '<meta name="description">');
define('META_KEYWORDS_TITLE' , 'Keywords');
define('META_KEYWORDS_DESC' , '<meta name="keywords">');

define('MODULE_PAYMENT_INSTALLED_TITLE' , 'Installed Payment Modules');
define('MODULE_PAYMENT_INSTALLED_DESC' , 'List of payment module filenames separated by semi-colon. The list is  updated automatically. No need to edit. (Example: cc.php;cod.php;paypal.php)');
define('MODULE_ORDER_TOTAL_INSTALLED_TITLE' , 'Installed Order Total Modules');
define('MODULE_ORDER_TOTAL_INSTALLED_DESC' , 'List of order_total module filenames separated by a semi-colon. The list is updated automatically. No need to edit. (Example: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)');
define('MODULE_SHIPPING_INSTALLED_TITLE' , 'Installed Shipping Modules');
define('MODULE_SHIPPING_INSTALLED_DESC' , 'List of shipping module filenames separated by a semi-colon. The list is updated automatically. No need to edit. (Example: ups.php;flat.php;item.php)');

define('CACHE_LIFETIME_TITLE','Cache Lifetime');
define('CACHE_LIFETIME_DESC','The number of seconds cached content will persist');
define('CACHE_CHECK_TITLE','Check if Cache Modified');
define('CACHE_CHECK_DESC','If true, then with cached content, If-Modified-Since headers are accounted for, and appropriate HTTP headers are sent. This way repeated hits to a cached page do not send the entire page to the client every time.');

define('PRODUCT_REVIEWS_VIEW_TITLE','Reviews in Product Details');
define('PRODUCT_REVIEWS_VIEW_DESC','Number of reviews displayed on the product details page');

define('DELETE_GUEST_ACCOUNT_TITLE','Delete Guest Accounts');
define('DELETE_GUEST_ACCOUNT_DESC','Shold guest accounts be deleted after placing orders? (Order data will be saved)');

define('USE_WYSIWYG_TITLE','Activate WYSIWYG Editor');
define('USE_WYSIWYG_DESC','Activate WYSIWYG editor for CMS and products');

define('PRICE_IS_BRUTTO_TITLE','Gross Admin');
define('PRICE_IS_BRUTTO_DESC','Usage of prices with tax in admin');

define('PRICE_PRECISION_TITLE','Gross/Net Precision');
define('PRICE_PRECISION_DESC','Gross/Net precision (Has no inluence on the display in the shop, which always displays 2 decimal places.)');

define('CHECK_CLIENT_AGENT_TITLE','Prevent Spider Sessions');
define('CHECK_CLIENT_AGENT_DESC','Prevent known spiders from starting a session.');
define('SHOW_IP_LOG_TITLE','IP-Log on Checkout?');
define('SHOW_IP_LOG_DESC','Show Text "Your IP will be saved", in checkout?');

define('ACTIVATE_GIFT_SYSTEM_TITLE','Activate Gift Voucher System');
define('ACTIVATE_GIFT_SYSTEM_DESC','Activate gift voucher system');

define('ACTIVATE_SHIPPING_STATUS_TITLE','Display Shipping Status');
define('ACTIVATE_SHIPPING_STATUS_DESC','Show shipping status? (Different dispatch times can be specified for individual products. If enabled, a new item <b>Delivery Status</b> is displayed on product input)');

define('SECURITY_CODE_LENGTH_TITLE','Security Code Length');
define('SECURITY_CODE_LENGTH_DESC','Security code length (Gift voucher)');

define('IMAGE_QUALITY_TITLE','Image Quality');
define('IMAGE_QUALITY_DESC','Image quality (0= highest compression, 100=best quality)');

define('GROUP_CHECK_TITLE','Customer Status Check');
define('GROUP_CHECK_DESC','Restrict access to individual categories, products and content items to specified customer groups (after activation, input fields will appear in categories, products and in content manager');

define('ACTIVATE_REVERSE_CROSS_SELLING_TITLE', 'Reverse Cross-Selling');
define('ACTIVATE_REVERSE_CROSS_SELLING_DESC', 'Activate reverse Cross-selling?');

define('ACTIVATE_NAVIGATOR_TITLE','Activate Product Navigator?');
define('ACTIVATE_NAVIGATOR_DESC','activate/deactivate product navigator in product_info, (deactivate for better performance if lots of articles are present in system)');

define('QUICKLINK_ACTIVATED_TITLE','Activate Multilink / Copy Function');
define('QUICKLINK_ACTIVATED_DESC','Allows selection of multiple categories when performing "copy product to"');

define('DOWNLOAD_UNALLOWED_PAYMENT_TITLE', 'Disallowed Download Payment Modules');
define('DOWNLOAD_UNALLOWED_PAYMENT_DESC', '<strong>DISALLOWED</strong> payment modules for downloads. Comma separated list, e.g. {banktransfer,cod,invoice,moneyorder}');
define('DOWNLOAD_MIN_ORDERS_STATUS_TITLE', 'Min. Order Status');
define('DOWNLOAD_MIN_ORDERS_STATUS_DESC', 'Min. order status to allow download of files.');

// Vat Check
define('STORE_OWNER_VAT_ID_TITLE' , 'VAT Reg No of Shop Owner');
define('STORE_OWNER_VAT_ID_DESC' , 'The VAT Reg No of the Shop Owner');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_TITLE' , 'Customers Group Approved VAT Reg No (Foreign Country)');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_DESC' , 'Customers group for customers whose VAT Reg No has been checked and approved, shop country <> customer\'s country');
define('ACCOUNT_COMPANY_VAT_CHECK_TITLE' , 'Validate VAT Reg No');
define('ACCOUNT_COMPANY_VAT_CHECK_DESC' , 'Customers may enter a VAT Registration number. If false, the box disappears');
define('ACCOUNT_COMPANY_VAT_LIVE_CHECK_TITLE' , 'Validate VAT Reg No online for plausability');
define('ACCOUNT_COMPANY_VAT_LIVE_CHECK_DESC' , 'Validate VAT Registration number online for plausability using the webservice of the taxation portal of the EU (<a href="http://ec.europa.eu/taxation_customs" style="font-style:italic">http://ec.europa.eu/taxation_customs</a>).<br/>Requires PHP5 with activated "SOAP" support! <strong><span class="messageStackSuccess">The "PHP5 SOAP"-support is actually '.(in_array ('soap', get_loaded_extensions()) ? '' : '<span class="messageStackError">NOT</span>').' active!</span></strong>');
define('ACCOUNT_COMPANY_VAT_GROUP_TITLE' , 'Automatic Pruning?');
define('ACCOUNT_COMPANY_VAT_GROUP_DESC' , 'Set to true, the customers group will be changed automatically if a valid VAT Reg No is used');
define('ACCOUNT_VAT_BLOCK_ERROR_TITLE' , 'Allow Invalid VAT Reg No?');
define('ACCOUNT_VAT_BLOCK_ERROR_DESC' , 'Set to true, only validated VAT Reg No are accepted');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL_TITLE','Customers Group - Approved VAT Reg No (Shop country)');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL_DESC','Customers group for customers whose VAT Reg No has been checked and approved, shop country = customers country');

// Google Conversion
define('GOOGLE_CONVERSION_TITLE','Google Conversion Tracking');
define('GOOGLE_CONVERSION_DESC','Track conversion keywords on orders');
define('GOOGLE_CONVERSION_ID_TITLE','Conversion ID');
define('GOOGLE_CONVERSION_ID_DESC','Your Google conversion ID');
define('GOOGLE_LANG_TITLE','Google Language');
define('GOOGLE_LANG_DESC','ISO code of used language');

// Afterbuy
define('AFTERBUY_ACTIVATED_TITLE','Active');
define('AFTERBUY_ACTIVATED_DESC','Activate afterbuy module');
define('AFTERBUY_PARTNERID_TITLE','Partner ID');
define('AFTERBUY_PARTNERID_DESC','Your Afterbuy Partner ID');
define('AFTERBUY_PARTNERPASS_TITLE','Partner Password');
define('AFTERBUY_PARTNERPASS_DESC','Your partner password for Afterbuy XML module');
define('AFTERBUY_USERID_TITLE','User ID');
define('AFTERBUY_USERID_DESC','Your Afterbuy user ID');
define('AFTERBUY_ORDERSTATUS_TITLE','Order Status');
define('AFTERBUY_ORDERSTATUS_DESC','Order status for exported orders');
define('AFTERBUY_URL','You will find detailed Afterbuy info here: <a href="http://www.afterbuy.de" target="new">http://www.afterbuy.de</a>');
define('AFTERBUY_DEALERS_TITLE', 'mark as Dealer');
define('AFTERBUY_DEALERS_DESC', 'Example: <em>6,5,8</em>');
define('AFTERBUY_IGNORE_GROUPE_TITLE', 'Customer group ignor');
define('AFTERBUY_IGNORE_GROUPE_DESC', 'Example: <em>6,5,8</em>.');

// Search-Options
define('SEARCH_IN_DESC_TITLE','Search in Products Descriptions');
define('SEARCH_IN_DESC_DESC','Include products descriptions when searching');
define('SEARCH_IN_ATTR_TITLE','Search in Products Attributes');
define('SEARCH_IN_ATTR_DESC','Include products attributes when searching');

// changes for 3.0.4 SP2
define('REVOCATION_ID_TITLE','Revocation ID');
define('REVOCATION_ID_DESC','Content ID of revocation content');
define('DISPLAY_REVOCATION_ON_CHECKOUT_TITLE','Display right of revocation?');
define('DISPLAY_REVOCATION_ON_CHECKOUT_DESC','Display right of revocation on checkout_confirmation?');

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('PAYPAL_MODE_TITLE','PayPal Mode:');
define('PAYPAL_MODE_DESC','Live (Default) or Test (Sandbox). Depending on the mode, you first have to create the PayPal API access: <br/>Link: <a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" target="_blank"><strong>Create API-access for live-mode</strong></a><br/>Link: <a href="https://www.sandbox.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" target="_blank"><strong>Create API-access for sandbox-mode</strong></a><br/>You still have no PayPal account? <a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=_registration-run" target="_blank"><strong>Click here to create one.</strong></a>');
define('PAYPAL_API_USER_TITLE','PayPal API-User (Live)');
define('PAYPAL_API_USER_DESC','Enter user name (live)');
define('PAYPAL_API_PWD_TITLE','PayPal API-Password (Live)');
define('PAYPAL_API_PWD_DESC','Enter password (live)');
define('PAYPAL_API_SIGNATURE_TITLE','PayPal API-Signature (Live)');
define('PAYPAL_API_SIGNATURE_DESC','Enter API signature (live)');
define('PAYPAL_API_SANDBOX_USER_TITLE','PayPal-API-User (Sandbox)');
define('PAYPAL_API_SANDBOX_USER_DESC','Enter user name (sandbox)');
define('PAYPAL_API_SANDBOX_PWD_TITLE','PayPal API-Password (Sandbox)');
define('PAYPAL_API_SANDBOX_PWD_DESC','Enter password (sandbox)');
define('PAYPAL_API_SANDBOX_SIGNATURE_TITLE','PayPal API-Signature (Sandbox)');
define('PAYPAL_API_SANDBOX_SIGNATURE_DESC','Enter API signature (sandbox)');
define('PAYPAL_API_VERSION_TITLE','PayPal API-Version');
define('PAYPAL_API_VERSION_DESC','Enter PayPal API version, e.g. 84.0');
define('PAYPAL_API_IMAGE_TITLE','PayPal Shop Logo');
define('PAYPAL_API_IMAGE_DESC','Please enter the name of the logo file to be displayed with PayPal.<br />Note: Only displayed if the shop uses SSL.<br />Max. width: 750px, max. height: 90px.<br />The logo file is called from: '.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
define('PAYPAL_API_CO_BACK_TITLE','PayPal Background Colour');
define('PAYPAL_API_CO_BACK_DESC','Enter a background colour to be displayed with PayPal. e.g. FEE8B9');
define('PAYPAL_API_CO_BORD_TITLE','PayPal Border Colour');
define('PAYPAL_API_CO_BORD_DESC','Enter a border colour to be displayed with PayPal. e.g. E4C558');
define('PAYPAL_ERROR_DEBUG_TITLE','PayPal Error Message');
define('PAYPAL_ERROR_DEBUG_DESC','Display PayPal error message? default=false');
define('PAYPAL_ORDER_STATUS_TMP_ID_TITLE','Order Status "cancel"');
define('PAYPAL_ORDER_STATUS_TMP_ID_DESC','Select the order status for aborted transaction (e.g. PayPal abort)');
define('PAYPAL_ORDER_STATUS_SUCCESS_ID_TITLE','Order Status OK');
define('PAYPAL_ORDER_STATUS_SUCCESS_ID_DESC','Select the order status for a successful transaction (e.g. open PP paid)');
define('PAYPAL_ORDER_STATUS_PENDING_ID_TITLE','Order Status "pending"');
define('PAYPAL_ORDER_STATUS_PENDING_ID_DESC','Select the order status for a transaction which hasn\'t been fully processed by PayPal (e.g. open PP waiting)');
define('PAYPAL_ORDER_STATUS_REJECTED_ID_TITLE','Order Status "rejected"');
define('PAYPAL_ORDER_STATUS_REJECTED_ID_DESC','Select the order status for a rejected transaction (e.g. PayPal rejected)');
define('PAYPAL_COUNTRY_MODE_TITLE','PayPal Country Mode');
define('PAYPAL_COUNTRY_MODE_DESC','Select a country mode. Some PayPal functions are available only in the UK (e.g. DirectPayment)');
define('PAYPAL_EXPRESS_ADDRESS_CHANGE_TITLE','PayPal-Express Address Data');
define('PAYPAL_EXPRESS_ADDRESS_CHANGE_DESC','Permits changing address data transferred by PayPal.');
define('PAYPAL_EXPRESS_ADDRESS_OVERRIDE_TITLE','Ship-To-Address Overwrite');
define('PAYPAL_EXPRESS_ADDRESS_OVERRIDE_DESC','Permits changing address data transferred by PayPal (existing account)');
define('PAYPAL_INVOICE_TITLE','Shop connoisseur for PayPal Invoice-No.');
define('PAYPAL_INVOICE_DESC','Letter(s) before the Order-No. as Shop connoisseurs to be set and as Invoice-No. of PayPal to be used. By different Shop connoisseurs several shops with a PayPal account can work, without which it comes with same order-numbers to same invoice-numbers in the PayPal account.');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

// BOF - Tomcraft - 2009-11-02 - New admin top menu
define('USE_ADMIN_TOP_MENU_TITLE' , 'Admin Top Navigation');
define('USE_ADMIN_TOP_MENU_DESC' , 'Activate Admin Top Navigation? Otherwise the menu will be displayed on the left (classic view)');
// EOF - Tomcraft - 2009-11-02 - New admin top menu

// BOF - Tomcraft - 2009-11-02 - Admin language tabs
define('USE_ADMIN_LANG_TABS_TITLE' , 'Language Tabs with Categories / Articles');
define('USE_ADMIN_LANG_TABS_DESC' , 'Use language tabs with categories / articles?');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs

// BOF - Hendrik - 2010-08-11 - Thumbnails in admin products list
define('USE_ADMIN_THUMBS_IN_LIST_TITLE' , 'Admin products list images');
define('USE_ADMIN_THUMBS_IN_LIST_DESC' , 'Show an extra column in Admin products list with images of the categories / products?');
define('USE_ADMIN_THUMBS_IN_LIST_STYLE_TITLE', 'Admin products list images CSS-Style');
define('USE_ADMIN_THUMBS_IN_LIST_STYLE_DESC', 'Here, simple CSS style information to be entered - for example, the maximum width: max-width: 90px;');// EOF - Hendrik - 2010-08-11 - Thumbnails in admin products list
// EOF - Hendrik - 2010-08-11 - Thumbnails in admin products list

// BOF - Tomcraft - 2009-11-05 - Advanced contact form
define('USE_CONTACT_EMAIL_ADDRESS_TITLE' , 'Contact Us - sending option');
define('USE_CONTACT_EMAIL_ADDRESS_DESC' , 'Use "Contact Us" e-mail address for sending contact form (important for some Hosters like Hosteurope)');
// EOF - Tomcraft - 2009-11-05 - Advanced contact form

// BOF - Dokuman - 2010-02-04 - delete cache files in admin section
define('DELETE_CACHE_SUCCESSFUL', 'Cache deleted successfully.');
define('DELETE_TEMP_CACHE_SUCCESSFUL', 'Templatecache deleted successfully.');
// EOF - Dokuman - 2010-02-04 - delete cache files in admin section

// BOF - DokuMan - 2010-08-13 - set Google RSS Feed in admin section
define('GOOGLE_RSS_FEED_REFID_TITLE' , 'Google RSS Feed - refID');
define('GOOGLE_RSS_FEED_REFID_DESC' , 'Enter your campaign ID here. It will be appended to every link of the Google RSS Feed automaticallyt.');
// EOF - DokuMan - 2010-08-13 - set Google RSS Feed in admin section

// BOF - web28 - 2010-08-17 -  Bildgrößenberechnung kleinerer Bilder
define('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT_TITLE','Upscaling low-res images)');
define('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT_DESC','If set to <strong>false</strong>, upscaling of low-res images to default settings for image size is disabled. Set to <strong>true</strong> to enable upscaling of low-res images. In this case those images will be shown blurry.');
// EOF - web28 - 2010-08-17 -  Bildgrößenberechnung kleinerer Bilder

//BOF - hendrik - 2011-05-14 - independent invoice number and date
define('IBN_BILLNR_TITLE', 'Next invoice number');
define('IBN_BILLNR_DESC', 'When assigning an invoice number, this number is given next.');
define('IBN_BILLNR_FORMAT_TITLE', 'Invoice number format');
define('IBN_BILLNR_FORMAT_DESC', 'Format invoice number.: {n}=number, {d}=day, {m}=month, {y}=year, <br>example. "100{n}-{d}-{m}-{y}" => "10099-28-02-2007"');
//EOF - hendrik - 2011-05-14 - independent invoice number and date

//BOC - h-h-h - 2011-12-23 - Button "Buy Now" optional - default off
define('SHOW_BUTTON_BUY_NOW_TITLE', 'Show "Buy Now" in productlists');
define('SHOW_BUTTON_BUY_NOW_DESC', '<b>Caution:</b><br /> This option is judicially critical if customers can\'t see all important informations directly in the productlists .');
//EOC - h-h-h - 2011-12-23 - Button "Buy Now" optional - default off

//split page results
define('MAX_DISPLAY_ORDER_RESULTS_TITLE', 'Number of orders per page');
define('MAX_DISPLAY_ORDER_RESULTS_DESC', 'Maximum number of orders that are to be displayed in the grid per page.');
define('MAX_DISPLAY_LIST_PRODUCTS_TITLE', 'Number of products per page');
define('MAX_DISPLAY_LIST_PRODUCTS_DESC', 'Maximum number of products that are to be displayed in the grid per page.');
define('MAX_DISPLAY_LIST_CUSTOMERS_TITLE', 'Number of customres per page');
define('MAX_DISPLAY_LIST_CUSTOMERS_DESC', 'Maximum number of customers that are to be displayed in the grid per page.');
define ('MAX_ROW_LISTS_ATTR_OPTIONS_TITLE', 'Product Options: Number of Product Options per page');
define ('MAX_ROW_LISTS_ATTR_OPTIONS_DESC', 'Maximum number of Product Options to be displayed per page.');
define ('MAX_ROW_LISTS_ATTR_VALUES_TITLE', 'Product Options: Number of Option Values per page');
define ('MAX_ROW_LISTS_ATTR_VALUES_DESC', 'Maximum number of Option Values to be displayed per page.');

// Whos online
define ('WHOS_ONLINE_TIME_LAST_CLICK_TITLE', 'Who\'s Online - Display period in seconds');
define ('WHOS_ONLINE_TIME_LAST_CLICK_DESC', 'Timing of online users in the "Who\'s Online" table, afterwhich time the entries are deleted (min value: 900).');

//Sessions
define ('SESSION_LIFE_ADMIN_TITLE', 'Session Lifetime Admin');
define ('SESSION_LIFE_ADMIN_DESC', 'time in seconds after the expiry of the session time for Admins (logged out) - Default 7200');
define ('SESSION_LIFE_CUSTOMERS_TITLE', 'Session lifetime customer');
define ('SESSION_LIFE_CUSTOMERS_DESC', 'time in seconds after the expiry of the session time for customers (logging out) - Default 1440');

//checkout confirmation options
define ('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION_TITLE', 'Order Confirmation page: Description');
define ('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION_DESC', 'On the order confirmation page you want the Item Description will be displayed? Note: The short description is displayed when there is NO Item Order Description True, the description does not always displayed.');
define ('CHECKOUT_SHOW_PRODUCTS_IMAGES_TITLE', 'Order Confirmation page: Product images');
define ('CHECKOUT_SHOW_PRODUCTS_IMAGES_DESC', 'If on the order confirmation page, the product images are displayed?');
define ('CHECKOUT_SHOW_PRODUCTS_MODEL_TITLE', 'Order Confirmation Page: Item no.');
define ('CHECKOUT_SHOW_PRODUCTS_MODEL_DESC', 'on the order confirmation page you want the item number will be displayed.');

// Billing email attachments
define ('EMAIL_BILLING_ATTACHMENTS_TITLE', 'Billing - e-mail attachments for orders');
define ('EMAIL_BILLING_ATTACHMENTS_DESC', 'Example of attachments - the files are in the shop directory <b>pdf</b>, separate multiple attachments with comma and no space:<br /> pdf/agb.pdf,pdf/widerruf.pdf.');

// email images
define ('SHOW_IMAGES_IN_EMAIL_TITLE', 'Product Images in Order - Insert email');
define ('SHOW_IMAGES_IN_EMAIL_DESC', 'Product images in the HTML order confirmation - Insert Email (increases risk, which is classified the e-mail as SPAM)');
define ('SHOW_IMAGES_IN_EMAIL_DIR_TITLE', 'Email pictures folder');
define ('SHOW_IMAGES_IN_EMAIL_DIR_DESC', 'Select email pictures folder');
define ('SHOW_IMAGES_IN_EMAIL_STYLE_TITLE', 'Email images CSS style');
define ('SHOW_IMAGES_IN_EMAIL_STYLE_DESC', 'Here, simple CSS style information to be entered - for example, the maximum width: max-width: 90px;');

// Popup window configuration
define ('POPUP_SHIPPING_LINK_PARAMETERS_TITLE', 'Returns popup window URL parameter');
define ('POPUP_SHIPPING_LINK_PARAMETERS_DESC', 'Here, the URL parameters are entered - Default: & Keep This = true & type = spare true & height = 400 & width = 600');
define ('POPUP_SHIPPING_LINK_CLASS_TITLE', 'Returns popup CSS class');
define ('POPUP_SHIPPING_LINK_CLASS_DESC', 'Here CSS classes to be entered - Default: thickbox');
define ('POPUP_CONTENT_LINK_PARAMETERS_TITLE', 'content pages, pop-up URL parameters');
define ('POPUP_CONTENT_LINK_PARAMETERS_DESC', 'Here, the URL parameters are entered - Default: & Keep This = true & type = spare true & height = 400 & width = 600');
define ('POPUP_CONTENT_LINK_CLASS_TITLE', 'content pages popup CSS class');
define ('POPUP_CONTENT_LINK_CLASS_DESC', 'Here CSS classes to be entered - Default: thickbox');
define ('POPUP_PRODUCT_LINK_PARAMETERS_TITLE', 'Product pages popup URL parameter');
define ('POPUP_PRODUCT_LINK_PARAMETERS_DESC', 'Here, the URL parameters are entered - Default: & Keep This = true & type = spare true & height = 450 & width = 750');
define ('POPUP_PRODUCT_LINK_CLASS_TITLE', 'Product pages popup CSS class');
define ('POPUP_PRODUCT_LINK_CLASS_DESC', 'Here CSS classes to be entered - Default: thickbox');
define ('POPUP_COUPON_HELP_LINK_PARAMETERS_TITLE', 'Coupon Help popup window URL parameter');
define ('POPUP_COUPON_HELP_LINK_PARAMETERS_DESC', 'Here, the URL parameters are entered - Default: & Keep This = true & type = spare true & height = 450 & width = 750');
define ('POPUP_COUPON_HELP_LINK_CLASS_TITLE', 'Coupon Help popup CSS class');
define ('POPUP_COUPON_HELP_LINK_CLASS_DESC', 'Here CSS classes to be entered - Default: thickbox');

define ('POPUP_PRODUCT_PRINT_SIZE_TITLE', 'product Print view window size');
define ('POPUP_PRODUCT_PRINT_SIZE_DESC', 'Sets the size of the popup window to be defined - default: width = 640, height = 600');
define ('POPUP_PRINT_ORDER_SIZE_TITLE', 'order window size Print view');
define ('POPUP_PRINT_ORDER_SIZE_DESC', 'Sets the size of the popup window to be defined - default: width = 640, height = 600');

// BOF - Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking
define('TRACKING_COUNT_ADMIN_ACTIVE_TITLE' , 'Count page views of the shop owner');
define('TRACKING_COUNT_ADMIN_ACTIVE_DESC' , 'By activating this option, all page views of the administration usersof the shop owner will be counted as well. This will falsify the visitor stats.');
define('TRACKING_GOOGLEANALYTICS_ACTIVE_TITLE' , 'Activate Google Analytics tracking');
define('TRACKING_GOOGLEANALYTICS_ACTIVE_DESC' , 'By activating this option, all page views will be submitted to Google Analytics for later evaluation. Before using this option, you need to register at <a href="http://www.google.com/analytics/" target="_blank"><b>Google Analytics</b></a> and create a new account.');
define('TRACKING_GOOGLEANALYTICS_ID_TITLE' , 'Google Analytics account number');
define('TRACKING_GOOGLEANALYTICS_ID_DESC' , 'Enter your Google Analytics account number in the format "UA-XXXXXXXX-X" which you received after successfully creating an account.');
define('TRACKING_PIWIK_ACTIVE_TITLE' , 'Activate PIWIK Web-Analytics tracking');
define('TRACKING_PIWIK_ACTIVE_DESC' , 'In order to use PIWIK at all, you have to download and install it to your webspace at first. See also <a href="http://http://de.piwik.org/" target="_blank"><b>PIWIK Web-Analytics</b></a>. In comparison to Google Analytics all data will be stored locally, i.e. you as show owner have complete control over all data.');
define('TRACKING_PIWIK_LOCAL_PATH_TITLE' , 'PIWIK install path (without "http://")');
define('TRACKING_PIWIK_LOCAL_PATH_DESC' , 'Enter the path when PIWIK was installed successfully. The complete path of the domain has to be given, but without "http://", e.g. "www.domain.de/piwik".');
define('TRACKING_PIWIK_ID_TITLE' , 'PIWIK page ID');
define('TRACKING_PIWIK_ID_DESC' , 'In the PIWIK administration a page ID will be created per domain (usually "1")');
define('TRACKING_PIWIK_GOAL_TITLE' , 'PIWIK campaign number (optional)');
define('TRACKING_PIWIK_GOAL_DESC' , 'Enter your campaign number, if you want to track predefined goals.. Details see <a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank"><b>PIWIK: Tracking Goal Conversions</b></a>');
// EOF - Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking

define ('CONFIRM_SAVE_ENTRY_TITLE', 'Confirmation when saving articles/category');
define ('CONFIRM_SAVE_ENTRY_DESC', 'Should be made a confirmation message when saving products/categories? Default: true (yes)');

define('WHOS_ONLINE_IP_WHOIS_SERVICE_TITLE', 'Who\'s Online - Whois Lookup URL');
define('WHOS_ONLINE_IP_WHOIS_SERVICE_DESC', 'http://www.utrace.de/?query= or http://whois.domaintools.com/');

define('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS_TITLE', 'Completion of order - disable Sold out?');
define('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS_DESC', 'If a sold-out items (stocks 0) be disabled at the end of the order automatically? The article is no longer visible in the shop! <br /> On Products are available again shortly, the option should be set to "false"');

define('SHIPPING_STATUS_INFOS_TITLE', 'Shippingtime ID');
define('SHIPPING_STATUS_INFOS_DESC', 'Content ID for Shippingtime');