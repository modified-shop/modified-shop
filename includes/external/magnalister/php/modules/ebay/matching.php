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
 * $Id: matching.php 4961 2014-12-09 14:10:12Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// mode=prepare&view=match&kind=ajax&where=prepareView
if (    isset($_GET['mode'])  && $_GET['mode']  == 'prepare'
     && isset($_GET['kind'])  && $_GET['kind']  == 'ajax'
     && isset($_GET['where']) && $_GET['where'] == 'prepareView') {
    include(DIR_MAGNALISTER_MODULES.'ebay/prepare.php');
    return;
}
if (    array_key_exists('savePrepareData', $_POST)
) {
    include(DIR_MAGNALISTER_MODULES.'ebay/prepare.php');
    return;
}

include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/matchingViews.php');

function thisIsMatching() {
    // dummy function: if defined, an included script knows that it's included here
    return true;
}
function jsProcessUnmatchButton() {
    global $_MagnaSession;
    $div = '<div id="unmatchPopup"></div>'."\n";
    /*
     * deactivate all other checkboxes if "unprepare" checked
     * (must be done in 1 line and apostrophes + slashes escaped, otherwise it doesn't work in jDialog)
     */
    ob_start();?> <script type="text\/javascript">/*<![CDATA[*/ $(\'input[id="unprepare"]\').change(function() { if ($(this).attr(\'checked\') == \'checked\') { $(\'input[id="resetEPID"]\').prop(\'disabled\', true); } else { $(\'input[id="resetEPID"]\').prop(\'disabled\', false); } }); /*]]>*/<\/script> <?php
        $popupJs = ob_get_contents();
        ob_end_clean();
        /* checkboxes */
        $popupContent =
            '<form id="resetMatchForm" action="magnalister.php?mp='.$_MagnaSession['mpID'].'&amp;mode=prepare&amp;view=match" method="post">'
            .'<input type="checkbox" value="resetEPID" name="action[ebaymatchingformaction][resetEPID]" id="resetEPID" />'.ML_EBAY_LABEL_RESET_PREPARE_EPID.'<br />'
            .'<br /><input type="checkbox" value="unprepare" name="action[ebaymatchingformaction][unprepare]" id="unprepare" />'.ML_EBAY_LABEL_UNPREPARE.'<br />'
            .'</form>';
        $popupContent .= $popupJs;

        /* jDialog call */
        ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#unmatchPopup').html('<?php echo $popupContent; ?>').jDialog({
		title: '<?php echo ML_BUTTON_LABEL_REVERT_FOR_SELECTION ?>',
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				jQuery(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				$('#resetMatchForm').submit();
				jQuery(this).dialog('close');
			}
		}
	});
	jQuery($('#unmatchPopup')).dialog('close');
});
$('#unmatch_partly').click(function() {
	// works also without the open / close stuff (just jDialog on click), but so we have an example how to call the window afterwards
	jQuery($('#unmatchPopup')).dialog('open');
});
/*]]>*/</script>
    <?php
    $js = ob_get_contents();
    ob_end_clean();
    return ($div.$js);
}

$_url['view'] = 'match';
$matchingSetting = array(
	'selectionName' => 'matching'
);

$matchAction = 'categoryview';

if (array_key_exists('PreparedTS', $_POST)) {
	$_MagnaSession['ebayLastPreparedTS'] = $_POST['PreparedTS'];
}
/**
 * Save and organize Multimatching
 */
if (array_key_exists('action', $_POST) && ($_POST['action'] == 'multimatching')) {
	include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/saveMatching.php');
	if (ctype_digit($_POST['matching_nextpage'])) {
		/* Noch nicht mit matching fertig */
		$matchAction = 'multimatching';
	} else {
            $selection = MagnaDB::gi()->fetchArray("
                SELECT * 
                  FROM ".TABLE_MAGNA_SELECTION."
                 WHERE     mpID = '".$_MagnaSession['mpID']."'
                       AND selectionname = '".$matchingSetting['selectionName']."'
                       AND session_id = '".session_id()."'
            ");
            if (empty($selection)) {
                unset($_POST['action']);
                $_POST['prepare'] = 'matching';
                require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/EbayMatchingProductList.php');
                $o = new EbayMatchingProductList();
                echo $o . jsProcessUnmatchButton();
                return;
            }
            foreach ($selection as &$select) {
                $select['selectionname'] = 'prepare';
            }
            MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                'mpID' => $_MagnaSession['mpID'],
                'selectionname' => 'prepare',
                'session_id' => session_id()
            ));
            MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $selection);

	    unset($_POST['action']);
            $_POST['prepare'] = 'matching';

            global $IsMatching;
            $IsMatching = true;

            include_once(DIR_MAGNALISTER_MODULES.'ebay/prepare.php');
            /* Daten loswerden */
            MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
            	'mpID' => $_MagnaSession['mpID'],
            	'selectionname' => $matchingSetting['selectionName'],
            	'session_id' => session_id()
            ));
            unset($_MagnaSession['ebay']['multimatching']);
            return;
	}
}

/**
 * Save Singlematching
 */
if (array_key_exists('action', $_POST) && ($_POST['action'] == 'singlematching')) {
    include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/saveMatching.php');
    $selection = MagnaDB::gi()->fetchArray("
        SELECT * 
          FROM ".TABLE_MAGNA_SELECTION."
         WHERE     mpID = '".$_MagnaSession['mpID']."'
               AND selectionname = '".$matchingSetting['selectionName']."'
               AND session_id = '".session_id()."'
    ");
    if (empty($selection)) {
        unset($_POST['action']);
        $_POST['prepare'] = 'matching';
        require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/EbayMatchingProductList.php');
        $o = new EbayMatchingProductList();
        echo $o . jsProcessUnmatchButton();
        return;
    }
    foreach ($selection as &$select) {
        $select['selectionname'] = 'prepare';
    }
    MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
        'mpID' => $_MagnaSession['mpID'],
        'selectionname' => 'prepare',
        'session_id' => session_id()
    ));
    MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $selection);
    unset($_POST['action']);
    $_POST['prepare'] = 'matching';

            global $IsMatching;
            $IsMatching = true;

    include_once(DIR_MAGNALISTER_MODULES.'ebay/prepare.php');
	/* Daten loswerden */
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $matchingSetting['selectionName'],
		'session_id' => session_id()
	));

    return;
}

if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST !== true ) {// will be done in MLProductListDependencyEbayMatchingFormAction, das hier kamma weglasse
	/**
	 * Daten loeschen
	 */
	if (array_key_exists('unmatching', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$matchingSetting['selectionName'].'\' AND
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
					'selectionname' => $matchingSetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}
}

/**
 * Matching Vorbereitung
 */
if (array_key_exists('matching', $_POST) && (!empty($_POST['matching'])) && ($matchAction != 'multimatching')) {
	$itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*) 
		  FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
		       selectionname=\''.$matchingSetting['selectionName'].'\' AND
		       session_id=\''.session_id().'\'
	  GROUP BY selectionname
	');

	if ($itemCount == 1) {
		$matchAction = 'singlematching';
	} else if ($itemCount > 1) {
		$matchAction = 'multimatching';
	}
}

if ($matchAction == 'singlematching') {
	include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/singlematching.php');
	
} else if ($matchAction == 'multimatching') {
	include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/multimatching.php');
	
} else {
	if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
		require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/EbayMatchingProductList.php');
		$o = new EbayMatchingProductList();
		echo $o . jsProcessUnmatchButton();
	} else {
		require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCategoryView.php');

		$aCV = new eBayCategoryView(
			$current_category_id, $matchingSetting,  /* $current_category_id is a global variable from xt:Commerce */
			(isset($_GET['sorting']) ? $_GET['sorting'] : ''),
			(isset($_POST['tfSearch']) ? $_POST['tfSearch'] : '')
		);
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			echo $aCV->renderAjaxReply();
		} else {
			echo $aCV->printForm();
		}
	}
}
/*
echo print_m($_MagnaSession, '$_MagnaSession');
echo print_m($_POST, '$_POST');
*/
