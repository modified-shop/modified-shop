// Append optgroup label to selected Etsy Processing Profile option (prepare + config)
(function($){
    $(document).ready(function(){
        var selectors = [
            "select[name='processingprofile']",
            "select[name=\"ml[field][processingprofile]\"]",
            "select[name='conf[etsy.ProcessingProfile]']"
        ].join(',');

        var $processingProfileSelects = $(selectors);
        if (!$processingProfileSelects.length) { return; }

        // Store original texts once per option
        $processingProfileSelects.find('option').each(function(){
            var $opt = $(this);
            if (typeof $opt.data('origText') === 'undefined') {
                $opt.data('origText', $opt.text());
            }
        });

        var updateLabel = function($select){
            // reset all options to original text
            $select.find('option').each(function(){
                var $opt = $(this);
                var orig = $opt.data('origText');
                if (typeof orig !== 'undefined') {
                    $opt.text(orig);
                }
            });
            var $selected = $select.find('option:selected');
            if (!$selected.length) { return; }
            var val = $selected.val();
            if (val !== undefined && val !== null && val !== '' && val !== '0') {
                var groupLabel = $selected.parent('optgroup').attr('label');
                if (groupLabel && groupLabel.length) {
                    var baseText = $selected.data('origText') || $selected.text();
                    $selected.text(groupLabel + ': ' + baseText);
                }
            }
        };

        $processingProfileSelects.on('change', function(){ updateLabel($(this)); });
        $processingProfileSelects.each(function(){ updateLabel($(this)); });
    });
})(window.jqml || window.jQuery || window.$);
