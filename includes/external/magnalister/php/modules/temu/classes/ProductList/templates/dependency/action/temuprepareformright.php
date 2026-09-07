<?php
/**
 * Temu prepare form right - Prepare submit button
 */
?>
<form action="<?php echo $this->getUrl(false, false, false); ?>" method="post">
    <input type="hidden" name="selectionName" value="prepare">
    <input type="hidden" id="actionType" value="_">
    <table class="right">
        <tbody>
        <tr>
            <td>
                <input type="submit" name="prepare" id="prepare"
                       value="<?php echo ML_EBAY_LABEL_PREPARE; ?>"
                       class="fullWidth ml-button smallmargin mlbtn-action"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>
