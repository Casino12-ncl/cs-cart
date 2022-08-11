<div id="product_features_{$block.block_id}">
<div class="ty-feature">

    {if $unit_head_data.main_pair}
    <div class="ty-feature__image">
        {include file="common/image.tpl" images=$unit_head_data.main_pair}
    </div>
    
    {/if}
    <div class="ty-feature__description ty-wysiwyg-content">
        {$unit_head_data.description nofilter}
    </div>
</div>

<table class = 'ty-table ty-orders search'>

    <thead>
        <tr>
            <td class = 'product-name-column'>
                {__('first_name')}
            </td>       
            <td class = 'product-name-column'>
                {__('last_name')}
            </td>
            <td class = 'product-name-column'>
                {__('email')}
            </td>
        </tr>
    </thead>
    
    {foreach from=$user_data item="worker_info"}
        
        <tr>
            <td class="ty-orders search_item">{$worker_info.firstname}</td>
            <td class="ty-orders search_item">{$worker_info.lastname}</td>
            <td class="ty-orders search_item">{$worker_info.email}</td>
        </tr>
    {/foreach}

</table>
