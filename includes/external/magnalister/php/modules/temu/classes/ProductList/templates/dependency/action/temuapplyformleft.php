<?php
/**
 * Temu apply form left - Remove and Reset buttons
 */
/* @var $this MLProductList */
/* @var $oObject MLProductListDependency */
class_exists('MLProductList') or die();
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
    <input type="submit" class="ml-button" value="<?php echo ML_BUTTON_LABEL_DELETE; ?>"
           id="removeapply" name="action[<?php echo $oObject->getIdent(); ?>][removeapply]"/>
    <input type="submit" class="ml-button" value="<?php echo ML_BUTTON_LABEL_RESET; ?>"
           id="resetapply" name="action[<?php echo $oObject->getIdent(); ?>][resetapply]"/>
</form>
