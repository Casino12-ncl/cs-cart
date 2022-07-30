{** units section **}

{capture name="mainbox"}


    <form action="{""|fn_url}" method="post" id="units_form" name="units_form" enctype="multipart/form-data">
        
        <input type="hidden" name="fake" value="1" />
        {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id="pagination_contents_units"}

        {$c_url=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

        {$rev=$smarty.request.content_id|default:"pagination_contents_units"}
        {include_ext file="common/icon.tpl" class="icon-`$search.sort_order_rev`" assign=c_icon}
        {include_ext file="common/icon.tpl" class="icon-dummy" assign=c_dummy}
        {$banner_statuses=""|fn_get_default_statuses:true}
        {$has_permission = fn_check_permissions("units", "update_status", "admin", "POST")}
               
            {capture name="tools_list"}                                  
            
            <a data-ca-confirm-text="Будут удалены все выбранные отделы" 
                class="cm-process-items cm-submit cm-confirm"
                data-ca-target-form="units_form" 
                data-ca-dispatch="dispatch[units.delete_units]"
                Method = "POST">
            Удалить все выбранные отделы
            </a>                     
            {/capture}                                
                            
        {if $units}
            {capture name="units_table"}
                <div class="table-responsive-wrapper longtap-selection">
                    
                    <table class="table table-middle table--relative table-responsive">
                        <thead
                            data-ca-bulkedit-default-object="true"
                            data-ca-bulkedit-component="defaultObject">
                        
                        <tr>
                            <th width="6%" class="left mobile-hide">
                                {include file="common/check_items.tpl" is_check_disabled=!$has_permission check_statuses=($has_permission) ? $banner_statuses : '' }

                                <input type="checkbox"
                                    class="bulkedit-toggler hide"
                                    data-ca-bulkedit-disable="[data-ca-bulkedit-default-object=true]"
                                    data-ca-bulkedit-enable="[data-ca-bulkedit-expanded-object=true]"
                                />
                            </th>
                            <th>
                                 <a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("logo")}{if $search.sort_by === "logo"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>                                
                            </th>
                            <th>
                                <a class="cm-ajax" href="{"`$c_url`&sort_by=position&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("position")}{if $search.sort_by === "position"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                            </th>
                           
                            <th>
                                <a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("name")}{if $search.sort_by === "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                            </th>
                            <th width="5%"><a href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("creation_date")}{if $search.sort_by === "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>

                            
                            
                            <th width="10%" class="right" ><a href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("status")}{if $search.sort_by === "status"}{$c_icon nofilter}{/if}</a></th>
                        </tr>
                        </thead>
                        
                        {foreach from=$units item=unit}
                        <tr class="cm-row-status-{$unit.status|lower} ">
                            {$allow_save=true}
                            {if $allow_save}
                                {$no_hide_input="cm-no-hide-input"}
                            {else}
                                {$no_hide_input=""}
                            {/if}

                            <td width="6%" class="left mobile-hide">
                                <input type="checkbox" name="units_ids[]" value="{$unit.unit_id}" class="cm-item {$no_hide_input} cm-item-status-{$unit.status|lower} " />
                            </td>
                            
                            <td width="{$image_width + 18px}" class="products-list__image">
                                {include
                                    file="common/image.tpl"
                                    image=$unit.main_pair.icon|default:$unit.main_pair.detailed
                                    image_id=$unit.main_pair.image_id
                                    image_width=50px
                                    image_height=50px
                                    image_css_class="products-list__image--img"
                                    link_css_class="products-list__image--link"
                                 }
                            </td>  
                            <td>
                                <input type="text" name="units_data[{$unit.unit_id}][position]" value="{$unit.position}" size="3" class="input-micro">
                            </td>
                            <td class="{$no_hide_input}" data-th="{__("name")}">
                                <a class="row-status" href="{"units.update_unit?unit_id=`$unit.unit_id`"|fn_url}">{$unit.unit}</a>
                                
                            </td>                
                            <td width="15%" data-th="{__("creation_date")}">
                                {$unit.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                            </td>
                            
                            <td width="10%" class="right" data-th="{__("status")}">
                            {include file="views/products/components/status_on_manage.tpl"
                            id=$unit.unit_id
                            status=$unit.status
                            hidden=true
                            object_id_name="unit_id"
                            table="units"
                            non_editable_status=!fn_check_permissions("tools", "update_status", "admin", "POST", ["table" => "products"])
                            }
                            </td>
                        </tr>
                        {/foreach}
                        
                    </table>
                </div>
            {/capture}

            {include file="common/context_menu_wrapper.tpl"
                form="units_form"
                object="units"
                items=$smarty.capture.units_table
                has_permissions=$has_permission
            }
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        {include file="common/pagination.tpl" div_id="pagination_contents_units"}
        {capture name="buttons"}
   
    {dropdown content=$smarty.capture.tools_list}
    {if $units}

        {include file="buttons/save.tpl" but_name="dispatch[units.update_units]" but_role="action" but_target_form="units_form" but_meta="cm-submit"}
    {/if}
{/capture}

{capture name="adv_buttons"} 
    {include file="common/tools.tpl" tool_href="units.update_unit"}
{/capture}

{capture name="sidebar"}
    {hook name="units:manage_sidebar"}
    {include 
    file="common/saved_search.tpl"
    dispatch="units.manage_units" view_type="units"}
    {include file="views/units/units_search_form.tpl" dispatch="units.manage_units"}
    {/hook}
{/capture}
    </form>

{/capture}


{hook name="units:manage_mainbox_params"}
    {$page_title = _("Отделы")}
    {$select_languages = true}
{/hook}

{include 
    file="common/mainbox.tpl"
    title=$page_title 
    content=$smarty.capture.mainbox 
    buttons=$smarty.capture.buttons
    adv_buttons=$smarty.capture.adv_buttons 
    select_languages=$select_languages 
    sidebar=$smarty.capture.sidebar
}

{** ad section **}
