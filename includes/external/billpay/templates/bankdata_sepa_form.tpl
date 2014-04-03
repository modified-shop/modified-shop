<div class="bpy-bank-data-form">
    <div style="margin: 10px 3px 3px">{$headline}</div>
    <table style="margin-bottom:5px">
        <tr>
            <td>{$account_holder_text}</td>
            <td>
                {$account_holder_input}
                <span class="inputRequirement">&nbsp;*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td>
                {$account_number_text}
                <i class="bpy-btn-sepa-info-popup" data-popup-target="auto">i</i>
            </td>
            <td>
                {$account_number_input}
                <span class="inputRequirement">&nbsp;*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td>
                {$bank_code_text}
                <i class="bpy-btn-sepa-info-popup" data-popup-target="auto">i</i>
            </td>
            <td>
                {$bank_code_input}
            </td>
        </tr>
    </table>
</div>