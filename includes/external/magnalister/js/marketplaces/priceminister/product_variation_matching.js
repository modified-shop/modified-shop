(function($) {
    $.widget("ui.ml_product_variation_matching", $.ui.ml_variation_matching, {

        _initMainSelectElement: function() {
            var self = this;
            $('body').on('change', 'input:radio', function() {
                var me = $(this),
                    productId = me.attr('data-id'),
                    category = $('#category_' + me.attr('id')),
                    categoryId = category .attr('data-id');
                $('#match_category_id_' + productId).val(categoryId);
                $('#match_category_name_' + productId).val(category.attr('data-name'));
                $('#match_title_' + productId).val($('#title_' + me.attr('id')).attr('data-id'));
                $('#match_product_id_' + productId).val(me.val());

                if(me.val() == 'false' || !ml_vm_config.singleMatching) {
                    return;
                }

                $.blockUI(blockUILoading);
                $.ajax({
                    type: 'POST',
                    url: 'magnalister.php?mp=' + ml_vm_config.mpid + '&kind=ajax',
                    dataType: 'html',
                    data: ({
                        request: 'AdvertAttrForCategory',
                        'productID': productId,
                        'category_id': categoryId
                    }),
                    success: function(data) {
                        $('.attributematching').html('');
                        $('#attributematching_' + me.val()).html(data);
                        $('#PrimaryCategory').val(categoryId);
                        if(categoryId != null && categoryId !== '' && categoryId != 'null') {
                            self._loadMPVariation(categoryId);

                            self.elements.matchingHeadline = $(ml_vm_config.elements.matchingHeadline);
                            self.elements.matchingHeadline.css('display', 'table-row-group');

                            self.elements.matchingOptionalHeadline = $(ml_vm_config.elements.matchingOptionalHeadline);
                            self.elements.matchingOptionalHeadline.css('display', 'table-row-group');

                            self.elements.matchingCustomHeadline = $(ml_vm_config.elements.matchingCustomHeadline);
                            self.elements.matchingCustomHeadline.css('display', 'table-row-group');

                            self.elements.matchingInput = $(ml_vm_config.elements.matchingInput);
                            self.elements.matchingInput.css('display', 'table-row-group');

                            self.elements.matchingOptionalInput = $(ml_vm_config.elements.matchingOptionalInput);
                            self.elements.matchingOptionalInput.css('display', 'table-row-group');

                            self.elements.matchingCustomInput = $(ml_vm_config.elements.matchingCustomInput);
                            self.elements.matchingCustomInput.css('display', 'table-row-group');

                            self.elements.categoryInfo = $(ml_vm_config.elements.categoryInfo);
                            self.elements.categoryInfo.css('display', 'block');
                        }
                        $.unblockUI();
                    },
                    error: function() {
                        alert(self.options.i18n.ajaxError);
                        $.unblockUI();
                        self._resetMPVariation();
                    }

                });
            });
        },

        _getAppropriateAttributeMatchingTableRowClass: function() {
            return "key";
        }
    });
})(jQuery);
