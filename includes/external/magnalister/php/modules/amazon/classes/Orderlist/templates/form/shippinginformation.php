<?php
/* @var $this  ML_Amazon_Controller_Amazon_ShippingLabel_Orderlist */
/* @var $oList ML_Amazon_Model_List_Amazon_Order */
class_exists('MLOrderlistAmazonAbstract', false) or die();
?>
<tbody class="even ml-shippinglabel-form ml-shippinglabel-form-upload" id="orderlist-<?php echo $aOrder['MPSpecific']['MOrderID'] ?>">
    <tr>
        <td colspan="6">
            <table class="fullWidth">
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="2"><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_SHIPPING_INFORMATION_LABEL ?>:</td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_PACKAGE_SIZE_LABEL ?>:</td>
                                    <td>
                                        <?php
                                        $sIdent = $aOrder['MPSpecific']['MOrderID'];
                                        $sHtmlId = str_replace(array('[', ']'), '_', $sIdent);
                                        $aDefaultDimension = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.default.dimension', $this->aMagnaSession['mpID']);
                                        $aText = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.default.dimension.text', $this->aMagnaSession['mpID']);
                                        $aLength = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.default.dimension.length', $this->aMagnaSession['mpID']);
                                        $aWidth = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.default.dimension.width', $this->aMagnaSession['mpID']);
                                        $aHeight = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.default.dimension.height', $this->aMagnaSession['mpID']);
                                        $fLength = 0;
                                        $fWidth = 0;
                                        $fHeight = 0;
                                        ?>
                                        <select class="ml-shippinglabel-configshipping ml-js-noBlockUi" id="<?php echo $sHtmlId ?>">
                                            <?php
                                            $sSizeUnit = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.size.unit', $this->aMagnaSession['mpID']);
                                            $sSizeUnit = ($sSizeUnit == 'centimeters' ? 'cm' : ($sSizeUnit == 'inches' ? 'in' : ''));
                                            foreach ($aDefaultDimension['defaults'] as $iKey => $sValue) {
                                                if ($aDefaultDimension['defaults'][$iKey] == '1' ? 'selected=selected' : '') {
                                                    $fLength = $aLength[$iKey];
                                                    $fWidth = $aWidth[$iKey];
                                                    $fHeight = $aHeight[$iKey];
                                                }
                                                ?>
                                                <option <?php echo $aDefaultDimension['defaults'][$iKey] == '1' ? 'selected=selected' : '' ?> value="<?php echo $aLength[$iKey] . '-' . $aWidth[$iKey] . '-' . $aHeight[$iKey] ?>">
                                                    <?php echo $aText[$iKey] . ' (' . $aLength[$iKey] . ' ' . $sSizeUnit . ' x ' . $aWidth[$iKey] . ' ' . $sSizeUnit . ' x ' . $aHeight[$iKey] . ' ' . $sSizeUnit . ')'; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_PACKAGE_DIMENSION_LABEL ?>:</td>
                                    <td>
                                        <table>
                                            <tr>
                                                <td class="normal"><label for="<?php echo $sHtmlId . 'length' ?>"><?php echo ML_AMAZON_SHIPPINGLABEL_PACKAGE_LENGTH ?></label></td><td>:</td><td><input class="ml-shippinglabel-size" id="<?php echo $sHtmlId . 'length' ?>" type="text" name="<?php echo 'length[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" value="<?php echo $fLength ?>"/></td><td><?php echo $sSizeUnit ?></td><td>&nbsp;&nbsp;</td>
                                                <td class="normal"><label for="<?php echo $sHtmlId . 'width' ?>"><?php echo ML_AMAZON_SHIPPINGLABEL_PACKAGE_WIDTH ?></label></td><td>:</td><td><input class="ml-shippinglabel-size" type="text" id="<?php echo $sHtmlId . 'width' ?>" name="<?php echo 'width[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" value="<?php echo $fWidth ?>"/></td><td><?php echo $sSizeUnit ?></td><td>&nbsp;&nbsp;</td>
                                                <td class="normal"><label for="<?php echo $sHtmlId . 'height' ?>"><?php echo ML_AMAZON_SHIPPINGLABEL_PACKAGE_HEIGHT ?></label></td><td>:</td><td><input class="ml-shippinglabel-size" type="text" id="<?php echo $sHtmlId . 'height' ?>" name="<?php echo 'height[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" value="<?php echo $fHeight ?>"/></td><td><?php echo $sSizeUnit ?></td><td>&nbsp;&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_GENERIC_WEIGHT ?>:</td>
                                    <td>
                                        <input type="text" class="ml-shippinglabel-size ml-shippinglabel-weight-<?php echo $aOrder['MPSpecific']['MOrderID'] ?>" name="<?php echo 'weight[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" value="<?php echo  $aOrder['TotalWeight'] ?>"/> <span class="normal"><?php echo getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.weight.unit', $this->aMagnaSession['mpID']) ?></span>
                                        <span class="infoTextGray"><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_WEIGHT_NOTICE ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo ML_LABEL_SHIPPING_DATE ?>:</td>
                                    <td>
                                        <select name="<?php echo 'date[' . $aOrder['MPSpecific']['MOrderID'] . ']'; ?>" class="ml-js-noBlockUi">
                                            <?php
                                            foreach (array(
                                                date('d.m.Y', time()),
                                                date('d.m.Y', time() + 24 * 60 * 60)
                                             ) as $sDate) {
                                                ?>
                                                <option value="<?php echo $sDate ?>"><?php echo $sDate ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="input">
                                    <label ><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_PACKAGE_CARRIERWILLPICKUP_LABEL ?>:</label>
                                </td>
                                <td class="normal">
                                    <?php
                                    $aService = amazonMfsGetConfigurationValues('ServiceOptions');
                                    $aOptions = array_key_exists('CarrierWillPickUp', $aService) ? $aService['CarrierWillPickUp'] : array();
                                    $sSelected = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippingservice.carrierwillpickup', $this->aMagnaSession['mpID']);
                                    foreach ($aOptions as $sKey => $sValue) {
                                        ?>
                                        <input type="radio" <?php echo $sSelected == $sKey ? 'checked=checked' : '' ?> name="<?php echo 'carrierwillpickup[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" value="<?php echo $sKey ?>" id="amazon_config_shippinglabel_<?php echo $sKey ?>">
                                        <label for="amazon_config_shippinglabel_<?php echo $sKey ?>"><?php echo $sValue ?></label>
                                    <?php } ?>
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <td class="input">
                                    <label><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_PACKAGE_DELIVERYEXPIRIENCE_LABEL ?>:</label>
                                </td>
                                <td>
                                    <select name="<?php echo 'deliveryexpirience[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" class="ml-js-noBlockUi" >
                                        <?php
                                        $aOptions = array_key_exists('DeliveryExperience', $aService) ? $aService['DeliveryExperience'] : array();
                                        $sSelected = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippingservice.deliveryexpirience', $this->aMagnaSession['mpID']);
                                        foreach ($aOptions as $sKey => $sValue) {
                                            ?>
                                            <option <?php echo $sSelected == $sKey ? 'selected=selected' : '' ?> value="<?php echo $sKey ?>"> <?php echo fixHTMLUTF8Entities($sValue) ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo ML_AMAZON_SHIPPINGLABEL_FORM_PACKAGE_SENDERADDRESS_LABEL ?>:</td>
                                <td>
                                    <?php
                                    $aDefaultAddress = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.address', $this->aMagnaSession['mpID']);
                                    $aStreet = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.address.streetandnr', $this->aMagnaSession['mpID']);
                                    $aZip = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.address.zip', $this->aMagnaSession['mpID']);
                                    $aCity = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.address.city', $this->aMagnaSession['mpID']);
                                    ?>
                                    <select name="<?php echo 'addressfrom[' . $aOrder['MPSpecific']['MOrderID'] . ']' ?>" class="ml-js-noBlockUi">
                                        <?php
                                        foreach ($aDefaultAddress['defaults'] as $iKey => $sValue) {
                                            ?>
                                            <option <?php echo $sValue == '1' ? 'selected=selected' : '' ?> value="<?php echo $iKey ?>">
                                                <?php echo $aStreet[$iKey] . ' - ' . $aZip[$iKey] . ' - ' . $aCity[$iKey]; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</tbody>
