<div class="bpy-description-text">
    {$campaignText}
</div>
<div class="bpy-loading-indicator">
    <img class="bpy-loading" src="{$image_loading}" alt="loading"/>
    <img class="bpy-loading-ok" id="bpy_loading_ok" src="{$image_ok}" alt="Ok"/>
</div>
<style type="text/css">
    {literal}
    .bpy-description-text {
        padding: 10px 15px;
    }
    .bpy-loading-indicator {
        position: relative;
        height: 20px;
        margin-top: 30px;
    }
    .bpy-loading-indicator .bpy-loading {
        position: absolute;
        width: 208px;
        height: 13px;
        margin: 0 50%;
        left: -104px;
    }
    .bpy-loading-indicator .bpy-loading-ok {
        position: absolute;
        width: 11px;
        height: 12px;
        margin: 0 50%;
        left: 106px;
        display: none;
    }
    {/literal}
</style>
<script type="text/javascript">
    var refreshUrl = "{$refresh_url}";
    var approveInterval = window.setInterval(bpyCheckForApprove, 2000);

    {literal}
    var XMLHttpFactories = [
        function () {return new XMLHttpRequest()},
        function () {return new ActiveXObject("Msxml2.XMLHTTP")},
        function () {return new ActiveXObject("Msxml3.XMLHTTP")},
        function () {return new ActiveXObject("Microsoft.XMLHTTP")}
    ];

    function bpyCreateAjaxObject() {
        var xmlhttp = false;
        for (var i=0;i<XMLHttpFactories.length;i++) {
            try {
                xmlhttp = XMLHttpFactories[i]();
            } catch (e) {
                continue;
            }
            break;
        }
        return xmlhttp;
    }

    function bpyCheckForApprove() {
        var req = bpyCreateAjaxObject();
        if (!req) return;

        req.open('GET', refreshUrl + '?ajaxCall=1');
        req.onreadystatechange = function() {
            if (req.readyState != 4) return;
            if (req.status != 200 && req.status != 304) return;

            var response = JSON.parse(req.responseText);
            if (response.redirectTarget) {
                document.getElementById('bpy_loading_ok').style.display = 'block';
                window.clearInterval(approveInterval);
                window.setTimeout(function() {
                    window.location.href = response.redirectTarget;
                }, 2000);
            }
        };
        if (req.readyState == 4) return;
        req.send();
    }
    {/literal}
</script>