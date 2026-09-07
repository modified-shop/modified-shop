<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MLProductListDependencyTemuPrepareFormAction extends MLProductListDependency {

    public function getActionBottomLeftTemplate() {
        return 'temuprepareformleft';
    }

    public function getActionBottomRightTemplate() {
        return 'temuprepareformright';
    }

    public function getDefaultConfig() {
        return array('selectionname' => 'general');
    }

    public function executeAction() {
        if (isset($_POST['unprepare'])) {
            $this->unprepare();
        }
        return $this;
    }

    protected function unprepare() {
        global $_MagnaSession;
        $mpID = $_MagnaSession['mpID'];
        $aSelection = $this->getSelection();
        if (empty($aSelection)) {
            return;
        }
        $sIds = MagnaDB::gi()->escape(implode(',', $aSelection));

        // Delete longtext entries first (foreign key reference)
        MagnaDB::gi()->query('
            DELETE lt FROM ' . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . ' lt
            INNER JOIN ' . TABLE_MAGNA_TEMU_PREPARE . ' p ON lt.TextId = p.ShopVariationId
            WHERE p.mpID = ' . $mpID . '
              AND p.ProductsID IN (' . $sIds . ')
        ');

        // Delete prepare entries
        MagnaDB::gi()->query('
            DELETE FROM ' . TABLE_MAGNA_TEMU_PREPARE . '
            WHERE mpID = ' . $mpID . '
              AND ProductsID IN (' . $sIds . ')
        ');

        // Clear selection
        $this->clearSelection();
    }
}
