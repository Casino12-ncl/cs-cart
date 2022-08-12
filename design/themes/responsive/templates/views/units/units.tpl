
{if $units}

    {script src="js/tygh/exceptions.js"}    

    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}

    {if !$show_empty}
        {split data=$units size=$columns|default:"2" assign="splitted_units"}
    {else}
        {split data=$units size=$columns|default:"2" assign="splitted_units" skip_complete=true}
    {/if}

    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}    

    {* FIXME: Don't move this file *}
    {script src="js/tygh/product_image_gallery.js"}

    <div class="grid-list">
        {strip}
            {foreach from=$splitted_units item="sunits"}
                {foreach from=$sunits item="unit"}
                    <div class="ty-column{$columns}">
                        {if $unit}
                            {assign var="obj_id" value=$unit.unit_id}
                            
                            {assign var="obj_id_prefix" value="`$obj_prefix``$unit.unit_id`"}
                                                       
                            <div class="ty-grid-list__item ty-quick-view-button__wrapper">
                            
                                    <div class="ty-grid-list__image">
                          
                                        <a href="{"units.unit?unit_id={$unit.unit_id}"|fn_url}">
                                            {include 
                                            file="common/image.tpl" 
                                            no_ids=true 
                                            images=$unit.main_pair 
                                            image_width=$settings.Thumbnails.product_lists_thumbnail_width 
                                            image_height=$settings.Thumbnails.product_lists_thumbnail_height 
                                            lazy_load=false
                                            }
                                        </a>
                                    </div>

                                    <div class="ty-grid-list__item-name">
                                        <bdi>                                        
                                            <a href="{"units.unit?unit_id={$unit.unit_id}"|fn_url}" class="product-title" title="{$unit.unit}">{$unit.unit}</a>                                             
                                            {__('boss')}<br>
                                           {$unit.lastname}<br>
                                           {$unit.firstname}
                                        </bdi>
                                    </div>
                            </div>
                        {/if}
                    </div>
                {/foreach}
            {/foreach}
        {/strip}
    </div>
    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}
{/if}
{capture name="mainbox_title"}{$title}{/capture}
