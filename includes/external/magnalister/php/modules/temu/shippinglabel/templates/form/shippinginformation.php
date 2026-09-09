<?php
/* @var $this TemuShippingLabelUploadForm */
class_exists('MLOrderlistTemuAbstract', false) or die();

$sMOrderID = isset($aOrder['MPSpecific']['MOrderID'])
    ? $aOrder['MPSpecific']['MOrderID']
    : (isset($aOrder['MOrderID']) ? $aOrder['MOrderID'] : '');
$sHtmlId = str_replace(array('[', ']', '-'), '_', $sMOrderID);
?>
<tbody class="even ml-shippinglabel-form ml-shippinglabel-form-upload" id="orderlist-<?php echo htmlspecialchars($sMOrderID); ?>">
    <tr>
        <td colspan="6">
            <table class="fullWidth">
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><?php echo ML_TEMU_SHIPPINGLABEL_FORM_LENGTH; ?>:</td>
                                    <td>
                                        <input class="ml-shippinglabel-size" type="text"
                                               name="<?php echo 'length[' . $sMOrderID . ']'; ?>"
                                               value="<?php echo $aOrder['DefaultLength']; ?>"/>
                                    </td>
                                    <td>&nbsp;&nbsp;</td>
                                    <td><?php echo ML_TEMU_SHIPPINGLABEL_FORM_WIDTH; ?>:</td>
                                    <td>
                                        <input class="ml-shippinglabel-size" type="text"
                                               name="<?php echo 'width[' . $sMOrderID . ']'; ?>"
                                               value="<?php echo $aOrder['DefaultWidth']; ?>"/>
                                    </td>
                                    <td>&nbsp;&nbsp;</td>
                                    <td><?php echo ML_TEMU_SHIPPINGLABEL_FORM_HEIGHT; ?>:</td>
                                    <td>
                                        <input class="ml-shippinglabel-size" type="text"
                                               name="<?php echo 'height[' . $sMOrderID . ']'; ?>"
                                               value="<?php echo $aOrder['DefaultHeight']; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_TEMU_SHIPPINGLABEL_FORM_WEIGHT; ?>:</td>
                                    <td colspan="7">
                                        <input type="text" class="ml-shippinglabel-size ml-shippinglabel-weight-<?php echo $sMOrderID; ?>"
                                               name="<?php echo 'weight[' . $sMOrderID . ']'; ?>"
                                               value="<?php echo $aOrder['TotalWeight']; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_TEMU_SHIPPINGLABEL_FORM_SHIPPINGDATE; ?>:</td>
                                    <td colspan="7">
                                        <select name="<?php echo 'date[' . $sMOrderID . ']'; ?>" class="ml-js-noBlockUi">
                                            <?php
                                            for ($i = 0; $i <= 3; $i++) {
                                                $sDate = date('d.m.Y', time() + $i * 24 * 60 * 60);
                                                echo '<option value="' . $sDate . '">' . $sDate . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</tbody>
