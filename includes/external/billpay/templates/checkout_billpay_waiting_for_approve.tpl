<div class="bpy-description-text">
    {if $language eq 'english'}
        We are waiting for your pre-payment. This page will refresh automatically.
    {elseif $language eq 'german'}
        Wir warten auf Ihre Vorauszahlung. Diese Seite wird automatisch aktualisiert.
    {else}
        We are waiting for your pre-payment. This page will refresh automatically.<br>
        Message in <strong>{$language}</strong> is not translated.
    {/if}
</div>
<script type="text/javascript">
    window.setTimeout(bpyCheckForApprove("{$refresh_url}"), 5000);
    {literal}
    function bpyCheckForApprove(url) {
        return function() {
            window.location.replace(url);
        }
    }
    {/literal}
</script>