{if isset($group_message) && $group_message}
<div class="group-price-text alert alert-info" style="margin-top: 10px;">
    <i class="material-icons">info</i>
    <span>{$group_message|escape:'html':'UTF-8'}</span>
</div>
{/if}

{if isset($regular_price) && $regular_price}
    <br>
    <strong>{l s='Katalogpreis:' mod='grouppricetext'} {$regular_price}</strong>
{/if}

{if isset($debug_info) && $debug_info}
<div class="group-price-text alert alert-warning" style="margin-top: 10px; font-size: 11px;">
    <strong>DEBUG - Product Properties:</strong>
    <pre style="white-space: pre-wrap; word-wrap: break-word;">{$debug_info|@print_r}</pre>
</div>
{/if}