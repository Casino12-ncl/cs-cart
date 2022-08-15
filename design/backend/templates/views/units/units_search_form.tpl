{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("admin_search_title")}</h6>
{/if}

<form name="units_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $search.unit}
<input  name="unit" value="{$search.unit}" />
{/if}

{if $put_request_vars}
    {array_to_fields data=$smarty.request skip=["callback"] escape=["data_id"]}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label >{_("Отдел")}</label>
    <div class="break">
        <input type="text" name="unit" id="unit" value="{$search.unit}" />
    </div>
</div>       
<div class="sidebar-field">
        <label for="elm_type">{__("status")}</label>
        {assign var="items_status" value=""|fn_get_default_statuses:true}
        <div class="controls">
            <select name="status" id="elm_type">
                <option value="">{__("all")}</option>
                {foreach from=$items_status key=key item=status}
                    <option value="{$key}" {if $search.status == $key}selected="selected"{/if}>{$status}</option>
                {/foreach}
            </select>
        </div>
</div>
    {/capture}

    {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="units" in_popup=$in_popup}

    </form>

    {if $in_popup}
</div>
</div>
{else}
</div><hr>
{/if}
