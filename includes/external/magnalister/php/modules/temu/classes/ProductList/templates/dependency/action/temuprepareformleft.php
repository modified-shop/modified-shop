<?php
/**
 * Temu prepare form left - Unprepare button
 */
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
    <input type="submit" class="ml-button" value="<?php echo ML_EBAY_BUTTON_UNPREPARE; ?>"
           id="unprepare" name="unprepare"/>
</form>
