<?php
/**
 * Temu prepare form left - Unprepare button
 */
/* @var $this MLProductList */
/* @var $oObject MLProductListDependency */
class_exists('MLProductList') or die();
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
    <input type="submit" class="ml-button" value="<?php echo ML_EBAY_BUTTON_UNPREPARE; ?>"
           id="unprepare" name="action[<?php echo $oObject->getIdent(); ?>][unprepare]"/>
</form>
