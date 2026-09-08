<?php
/**
 * Temu apply form left - Remove and Reset buttons
 */
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
    <input type="submit" class="ml-button" value="<?php echo ML_BUTTON_LABEL_DELETE; ?>"
           id="removeapply" name="removeapply"/>
    <input type="submit" class="ml-button" value="<?php echo ML_BUTTON_LABEL_RESET; ?>"
           id="resetapply" name="resetapply"/>
</form>
