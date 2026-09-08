<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/**
 * Renders the "Product Data" section of the Temu Product Preparation screen,
 * following the OTTO prepare layout (3-column table: label / input / info).
 *
 * Editable + persisted: Title, Description (WYSIWYG).
 * Read-only:            EAN, SKU, Price.
 * Images:               checkbox include/exclude list (Temu has no main-image concept).
 *
 * Saved values are read from magnalister_temu_prepare when a row exists, else the
 * shop product (MLProduct) is used. EAN/SKU/Price always come from the shop product.
 */
class TemuProductDataView {

    /** Formatting tags kept in the description (display + save). */
    const DESCRIPTION_ALLOWED_TAGS = '<b><strong><i><em><u><br><p><ul><ol><li>';

    protected $mpID;
    protected $aPIDs;
    protected $iLang;

    public function __construct($mpID, $aPIDs) {
        $this->mpID  = (int)$mpID;
        $this->aPIDs = array_map('intval', (array)$aPIDs);
        $this->iLang = getDBConfigValue('temu.lang', $this->mpID,
            isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : 2);
    }

    /**
     * Strip the description down to the allowed formatting tags. Single source of the
     * whitelist; applied on both display and save (defense-in-depth, mitigates XSS).
     */
    public static function sanitizeDescription($sHtml) {
        return strip_tags((string)$sHtml, self::DESCRIPTION_ALLOWED_TAGS);
    }

    /** i18n helper: constant if defined, else fallback (matches existing temu code style). */
    protected function t($sConst, $sFallback) {
        return defined($sConst) ? constant($sConst) : $sFallback;
    }

    public function render() {
        if (empty($this->aPIDs)) {
            return '';
        }
        $sHtml = '<table class="attributesTable">';
        foreach ($this->aPIDs as $pID) {
            $sHtml .= $this->renderProductBlock($pID);
        }
        $sHtml .= '</table>';
        return $sHtml;
    }

    protected function loadSavedRow($pID) {
        $aRow = MagnaDB::gi()->fetchRow("
            SELECT Title, Description, Images
              FROM ".TABLE_MAGNA_TEMU_PREPARE."
             WHERE mpID = ".(int)$this->mpID."
               AND products_id = ".(int)$pID."
        ");
        return is_array($aRow) ? $aRow : null;
    }

    protected function renderProductBlock($pID) {
        $aProduct = MLProduct::gi()->setLanguage($this->iLang)->getProductById((int)$pID);
        if (!is_array($aProduct)) {
            return '';
        }
        $aSaved = $this->loadSavedRow($pID);

        // Title / Description: saved value wins, else shop product.
        $sTitle = ($aSaved !== null && $aSaved['Title'] !== '' && $aSaved['Title'] !== null)
            ? $aSaved['Title'] : (isset($aProduct['Title']) ? $aProduct['Title'] : '');
        $sDesc = ($aSaved !== null && $aSaved['Description'] !== '' && $aSaved['Description'] !== null)
            ? $aSaved['Description'] : (isset($aProduct['Description']) ? $aProduct['Description'] : '');
        $sDesc = self::sanitizeDescription($sDesc);

        // Read-only fields always from the shop product.
        $sEAN   = isset($aProduct['EAN']) ? $aProduct['EAN'] : '';
        $sSKU   = isset($aProduct['ProductsModel']) ? $aProduct['ProductsModel'] : '';
        $sPrice = isset($aProduct['Price']) && $aProduct['Price'] !== '' ? $aProduct['Price'] : '';
        $sCurr  = isset($aProduct['Currency']) ? $aProduct['Currency'] : '';
        $sPriceDisplay = ($sPrice === '') ? '&mdash;'
            : htmlspecialchars($sPrice.($sCurr !== '' ? ' '.$sCurr : ''), ENT_QUOTES);

        // Images: all shop images; checked when in the saved subset (or all when none saved).
        $aAllImages = (isset($aProduct['Images']) && is_array($aProduct['Images'])) ? $aProduct['Images'] : array();
        $aSavedImages = null;
        if ($aSaved !== null && !empty($aSaved['Images'])) {
            $aDecoded = json_decode($aSaved['Images'], true);
            if (is_array($aDecoded) && !empty($aDecoded)) {
                $aSavedImages = $aDecoded;
            }
        }

        $sName   = 'temu_prepare['.(int)$pID.']';
        $sDescId = 'temu_desc_'.(int)$pID;
        $oddEven = false;

        $s  = '<tbody>';
        $s .= '<tr class="headline"><td colspan="3"><h4>'
            .$this->t('ML_TEMU_LABEL_PRODUCT_DATA', 'Product data')
            .' &mdash; '.fixHTMLUTF8Entities(isset($aProduct['Title']) ? $aProduct['Title'] : '', ENT_COMPAT)
            .' (ID: '.(int)$pID.')</h4></td></tr>';

        // Title (editable)
        $s .= '<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">'
            .'<th>'.$this->t('ML_TEMU_LABEL_TITLE', 'Title').'</th>'
            .'<td class="input"><input type="text" class="fullwidth" maxlength="255" '
            .'name="'.$sName.'[Title]" value="'.htmlspecialchars($sTitle, ENT_QUOTES).'" /></td>'
            .'<td class="info"></td></tr>';

        // Description (WYSIWYG editable)
        $s .= '<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">'
            .'<th>'.$this->t('ML_TEMU_LABEL_DESCRIPTION', 'Description').'</th>'
            .'<td class="input">'.magna_wysiwyg(array(
                'id'    => $sDescId,
                'name'  => $sName.'[Description]',
                'class' => 'fullwidth',
                'cols'  => '80',
                'rows'  => '20',
                'wrap'  => 'virtual',
            ), fixHTMLUTF8Entities($sDesc, ENT_COMPAT)).'</td>'
            .'<td class="info"></td></tr>';

        // EAN / SKU / Price (read-only)
        $s .= $this->readonlyRow($this->t('ML_TEMU_LABEL_EAN', 'EAN'),
            htmlspecialchars($sEAN, ENT_QUOTES), ($oddEven = !$oddEven));
        $s .= $this->readonlyRow($this->t('ML_TEMU_LABEL_SKU', 'SKU'),
            htmlspecialchars($sSKU, ENT_QUOTES), ($oddEven = !$oddEven));
        $s .= $this->readonlyRow($this->t('ML_TEMU_LABEL_PRICE', 'Price'),
            $sPriceDisplay, ($oddEven = !$oddEven));

        // Images (checkbox include/exclude — no main image)
        $s .= '<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">'
            .'<th>'.$this->t('ML_TEMU_LABEL_IMAGES', 'Images').'</th>'
            .'<td class="input">'.$this->renderImages($sName, $aAllImages, $aSavedImages).'</td>'
            .'<td class="info"></td></tr>';

        $s .= '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
        $s .= '</tbody>';
        return $s;
    }

    protected function readonlyRow($sLabel, $sValueHtml, $bEven) {
        return '<tr class="'.($bEven ? 'odd' : 'even').'">'
            .'<th>'.$sLabel.'</th>'
            .'<td>'.($sValueHtml === '' ? '&mdash;' : $sValueHtml).'</td>'
            .'<td class="info"></td></tr>';
    }

    protected function renderImages($sName, $aAllImages, $aSavedImages) {
        if (empty($aAllImages)) {
            return '&mdash;';
        }
        $sHtml = '';
        foreach ($aAllImages as $sImg) {
            $sEsc = htmlspecialchars($sImg, ENT_QUOTES);
            $bChecked = ($aSavedImages === null) ? true : in_array($sImg, $aSavedImages, true);
            $sHtml .= '<label class="ml-prepare-image" style="display:inline-block;margin:4px;text-align:center;vertical-align:top;">'
                .generateProductCategoryThumb($sImg, 60, 60).'<br />'
                .'<input type="checkbox" name="'.$sName.'[Images][]" value="'.$sEsc.'" '
                .($bChecked ? 'checked="checked"' : '').' /></label>';
        }
        return $sHtml;
    }
}
