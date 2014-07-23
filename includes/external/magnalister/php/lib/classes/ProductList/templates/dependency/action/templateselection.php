<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
/* @var $this MLProductList */
/* @var $oObject MLProductListDependencyAction */
class_exists('MLProductList') or die();
                                    $aTemplates = $oObject->getTemplates();
?>
<form action="<?php echo $this->getUrl(true, true, true); ?>" method="post" onchange="this.submit();" id="<?php echo $oObject->getIdent(); ?>">
<div>
            <select class="n" name="selectTemplate">
                        <option value="-1" ><?php echo  empty($aTemplates['list'])?  ML_LABEL_NO_TEMPLATES_YET:ML_LABEL_USE_TEMPLATE ; ?></option>
                        <?php foreach($aTemplates['list'] as $aRow) { ?>
                                    <option value="<?php echo $aRow['tID']; ?>"<?php echo (isset($aTemplates['selected']['tID']) && $aTemplates['selected']['tID'] == $aRow['tID'])  ? ' selected="selected"' : ''; ?>><?php echo $aRow['title']; ?></option>
                        <?php } ?>
            </select>
            <a title="Vorlagen verwalten" href="<?php echo $this->getUrl(true, true, true,array('view'=>'administrate')); ?>" class="n gfxbutton medium border visiblebg cog valignbottom" id="editTemplates"></a>
</div></form>

<div id="templateInfoDiag" class="dialog2" title="<?php echo ML_LABEL_INFORMATION; ?>"><?php echo ML_TEXT_TEMPLATE_INFO; ?></div>
<script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function() {
                        $('#template_info').click(function() {
                                    $('#templateInfoDiag').jDialog();
                        });
            });
/*]]>*/</script>

