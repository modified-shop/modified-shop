<div style="font-size: {$misc.font_size.big}px;">
    {$texts.top_info}
</div>
<br/>
<table class="small" cellpadding="3" cellspacing="0" style="font-size: {$misc.font_size.big}px">
    {if $values.pre_payment|string_format:"%f" > 0}
        <tr>
            <td style="text-align: right; width: 80px;">{$texts.pre_payment}</td>
            <td style="text-align: right; width: 80px;">{$values.pre_payment}</td>
            <td></td>
        </tr>
    {/if}
    {foreach from=$values.dues key=rate_number item=rate_detail}
        <tr>
            <td style="text-align: right; width: 80px;">{$rate_number+1}. {$texts.rate}</td>
            <td style="text-align: right; width: 80px;">{$rate_detail.amount}</td>
            <td style="width: 140px;">
                {if $rate_detail.date != 0}
                    ({$texts.rate_due} {$rate_detail.date|date_format:"%d.%m.%G"})
                {/if}
            </td>
        </tr>
    {/foreach}
</table>
<br/>
<br/>
<strong>{$texts.top_calculation}:</strong>
<br/>
<br/>
<table class="small" cellpadding="3" cellspacing="0" style="font-size: {$misc.font_size.big}px">
    <tr>
        <td>{$texts.cart_amount}</td>
        <td>=</td>
        <td style="text-align: right;">
            {math
                equation="x + y"
                x=$values.rate_base_amount|string_format:"%f"
                y=$values.pre_payment|string_format:"%f"
                format="%f"
                assign="cart_amount"}
            {$cart_amount|xtc_format_price_order:1:$misc.currency}
        </td>
    </tr>
    {if $values.pre_payment|string_format:"%f" > 0}
        <tr>
            <td>{$texts.pre_payment}</td>
            <td>-</td>
            <td style="text-align: right;">{$values.pre_payment}</td>
        </tr>
        <tr>
            <td>{$texts.cart_amount_without_pre_payment}</td>
            <td>=</td>
            <td style="text-align: right;">
                {$values.rate_base_amount}
            </td>
        </tr>
    {/if}
    <tr>
        <td style="border-top:1px double silver;">{$texts.surcharge}</td>
        <td  style="border-top:1px double silver;" colspan="2"></td>
    </tr>
    <tr>
        <td>({$values.rate_base_amount} x {$values.interest|string_format:"%.2f"}% x {$values.rate_count})</td>
        <td>+</td>
        <td style="text-align: right;">{$values.surcharge}</td>
    </tr>
    <tr>
        <td>
            {$texts.fee}
            {if $values.fee_tax|string_format:"%f" > 0}
                {$values.fee_tax|string_format:$texts.fee_tax}
            {/if}
        </td>
        <td>+</td>
        <td style="text-align: right;">{$values.fee}</td>
    </tr>
    <tr>
        <td>{$texts.additional_costs}</td>
        <td>+</td>
        <td style="text-align: right;">{$values.additional_costs}</td>
    </tr>
    {if $values.pre_payment|string_format:"%f" > 0}
        <tr>
            <td>{$texts.pre_payment}</td>
            <td>+</td>
            <td style="text-align: right;">{$values.pre_payment}</td>
        </tr>
    {/if}
    <tr>
        <td>{$texts.total_amount}</td>
        <td>=</td>
        <td style="text-align: right;">{$values.total_amount}</td>
    </tr>
    <tr>
        <td style="border-top:1px double silver;">{$texts.annual_rate}</td>
        <td style="border-top:1px double silver;"></td>
        <td style="text-align: right; border-top:1px double silver;">{$values.annual_rate|string_format:"%.2f"} %</td>
    </tr>
</table>
<br/>
<div style="font-size: {$misc.font_size.small}px">
    {$texts.button_calculation}
</div>
<br/>