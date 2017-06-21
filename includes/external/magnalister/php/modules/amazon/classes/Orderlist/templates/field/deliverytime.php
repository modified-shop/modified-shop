<?php

/* @var $this  ML_Amazon_Controller_Amazon_ShippingLabel_Orderlist */
/* @var $oList ML_Amazon_Model_List_Amazon_Order */
class_exists('MLOrderlistAmazonAbstract', false) or die();
?>
<td>
    <?php echo $aRow['EarliestEstimatedDeliveryDate'] . ', ' . $aRow['LatestEstimatedDeliveryDate']; ?>
</td>


