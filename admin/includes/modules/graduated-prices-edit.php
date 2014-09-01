<?php
/* --------------------------------------------------------------
   $Id: graduated-prices-edit.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on Third Party contribution:
   MOD graduated-prices-edit by Web4Business GmbH - Designs - Modules

   Released under the GNU General Public License
   --------------------------------------------------------------*/
 defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

function W4B_graduated_prices_edit_logic() {

  if(!isset($_GET['pID']))
    return;
?>
<tr style="display:none;"><td colspan="2">
<script type="text/javascript">
/*<![CDATA[*/

function W4B_graduated_prices_edit_removerow(objButton) {

  // fetch table cell
  var objCell = objButton.parentNode;

  // fetch corresponding row
  var objRow = objCell.parentNode;

  // fetch next row
  var objNextRow = objRow.nextSibling;

  // fetch table element to remove rows
  var objTable = objRow.parentNode;

  // remove the two rows
  objTable.removeChild(objRow);
  objTable.removeChild(objNextRow);

  return;
}

var W4B_graduated_prices_edit_objQuantity;
var W4B_graduated_prices_edit_objPrice;

function W4B_graduated_prices_edit_addrow(objButton, intStatus) {

  // fetch quantity field
  var objQuantity = document.getElementsByName("products_quantity_staffel_"+intStatus)[0];

  // fetch price field
  var objPrice = document.getElementsByName("products_price_staffel_"+intStatus)[0];

  // fetch table
  var objTable = document.getElementById("staffel_"+intStatus).getElementsByTagName("table")[0];

  // create row
  var objRow = document.createElement("tr");


  // create quantity cell
  var objQuantityCell = document.createElement("td");

  var objQuantityCellWidth = document.createAttribute("width");
  objQuantityCellWidth.nodeValue = "20";
  var objQuantityCellClassName = document.createAttribute("class");
  objQuantityCellClassName.nodeValue = "main";
  var objQuantityCellStyle = document.createAttribute("style");
  objQuantityCellStyle.nodeValue = "border: 1px solid; border-color: #cccccc;";

  objQuantityCell.setAttributeNode(objQuantityCellWidth);
  objQuantityCell.setAttributeNode(objQuantityCellClassName);
  objQuantityCell.setAttributeNode(objQuantityCellStyle);

  var objQuantityCellText = document.createTextNode(objQuantity.value);
  objQuantityCell.appendChild(objQuantityCellText);


  // create spacer cell
  var objSpacerCell = document.createElement("td");

  var objSpacerCellWidth = document.createAttribute("width");
  objSpacerCellWidth.nodeValue = "15";

  objSpacerCell.setAttributeNode(objSpacerCellWidth);

  var objSpacerCellText = document.createTextNode(" ");
  objSpacerCell.appendChild(objSpacerCellText);


  // create price cell
  var objPriceCell = document.createElement("td");

  var objPriceCellNowrap = document.createAttribute("nowrap");
  objPriceCellNowrap.nodeValue = "nowrap";
  var objPriceCellWidth = document.createAttribute("width");
  objPriceCellWidth.nodeValue = "142";
  var objPriceCellClassName = document.createAttribute("class");
  objPriceCellClassName.nodeValue = "main";
  var objPriceCellStyle = document.createAttribute("style");
  objPriceCellStyle.nodeValue = "border: 1px solid; border-color: #cccccc;";

  objPriceCell.setAttributeNode(objPriceCellNowrap);
  objPriceCell.setAttributeNode(objPriceCellWidth);
  objPriceCell.setAttributeNode(objPriceCellClassName);
  objPriceCell.setAttributeNode(objPriceCellStyle);

  var objPriceCellText = document.createTextNode(objPrice.value);
  objPriceCell.appendChild(objPriceCellText);


  // create delete cell
  var objDeleteCell = document.createElement("td");

  var objDeleteCellWidth = document.createAttribute("width");
  objDeleteCellWidth.nodeValue = "80";
  var objDeleteCellAlign = document.createAttribute("align");
  objDeleteCellAlign.nodeValue = "left";

  objDeleteCell.setAttributeNode(objDeleteCellWidth);
  objDeleteCell.setAttributeNode(objDeleteCellAlign);


  // create delete button
  var objDeleteButton = document.createElement("a");

  var objDeleteButtonClassName = document.createAttribute("class");
  objDeleteButtonClassName.nodeValue = "button";
  var objDeleteButtonHref = document.createAttribute("href");
  objDeleteButtonHref.nodeValue = '<?php global $cPath; echo xtc_href_link(FILENAME_CATEGORIES,
    'cPath=' . $cPath . '&function=delete&quantity=\'+objQuantity.value+\'&statusID=\'+intStatus+\'&action=new_product&pID=' . $_GET['pID']); ?>';
  var objDeleteButtonOnclick = document.createAttribute("onclick");
  objDeleteButtonOnclick.nodeValue = "W4B_graduated_prices_edit_removerow(this);";
  var objDeleteButtonStyle = document.createAttribute("style");
  objDeleteButtonStyle.nodeValue = "margin-left: 10px;";

  objDeleteButton.setAttributeNode(objDeleteButtonClassName);
  objDeleteButton.setAttributeNode(objDeleteButtonHref);
  objDeleteButton.setAttributeNode(objDeleteButtonOnclick);
  objDeleteButton.setAttributeNode(objDeleteButtonStyle);

  var objDeleteButtonText = document.createTextNode("<?php echo W4B_graduated_prices_edit_unhtmlentities(BUTTON_DELETE); ?>");
  objDeleteButton.appendChild(objDeleteButtonText);

  objDeleteCell.appendChild(objDeleteButton);


  // glue row together
  objRow.appendChild(objQuantityCell);
  objRow.appendChild(objSpacerCell);
  objRow.appendChild(objPriceCell);
  objRow.appendChild(objDeleteCell);


  // create spacer row
  var objNextRow = document.createElement("tr");
  var objNextCell = document.createElement("td");

  var objNextCellColspan = document.createAttribute("colspan");
  objNextCellColspan.nodeValue = "4";
  var objNextCellHeight = document.createAttribute("height");
  objNextCellHeight.nodeValue = "5";

  objNextCell.setAttributeNode(objNextCellColspan);
  objNextCell.setAttributeNode(objNextCellHeight);
  objNextRow.appendChild(objNextCell);


  // add the two rows
  objTable.appendChild(objRow);
  objTable.appendChild(objNextRow);


  // empty the input fields
  W4B_graduated_prices_edit_objQuantity = objQuantity;
  W4B_graduated_prices_edit_objPrice = objPrice;
  window.setTimeout('W4B_graduated_prices_edit_objQuantity.value = W4B_graduated_prices_edit_objPrice.value = "";', 500);
  objQuantity.focus();

  return;
}

function W4B_graduated_prices_edit_makeToggleTextClickable() {

  var objDivs = document.getElementsByTagName("div");
  var objImage, objStaffelText, objStaffelLink, objStaffelLinkStyle, objStaffelLinkOnclick;

  for(var i = 0; i < objDivs.length; i++) {

    if(objDivs[i].id.indexOf("staffel_") == -1)
      continue;

    objImage = objDivs[i].previousSibling;
    objStaffelText = objImage.previousSibling;

    objStaffelLink = document.createElement("span");
    objStaffelLinkStyle = document.createAttribute("style");
    objStaffelLinkStyle.nodeValue = "cursor: pointer";
    objStaffelLink.setAttributeNode(objStaffelLinkStyle);

    objStaffelLinkOnclick = document.createAttribute("onclick");
    objStaffelLinkOnclick.nodeValue = objImage.getAttribute("onclick");
    objStaffelLink.setAttributeNode(objStaffelLinkOnclick);

    objStaffelLink.appendChild(document.createTextNode(objStaffelText.nodeValue));
    objStaffelText.parentNode.replaceChild(objStaffelLink, objStaffelText);
  }
}

W4B_graduated_prices_edit_makeToggleTextClickable();

/*]]>*/
</script>
</td></tr>
<?php
}

function W4B_graduated_prices_edit_unhtmlentities($text) {

  // removes html entities and converts them into the real characters
  // @param  textToConvert
  // @return string convertedText

  for($i = 160; $i <= 255; $i++) {
    $letter = chr($i);
    $text = str_replace(htmlentities($letter,ENT_NOQUOTES,strtoupper($_SESSION['language_charset'])),$letter,$text); //web28 - 2012-04-29 - add charset encoding
  }

  $text = str_replace("&amp;"  ,"&",$text);
  $text = str_replace("&lt;"   ,"<",$text);
  $text = str_replace("&gt;"   ,">",$text);
  $text = str_replace("&quot;" ,'"',$text);
  $text = str_replace("&ndash;","–",$text);
  $text = str_replace("&bdquo;","„",$text);
  $text = str_replace("&ldquo;","“",$text);
  $text = str_replace("&rdquo;","”",$text);
  $text = str_replace("&euro;" ,"€",$text);

  return $text;
}

function W4B_graduated_prices_save() {

  $products_data = $_POST;
  $group_data = array();
  $products_id = (int)$_GET['pID'];

    $i = 0;
    $group_query = xtc_db_query("SELECT customers_status_id
                                         FROM ".TABLE_CUSTOMERS_STATUS."
                                        WHERE language_id = '".(int) $_SESSION['languages_id']."'
                                          AND customers_status_id != '0'");
    while ($group_values = xtc_db_fetch_array($group_query)) {
      // load data into array
      $i ++;
      $group_data[$i] = array ('STATUS_ID' => $group_values['customers_status_id']);
    }
    for ($col = 0, $n = sizeof($group_data); $col < $n +1; $col ++) {
      if ($group_data[$col]['STATUS_ID'] != '') {
        $quantity = xtc_db_prepare_input($products_data['products_quantity_staffel_'.$group_data[$col]['STATUS_ID']]);
        $staffelpreis = xtc_db_prepare_input($products_data['products_price_staffel_'.$group_data[$col]['STATUS_ID']]);

        if (PRICE_IS_BRUTTO == 'true') {
          $staffelpreis = ($staffelpreis / (xtc_get_tax_rate($products_data['products_tax_class_id']) + 100) * 100);
        }
        $staffelpreis = xtc_round($staffelpreis, PRICE_PRECISION);
        if ($staffelpreis != '' && $quantity != '') {
          // ok, lets check entered data to get rid of user faults
          if ($quantity <= 1)
            $quantity = 2;
          $check_query = xtc_db_query("SELECT quantity
                                                           FROM personal_offers_by_customers_status_".$group_data[$col]['STATUS_ID']."
                                                          WHERE products_id = '".$products_id."'
                                                            AND quantity    = '".$quantity."'");
          // dont insert if same qty!
          if (xtc_db_num_rows($check_query) < 1) {
            xtc_db_query("INSERT INTO personal_offers_by_customers_status_".$group_data[$col]['STATUS_ID']."
                                                   SET price_id       = '',
                                                       products_id    = '".$products_id."',
                                                       quantity       = '".$quantity."',
                                                       personal_offer = '".$staffelpreis."'");
          }
        }
      }
    }

  header("HTTP/1.0 204 No Content");
  die();
}

if(isset($_GET['action']) && $_GET['action'] == "update_product" && isset($_GET['pID']) && isset($_POST['graduated_prices_edit'])) {

  W4B_graduated_prices_save();
}

//this is used only by group_prices
$function = (isset($_GET['function']) ? $_GET['function'] : '');
if (xtc_not_null($function)) {
  switch ($function) {
    case 'delete' :
      xtc_db_query("DELETE FROM personal_offers_by_customers_status_".(int) $_GET['statusID']."
                                 WHERE products_id = '".(int) $_GET['pID']."'
                                 AND quantity    = '".(int) $_GET['quantity']."'");
      break;
  }

  header("HTTP/1.0 204 No Content");
  die();
}
?>