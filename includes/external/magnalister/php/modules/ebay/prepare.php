<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: prepare.php 674 2011-01-08 03:21:50Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

# return product details for each item in selection
function eBayGetSelection() {
    global $_MagnaSession;
    eBayRemoveDoublePrepareEntries();
    # Daten aus magnalister_ebay_properties (bereits frueher vorbereitet)
    $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');
    $shortDescColumnExists =  MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
    $blBusinessPoliciesSet = geteBayBusinessPolicies();

    if ($keytypeIsArtNr) {
        $dbOldSelectionQuery = 'SELECT '
            .' p.products_id products_id, ep.products_model products_model, '
            .' Price, IF(0.0=Price, 0, 1) as priceFrozen, StrikePriceConf, '
            .' ms.mpID mpID, Title, Subtitle, Description, MobileDescription, '
            .' p.products_weight AS products_weight, '
            .' pd.products_name products_name, pd.products_description description, '
            .($shortDescColumnExists?' pd.products_short_description ':'\'\'').' AS shortdescription, '
            .'PictureURL, GalleryURL, ConditionID, ConditionDescriptors, ConditionDescription, '
            .' PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, '
            .' Attributes, ItemSpecifics, eBayPicturePackPurge, VariationDimensionForPictures, GalleryType, '
            .' ListingType, ListingDuration, PaymentMethods, ShippingDetails, SellerProfiles, DispatchTimeMax, ePID '
            .', mwst'
            .' FROM '.TABLE_MAGNA_EBAY_PROPERTIES .' ep, '.TABLE_MAGNA_SELECTION.' ms, '
            . TABLE_PRODUCTS .' p, ' . TABLE_PRODUCTS_DESCRIPTION .' pd '
            .' WHERE ep.products_model = p.products_model '
            .' AND ep.Verified <> \'EMPTY\' '
            .' AND p.products_id = ms.pID AND ep.mpID = ms.mpID '
            .' AND pd.products_id = p.products_id '
            .' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
            .' AND selectionname=\'prepare\' '
            .' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
            .' AND session_id=\''.session_id().'\'';
    } else {
        $dbOldSelectionQuery = 'SELECT '
            .' ep.products_id products_id, ep.products_model products_model, '
            .' Price, IF(0.0=Price, 0, 1) as priceFrozen, StrikePriceConf, '
            .' ms.mpID mpID, Title, Subtitle, Description, MobileDescription, '
            .' pd.products_name products_name, pd.products_description description, '
            .($shortDescColumnExists?' pd.products_short_description ':'\'\'').' AS shortdescription, '
            .' PictureURL, GalleryURL, ConditionID, ConditionDescriptors, ConditionDescription, '
            .' p.products_weight AS products_weight, '
            .' PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, '
            .' Attributes, ItemSpecifics, eBayPicturePackPurge, VariationDimensionForPictures, GalleryType, '
            .' ListingType, ListingDuration, PaymentMethods, ShippingDetails, SellerProfiles, DispatchTimeMax , ePID'
            .', mwst'
            .' FROM '.TABLE_MAGNA_EBAY_PROPERTIES .' ep, '.TABLE_MAGNA_SELECTION.' ms, '
            . TABLE_PRODUCTS_DESCRIPTION .' pd, '.TABLE_PRODUCTS.' p '
            .' WHERE p.products_id=pd.products_id and ep.products_id = ms.pID AND ep.mpID = ms.mpID  AND pd.products_id = ep.products_id '
            .' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
            .' AND selectionname=\'prepare\' '
            .' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
            .' AND session_id=\''.session_id().'\'';
    }
    $dbOldSelection = MagnaDB::gi()->fetchArray($dbOldSelectionQuery);
    if (empty($dbOldSelection) && !is_array($dbOldSelection)) {
        $dbOldSelection = array();
    }
    $oldProducts = array();
    $matchingSelection = array();
    foreach ($dbOldSelection as $nr => $row) {
	if (!empty($row['PrimaryCategory'])) {
	// use old data only if category filled; otherwise, it comes from Matching
	// and is almost empty
        	$oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
	} else {
		$matchingSelection[] = $row;
		unset($dbOldSelection[$nr]);
	}
    }
    if (empty($oldProducts)) {
        $oldProductsList = "''";
    } else {
        $oldProductsList = '"'.implode('", "', $oldProducts).'"';
    }
    # Daten fuer magnalister_ebay_properties
    # die Namen schon fuer diese Tabelle
    # products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
    $dbNewSelectionQuery = 'SELECT '
        .' p.products_id AS products_id, '
        .' p.products_model AS products_model, '
        .' p.products_price AS Price, '
        .' ms.mpID AS mpID, '
        .' pd.products_name AS products_name, ';
    if ($shortDescColumnExists) {
        $dbNewSelectionQuery .=
            ' pd.products_short_description AS shortdescription, ';
    } else {
        $dbNewSelectionQuery .=
            ' \'\' AS shortdescription, ';
    }
    $sDefaultSellerProfiles = $blBusinessPoliciesSet
        ?json_encode(array (
            'Payment'  => getDBConfigValue('ebay.default.paymentsellerprofile' , $_MagnaSession['mpID'], 0),
            'Shipping' => getDBConfigValue('ebay.default.shippingsellerprofile', $_MagnaSession['mpID'], 0),
            'Return'   => getDBConfigValue('ebay.default.returnsellerprofile'  , $_MagnaSession['mpID'], 0)
        ))
        :'';
    $dbNewSelectionQuery .=
        ' pd.products_description AS description, '
        .' p.products_image AS PictureURL, '
        .' p.products_weight AS products_weight, '
        .' \''.$sDefaultSellerProfiles.'\' AS SellerProfiles, '
        .' \''.getDBConfigValue('ebay.DispatchTimeMax', $_MagnaSession['mpID'], 30).'\' AS DispatchTimeMax '
        .' FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd, '.TABLE_MAGNA_SELECTION.' ms '
        .' WHERE pd.products_id = p.products_id AND ms.pID = p.products_id '
        .' AND '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ('.$oldProductsList.') '
        .' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
        .' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
        .' AND selectionname=\'prepare\' '
        .' AND session_id=\''.session_id().'\'';
    $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
    $dbSelection = array_merge($dbOldSelection, $dbNewSelection);
    if (!empty($matchingSelection)) {
	foreach ($matchingSelection as $mrow) {
		foreach ($dbSelection as &$dbrow) {
			if (
			    ($keytypeIsArtNr && $mrow['products_model'] == $dbrow['products_model'])
            ||
			    (!$keytypeIsArtNr && $mrow['products_id'] == $dbrow['products_id'])
            ) {
                $dbrow['ePID'] = $mrow['ePID'];
                $dbrow['mwst'] = (int)$mrow['mwst'];
			}
		}
	}
    }
    if (false) { # DEBUG
        echo "dbOldSelectionQuery == \n$dbOldSelectionQuery<br />\n";
        echo "dbNewSelectionQuery == \n$dbNewSelectionQuery<br />\n";
        echo print_m($dbOldSelection, '$dbOldSelection');
        echo print_m($dbNewSelection, '$dbNewSelection');
        echo print_m($dbSelection, '$dbSelection');
    }
    $rowCount = 0;
    $imagePath = getDBConfigValue('ebay.imagepath',$_MagnaSession['mpID']);
    foreach ($dbSelection as &$current_row) {
        ++$rowCount;
        // Filter JNH Tab
        #$current_row['description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $current_row['description']);
        if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
            if (strpos($current_row['description'], '[TAB:')) {
                $current_row['description'] = substr($current_row['description'], 0, strpos($current_row['description'], '[TAB:'));
            }
        } else {
            $current_row['description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $current_row['description']);
        }

        $product_images = array_unique(MLProduct::gi()->getAllImagesByProductsId($current_row['products_id']));
        if(empty($current_row['PictureURL'])){//if product images was reset
            $aPictureUrls = $product_images;
        } else {
            $aPictureUrls = json_decode($current_row['PictureURL'],true);
        }

        if(getDBConfigValue(array('ebay.picturepack', 'val'), $_MagnaSession['mpID']) ){
            if(is_array($aPictureUrls)){
                $current_row['PictureURL'] = $aPictureUrls;
            }else{
                $current_row['PictureURL'] = array(
                    str_replace($imagePath, '', $current_row['PictureURL'])
                );
            }
        } elseif(is_array($aPictureUrls)) {//picture pack was active before , now it is inactive
            $current_row['PictureURL'] = $imagePath . current($aPictureUrls);
        } else {//picture pack inactive
            $current_row['PictureURL'] = empty($current_row['PictureURL'])? '': $current_row['PictureURL'];
            if (    (!empty($current_row['PictureURL']))
                && (false === strpos($current_row['PictureURL'], 'http'))) {
                $current_row['PictureURL'] = $imagePath . $current_row['PictureURL'];
            }
        }
        if(empty($current_row['GalleryType'])){
            $current_row['GalleryType'] = getDBConfigValue('ebay.gallery.type', $_MagnaSession['mpID'], 'Gallery');
        }
        if(empty($current_row['mwst'])){
            $current_row['mwst'] = getDBConfigValue('ebay.mwst', $_MagnaSession['mpID'], 0);
        }
    }

    if ((1 == $rowCount) && empty($dbSelection[0]['Description'])) {
        $eBayTemplate = getDBConfigValue('ebay.template.content',$_MagnaSession['mpID']);
        # Template fuellen
        # bei mehreren Artikeln erst beim Speichern fuellen
        # Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
        if (false) { # DEBUG
            echo print_m($dbSelection[0], '$dbSelection[0]');
        }
        $substitution = array (
            '#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
            '#ARTNR#' => $dbSelection[0]['products_model'],
            '#PID#' => $dbSelection[0]['products_id'],
            '#SKU#' => magnaPID2SKU($dbSelection[0]['products_id']),
            '#SHORTDESCRIPTION#' => fixHTMLUTF8Entities($dbSelection[0]['shortdescription']),
            '#DESCRIPTION#' => fixHTMLUTF8Entities(stripLocalWindowsLinks($dbSelection[0]['description'])),
            '#PICTURE1#' => is_array($dbSelection[0]['PictureURL'])? $imagePath.current($dbSelection[0]['PictureURL']) : $dbSelection[0]['PictureURL'],
            '#WEIGHT#' => ((float)$dbSelection[0]['products_weight']>0)?$dbSelection[0]['products_weight']:'',
        );
        $dbSelection[0]['Description'] = substitutePictures(eBaySubstituteTemplate(
            $_MagnaSession['mpID'], $dbSelection[0]['products_id'], $eBayTemplate, $substitution
        ), $dbSelection[0]['products_id'], $imagePath);
    }
    if ((1 == $rowCount) && empty($dbSelection[0]['MobileDescription'])) {
        $eBayMTemplate = getDBConfigValue('ebay.template.mobilecontent',$_MagnaSession['mpID']);
        # Template fuellen
        # bei mehreren Artikeln erst beim Speichern fuellen
        # Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
        if (false) { # DEBUG
            echo print_m($dbSelection[0], '$dbSelection[0]');
        }
        $substitution = array (
            '#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
            '#ARTNR#' => $dbSelection[0]['products_model'],
            '#PID#' => $dbSelection[0]['products_id'],
            '#SKU#' => magnaPID2SKU($dbSelection[0]['products_id']),
            '#SHORTDESCRIPTION#' => fixHTMLUTF8Entities($dbSelection[0]['shortdescription']),
            '#DESCRIPTION#' => fixHTMLUTF8Entities(stripLocalWindowsLinks($dbSelection[0]['description'])),
            '#WEIGHT#' => ((float)$dbSelection[0]['products_weight']>0)?$dbSelection[0]['products_weight']:'',
        );
        $dbSelection[0]['MobileDescription'] = strip_tags(eBaySubstituteTemplate(
            $_MagnaSession['mpID'], $dbSelection[0]['products_id'], $eBayMTemplate, $substitution
        ), '<ol></ol><ul></ul><li></li><br><br/><br />');
    }
    if ((1 == $rowCount) && empty($dbSelection[0]['Title'])) {
        $eBayTitleTemplate = getDBConfigValue('ebay.template.name',$_MagnaSession['mpID'], '#TITLE#');
        # Titel-Template fuellen
        # bei mehreren Artikeln erst beim Speichern fuellen
        # Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
        $substitution = array (
            '#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
            '#ARTNR#' => $dbSelection[0]['products_model'],
        );
        $dbSelection[0]['Title'] = eBaySubstituteTemplate(
            $_MagnaSession['mpID'], $dbSelection[0]['products_id'], $eBayTitleTemplate, $substitution
        );
    }
    return $dbSelection;
}

function jsProcessPrepareButton() {
    global $_MagnaSession;
    $div = '<div id="unpreparePopup"></div>'."\n";
    /*
     * deactivate all other checkboxes if "unprepare" checked
     * (must be done in 1 line and apostrophes + slashes escaped, otherwise it doesn't work in jDialog)
     */
    ob_start();?> <script type="text\/javascript">/*<![CDATA[*/ $(\'input[id="unprepare"]\').change(function() { if ($(this).attr(\'checked\') == \'checked\') { $(\'input[id="resetTitle"]\').prop(\'disabled\', true); $(\'input[id="resetSubtitle"]\').prop(\'disabled\', true); $(\'input[id="resetDescription"]\').prop(\'disabled\', true); $(\'input[id="resetPictures"]\').prop(\'disabled\', true); $(\'input[id="resetStrikePrices"]\').prop(\'disabled\', true); } else { $(\'input[id="resetTitle"]\').prop(\'disabled\', false); $(\'input[id="resetSubtitle"]\').prop(\'disabled\', false); $(\'input[id="resetDescription"]\').prop(\'disabled\', false); $(\'input[id="resetPictures"]\').prop(\'disabled\', false); $(\'input[id="resetStrikePrices"]\').prop(\'disabled\', false); } }); /*]]>*/<\/script> <?php
        $popupJs = ob_get_contents();
        ob_end_clean();
        /* checkboxes */
        $popupContent =
            '<form id="resetPrepareForm" action="magnalister.php?mp='.$_MagnaSession['mpID'].'&amp;mode=prepare" method="post">'
            .'<input type="checkbox" value="resetTitle" name="action[ebayprepareformaction][resetTitle]" id="resetTitle" />'.ML_EBAY_LABEL_RESET_PREPARE_TITLE.'<br />'
            .'<input type="checkbox" value="resetSubtitle" name="action[ebayprepareformaction][resetSubtitle]" id="resetSubtitle" />'.ML_EBAY_LABEL_RESET_PREPARE_SUBTITLE.'<br />'
            .'<input type="checkbox" value="resetDescription" name="action[ebayprepareformaction][resetDescription]" id="resetDescription" />'.ML_EBAY_LABEL_RESET_PREPARE_DESCRIPTION.'<br />'
            .'<input type="checkbox" value="resetPictures" name="action[ebayprepareformaction][resetPictures]" id="resetPictures" />'.ML_EBAY_LABEL_RESET_PREPARE_PICTURES.'<br />'
            .'<input type="checkbox" value="resetStrikePrices" name="action[ebayprepareformaction][resetStrikePrices]" id="resetStrikePrices" />'.ML_EBAY_LABEL_RESET_PREPARE_STRIKEPRICES.'<br />'
            .'<br /><input type="checkbox" value="unprepare" name="action[ebayprepareformaction][unprepare]" id="unprepare" />'.ML_EBAY_LABEL_UNPREPARE.'<br />'
            .'</form>';
        $popupContent .= $popupJs;

        /* jDialog call */
        ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#unpreparePopup').html('<?php echo $popupContent; ?>').jDialog({
		title: '<?php echo ML_BUTTON_LABEL_REVERT_FOR_SELECTION ?>',
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				jQuery(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				$('#resetPrepareForm').submit();
				jQuery(this).dialog('close');
			}
		}
	});
	jQuery($('#unpreparePopup')).dialog('close');
});
$('#reset_partly').click(function() {
	// works also without the open / close stuff (just jDialog on click), but so we have an example how to call the window afterwards
	jQuery($('#unpreparePopup')).dialog('open');
});
/*]]>*/</script>
    <?php
    $js = ob_get_contents();
    ob_end_clean();
    return ($div.$js);
}

function getStrikePriceConfigForPrepareTable() {
 global $_MagnaSession;
 $aStrikePriceConfigFromDB = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_MAGNA_CONFIG.' WHERE mkey LIKE \'ebay.strike.price.%\' AND mpID='.$_MagnaSession['mpID']);
 if (empty($aStrikePriceConfigFromDB)) return('{"ebay.strike.price.kind":"DontUse"}');
 $aConfig = array();
 foreach ($aStrikePriceConfigFromDB as $row) {
    $aConfig[$row['mkey']] = $row['value'];
 }
 return json_encode($aConfig);
}

#echo print_m($_POST, __LINE__.' $_POST');
#echo print_m($_GET, '$_GET');
#echo print_m($_MagnaSession, '$_MagnaSession');

if (!isset($IsMatching)) $IsMatching=false;
global $IsMatching;

$_url['mode'] = 'prepare';
$_url['view'] = 'apply';
$prepareSetting = array(
    'selectionName' => 'prepare'
);

if (!empty($_POST['Action']) && empty($_POST['action'])) {
    $_POST['action'] = $_POST['Action'];
}

/**
 * Daten speichern
 */
if (array_key_exists('savePrepareData', $_POST) || (!empty($_POST['action']) && ($_POST['action'] == 'SaveMatching'))) {
    @set_time_limit(300);
    $itemDetails = $_POST;
    unset($itemDetails['savePrepareData']);
    if (isset($itemDetails['Variations'])) {
        parse_str_unlimited($itemDetails['Variations'], $params);
        $itemDetails = $params;
        $_POST['SecondaryCategory'] = $itemDetails['SecondaryCategory'];
    }
    #	echo print_m($itemDetails, '$itemDetails');

    $pIDs = MagnaDB::gi()->fetchArray('
		SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			   selectionname=\''.$prepareSetting['selectionName'].'\' AND
			   session_id=\''.session_id().'\'
	', true);
// DEBUG
#echo print_m($itemDetails, __LINE__.' $itemDetails');
#echo print_m($pIDs, __LINE__.' $pIDs');
    if (1 == count($pIDs)) {
        SaveEBaySingleProductProperties($pIDs[0], $itemDetails);
    } else if (!empty($pIDs)) {
        /* hack für den Fall dass die Verarbeitung wg großer Datenmenge abbricht */
        if (0 != getDBConfigValue('ebay.maxPreparationsToSaveAtOnce', 0, 0)) {
            $pIDs = array_slice($pIDs, 0, getDBConfigValue('ebay.maxPreparationsToSaveAtOnce', 0, 0));
        }
        SaveEBayMultipleProductProperties($pIDs, $itemDetails);
    }

    if (!empty($_POST['action']) && ($_POST['action'] == 'SaveMatching')) {
        $selectedCategory = $itemDetails['PrimaryCategory'];
        $productModel = EbayHelper::gi()->getProductModel($prepareSetting['selectionName']);

        echo json_encode(EbayHelper::gi()->getMPVariations($selectedCategory,
            ((getDBConfigValue('general.keytype', '0') == 'artNr') ? $productModel : $pIDs[0]),
            true));
        exit();
    }

    require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCheckinSubmit.php');
    $ecs = new eBayCheckinSubmit(array(
        'itemsPerBatch'   => 1,
        'selectionName'   => $prepareSetting['selectionName'],
        'marketplace'     => 'ebay',
    ));
    $verified = $ecs->verifyOneItem();
    #echo print_m($verified, '$ecs->verifyOneItem()');

    if('SUCCESS' == $verified['STATUS']) {
	# wenn view=matching, so lassen
	#if ($_GET['view']='match') $_url['view'] = 'match';
	if ($_GET['view']='match') $_POST['prepare'] = 'matching';

        MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
            'mpID' => $_MagnaSession['mpID'],
            'selectionname' => $prepareSetting['selectionName'],
            'session_id' => session_id()
        ));
	if (1 == count($pIDs)) {
		$msgSuccess = ML_EBAY_LABEL_PREPARED_SUCCESS;
	} else {
		$msgSuccess = ML_EBAY_LABEL_PREPARED_SUCCESS_MULTI;
	}
       	echo '<div class="successBox">'.$msgSuccess.'</div>'."\n";
    } else if('ERROR' == $verified['STATUS']) {
        # noch mal in der Maske bleiben
        $_POST['prepare'] = 'prepare';
	# wenn view=matching, so lassen
	#if ($_GET['view']='match') $_url['view'] = 'match';
	if ($_GET['view']='match') $_POST['prepare'] = 'matching';


        /* Letzte Exception holen */
        $ex = $ecs->getLastException();
        /* Wenns eine Exception war und es sich nicht um einen Fehler in der API handelt... */
        if (is_object($ex) && ($ex->getSubsystem() != 'PHP')
            && ($errors = $ex->getErrorArray())
            && (    isset($errors['RESPONSEDATA'][0]['ERRORS'][0]['ERRORCODE'])
                 || isset($errors['ERRORS'][0]['ERRORMESSAGE']))
        ) {
            if (!isset($errors['RESPONSEDATA'][0]['ERRORS'][0]['ERRORCODE'])) {
                $errors['RESPONSEDATA'] = array(array ('ERRORS' => array (array (
                    'ERRORCODE' => '',
                    'ERRORMESSAGE' => $errors['ERRORS'][0]['ERRORMESSAGE'],
                    'ERRORCLASS' => 'RequestError',
                    'ERRORLEVEL' => 'Error',
                ))));
            }
            /* Sonderfall: Fehlermeldung überschrieben von eBayCheckinSubmit */
            if (isset($verified['OVERWRITE_ERRORS'])) {
                $errors['RESPONSEDATA'][0]['ERRORS']  = $verified['OVERWRITE_ERRORS'];   
            }
            $supportsUTF8 = (stripos($_SESSION['language_charset'], 'utf') !== false);
            /* ... als unkrittisch markieren. */
            $ex->setCriticalStatus(false);
            foreach ($errors['RESPONSEDATA'] as $ebayItemErrors) {
                foreach ($ebayItemErrors['ERRORS'] as $ebayError) {
                    if (($ebayError['ERRORCLASS'] != 'RequestError') || ($ebayError['ERRORLEVEL'] != 'Error')) continue;
                    if (!$supportsUTF8) arrayEntitiesToLatin1($ebayError);
                    if (    array_key_exists('ORIGIN',$ebayError)
                        && !empty($ebayError['ORIGIN'])         ) {
                        $sMsgHead = $ebayError['ORIGIN'].' '.ML_ERROR_LABEL.' '.$ebayError['ERRORCODE'].': ';
                    } else {
                        $sMsgHead = sprintf(ML_EBAY_LABEL_EBAYERROR, $ebayError['ERRORCODE']);
                    }
                    echo '<div class="ebay errorBox"><span class="error">'.$sMsgHead.'</span>: '.
                        $ebayError['ERRORMESSAGE'].'</div>';
                    //echo print_m($ebayError);
                }
            }
        }
    }
    echo "\n\n<!--\n".str_replace(array('<!--', '-->'), array('<!- -', '- ->'), json_indent($ecs->getLastRequest()))."-->\n\n";
}

if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST === false) {// will be done in MLProductListDependencyEbayPrepareFormAction
    /**
     * Daten loeschen
     */
    if ((array_key_exists('unprepare', $_POST)) && (!empty($_POST['unprepare']))) {
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$prepareSetting['selectionName'].'\' AND
				   session_id=\''.session_id().'\'
		', true);
        if (!empty($pIDs)) {
            foreach ($pIDs as $pID) {
                $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                    ? array ('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM '.TABLE_PRODUCTS.'
								 WHERE products_id='.$pID
                    ))
                    : array ('products_id'    => $pID);
                $where['mpID'] = $_MagnaSession['mpID'];

                MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, $where);
                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'pID' => $pID,
                    'mpID' => $_MagnaSession['mpID'],
                    'selectionname' => $prepareSetting['selectionName'],
                    'session_id' => session_id()
                ));
            }
        }
        unset($_POST['unprepare']);
    }

    /**
     * Nur Artikelbeschreibung loeschen
     */
    if ((array_key_exists('reset_description', $_POST)) && (!empty($_POST['reset_description']))) {
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$prepareSetting['selectionName'].'\' AND
				   session_id=\''.session_id().'\'
		', true);
        if (!empty($pIDs)) {
            foreach ($pIDs as $pID) {
                $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                    ? array ('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM '.TABLE_PRODUCTS.'
								 WHERE products_id='.$pID
                    ))
                    : array ('products_id'    => $pID);
                $where['mpID'] = $_MagnaSession['mpID'];

                MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, array('Description' => '', 'MobileDescription' => ''), $where);
                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'pID' => $pID,
                    'mpID' => $_MagnaSession['mpID'],
                    'selectionname' => $prepareSetting['selectionName'],
                    'session_id' => session_id()
                ));
            }
        }
        unset($_POST['reset_description']);
    }
}
/**
 * Matching Vorbereitung
 */
if (array_key_exists('prepare', $_POST) && (!empty($_POST['prepare']))) {

    /**
     * Fall 'nur nicht vorbereitete': Fertige aus der selection entfernen
     */
    if (isset($_POST['match']) && ($_POST['match'] == 'notmatched')) {
        MagnaDB::gi()->query('
			DELETE FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
				   AND selectionname=\''.$prepareSetting['selectionName'].'\'
				   AND session_id=\''.session_id().'\'
				   AND pID IN ( SELECT products_id
						FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
						 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
						 AND Verified = \'OK\' )
		');
    }


    $itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*)
		  FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			   selectionname=\''.$prepareSetting['selectionName'].'\' AND
			   session_id=\''.session_id().'\'
		GROUP BY selectionname
	');

    if ($itemCount == 1) {
        $prepareAction = 'singleprepare';
    } else if ($itemCount > 1) {
        $prepareAction = 'multiprepare';
    }
}

if (isset($prepareAction)
    || (
        isset($_GET['kind']) && ($_GET['kind'] == 'ajax')
        && isset($_GET['where']) && ($_GET['where'] == 'prepareView')
    )
) {
    require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCategoryMatching.php');
    require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayShippingDetailsProcessor.php');
    $kind = (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) ? 'ajax' : 'view';
    $ycm = new eBayCategoryMatching($kind);
    if ($kind == 'view') {
        require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/prepareView.php');
        echo renderPrepareView(eBayGetSelection());
        echo $ycm->render();
    } else if (array_key_exists('action', $_POST)) {
        switch ($_POST['action']) {
            case 'getListingDurations': {
                try {
                    $result = MagnaConnector::gi()->submitRequest(array(
                        'ACTION' => 'GetListingDurations',
                        'DATA' => array (
                            'ListingType' => $_POST['ListingType']
                        )
                    ));
                } catch (MagnaException $e) {
                    echo print_m($e->getErrorArray(), 'Error');
                    $result = array (
                        'DATA' => array (
                            'null' => 'Konnte nicht abgerufen werden.'
                        )
                    );
                }
                $html = '';
                if (!in_array($_POST['preselected'], $result['DATA']['ListingDurations'])) {
                    # Fall: 30 Tage sind default, aber hoechstens 10 verfuegbar
                    $highestKeyOfListingDurations = count($result['DATA']['ListingDurations']) - 1;
                    $_POST['preselected'] = $result['DATA']['ListingDurations']["$highestKeyOfListingDurations"];
                }
                foreach ($result['DATA']['ListingDurations'] as $duration) {
                    $define = 'ML_EBAY_LABEL_LISTINGDURATION_'.strtoupper($duration);
                    if ($_POST['preselected'] == $duration)
                        $html .= '
						<option selected="selected" value="'.$duration.'">'.(defined($define) ? constant($define) : $duration).'</option>';
                    else
                        $html .= '
						<option value="'.$duration.'">'.(defined($define) ? constant($define) : $duration).'</option>';
                }
                echo $html;
                break;
            }
            case 'extern': {
                $args = $_POST;
                unset($args['function']);
                unset($args['action']);
                $tmpURL = $_url;
                $tmpURL['where'] = 'prepareView';
                $blBusinessPoliciesSet = geteBayBusinessPolicies();
                if (!$blBusinessPoliciesSet) {
                    if ('true' == $args['international']) {
                        $shipProc = new eBayShippingDetailsProcessor($args, 'ebay.default.shipping.international', $tmpURL);
                    } else {
                        $shipProc = new eBayShippingDetailsProcessor($args, 'ebay.default.shipping.local', $tmpURL);
                    }
                    echo $shipProc->process();
                }
                break;
            }
            case 'getEBayAttributes': {
                echo getEBayAttributes($_POST['CategoryID'], $_POST['Mode'], (isset($_POST['preselectedValues'])?$_POST['preselectedValues']:''));
                break;
            }
            case 'LoadMPVariations': {
                if (isset($_POST['SelectValue'])) {
                    $select = $_POST['SelectValue'];
                } else {
                    $select = $_POST['PrimaryCategory'];
                }

                $productModel = EbayHelper::gi()->getProductModel($prepareSetting['selectionName']);

                echo json_encode(EbayHelper::gi()->getMPVariations($select, $productModel, true));
                break;
            }
            case 'makePrice': {
                require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
                echo makePrice($_POST['pID'], $_POST['ListingType']);
                break;
            }
            case 'makePriceByStrikePriceSettings': {
                require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
                if ($_POST['UseStrikePrice'] === 'checked') {
                    echo json_encode(makePriceByStrikePriceSettings($_POST['pID'], $_POST['StrikePriceKind'], $_POST['StrikePriceGroup'], 0, true));
                } else {
                    $aPriceByStrikePriceSettings = makePriceByStrikePriceSettings($_POST['pID'], $_POST['StrikePriceKind'], $_POST['StrikePriceGroup'], 0, true);
                    $aPriceByStrikePriceSettings['strikePrice'] = '0';
                    echo json_encode($aPriceByStrikePriceSettings);
                }
                break;
            }
            case 'GetSellerProfileData': {
                require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
                eBayGetSellerProfileData($_POST['value']);
                break;
            }
            default: {
                echo $ycm->render();
                break;
            }
        }
    }
} else {
    if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
        require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/EbayPrepareProductList.php');
        $o = new EbayPrepareProductList();
        echo $o . jsProcessPrepareButton();
    } else {
        require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/PrepareCategoryView.php');
        if (!isset($_GET['sorting'])) $_GET['sorting'] = false;
        if (!isset($_POST['tfSearch'])) $_POST['tfSearch'] = '';
        $eCV = new PrepareCategoryView(null, $prepareSetting, $_GET['sorting'], $_POST['tfSearch']); /* $current_category_id is a global variable from xt:Commerce */
        if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
            echo $eCV->renderAjaxReply();
        } else {
            echo $eCV->printForm();
        }
        unset($_MagnaShopSession['prepareMode']);
    }
}
