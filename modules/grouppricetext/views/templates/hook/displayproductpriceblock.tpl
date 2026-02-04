{if isset($group_message) && $group_message}
<div class="group-price-text alert alert-info" style="margin-top: 10px;">
    <i class="material-icons">info</i>
    <span>{$group_message|escape:'html':'UTF-8'}</span>
</div>
{/if}