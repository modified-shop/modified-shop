/* jQuery Version 1.00 */

function email_popup () {
    var form = $("form[name='status']");

    //var checkbox_notify = $("input[name='notify']");
    var email_preview = $("input[name='email_preview']");
    //if (checkbox_notify.is(':checked')) {
        form.attr('target', 'emailPreview');
        email_preview.val('1');
        var w = window.open(form.attr('action'), 'emailPreview', 'width=700,height=800,resizable=yes,scrollbars=yes,left=100,top=50');

        form.submit(function() {
            return w;
        });
        form.submit();
        email_preview.val('');
        form.attr('target', '');        
    //}
}