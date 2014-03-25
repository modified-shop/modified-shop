<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { // TABS/ACCORDION in product_info ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion           
        });
        
        $('#horizontalAccordion').easyResponsiveTabs({
            type: 'accordion', //Types: default, vertical, accordion           
        });
    });
</script>
<?php } ?>

<?php if (strstr($PHP_SELF, 'checkout')) { ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#horizontalAccordion').easyResponsiveTabs({
            type: 'accordion', //Types: default, vertical, accordion     
            closed: true,     
            activate: function(event) { // Callback function if tab is switched
               $(".resp-tab-active input[type=radio]").prop('checked', true);
            }
        });
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion           
        });
    });
</script>
<?php } ?>