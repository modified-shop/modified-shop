<div class="bpy-checkout">
    {$campaignText}<br />
    <img class="bpy-partner-logo" src="{$campaignImg}" alt="Billpay Partnerlogo"/>
    <div class="bpy-info-block">
        <dl>
            <dt>Kostenlos</dt>
            <dd>Keine zus&#228;tzlichen Geb&uuml;hren bei der Bezahlung mit giropay</dd>
            <dt>Einfach</dt>
            <dd>Direktes Bezahlen per Online-&Uuml;berweisung mit PIN/TAN-Verfahren</dd>
            <dt>Schnell</dt>
            <dd>Keine Extra-Anmeldung oder Registrierung erforderlich</dd>
            <dt>Sicher</dt>
            <dd>Sicheres und vertrautes Bezahlen im Online-Banink der eigenen Bank oder Sparkasse</dd>
        </dl>
    </div>
    <div class="bpy-rate-plan">
        Sehen Sie hier die angepassten <a href="{$rateLink}" target="_blank">Finanzierungsdetails</a>
        nach erfolgreicher Anzahlung.
    </div>
    <div class="bpy-button-bar {$button_container_class}">
        <a class="bpy-button bpy-back" href="{$backRedirect}">{$button_back_content}</a>
        <a class="bpy-button bpy-continue" href="{$externalRedirect}">{$button_continue_content}</a>
    </div>
    <br style="clear: both"/>
</div>
<style type="text/css">
    {literal}
    .bpy-checkout {
        margin: 30px 0;
        padding: 30px;
        background: #f4f4f4;
    }
    .bpy-checkout .bpy-partner-logo {
        width: 370px;
    }
    .bpy-checkout .bpy-info-block {
        margin-top: 5px;
    }
    .bpy-checkout .bpy-info-block dl dt {
        float: none;
        font-weight: bold;
    }
    .bpy-checkout .bpy-info-block dl dd {
        float: none;
        width: 100%;
        margin-left: 20px;
        margin-bottom: 10px;
        list-style-type: disc;
        display: list-item;
    }
    .bpy-checkout .bpy-rate-plan a {
        font-weight: bold;
    }
    .bpy-button-bar {
        float: left;
        width: 100%;
        height: 50px;
    }
    .bpy-checkout .bpy-button-bar .bpy-button {
        display: inline-block;
        height: 24px;
        width: 124px;
        margin: 10px;
        text-align: center;
        vertical-align: middle;
    }
    .bpy-checkout .bpy-button-bar .bpy-back {
        float: left;
    }
    .bpy-checkout .bpy-button-bar .bpy-continue {
        float: right;
    }
    .bpy-checkout .bpy-image-buttons .bpy-button {
        text-indent:  -100000px;
        overflow: hidden;
    }
    {/literal}
    .bpy-checkout .bpy-image-buttons .bpy-back {ldelim}
        background: url("{$button_back_image}") transparent;
        height: {$button_back_height};
        width: {$button_back_width};
    {rdelim}
    .bpy-checkout .bpy-image-buttons .bpy-continue {ldelim}
        background: url("{$button_continue_image}") transparent;
        height: {$button_continue_height};
        width: {$button_continue_width};
    {rdelim}
</style>