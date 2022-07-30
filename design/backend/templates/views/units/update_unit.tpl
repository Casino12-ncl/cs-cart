{if $unit_data}
    {assign var="id" value=$unit_data.unit_id}
{else}
    {assign var="id" value=0}
{/if}




{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="banners_form" enctype="multipart/form-data">
    <input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
    <input type="hidden" class="cm-no-hide-input" name="unit_id" value="{$id}" />
        <div id="content_general">
            <div class="control-group">
                <label for="elm_banner_name" class="control-label cm-required">{__("name")}</label>
                <div class="controls">
                <input type="text"
                 name="unit_data[unit]"
                 id="elm_banner_name" 
                 value="{$unit_data.unit}" 
                 size="25" 
                 class="input-large" />
                </div>
            </div>         
          

                <div class="control-group">
                    <label class="control-label">{__("boss")}</label>
            
                    <div class="controls">
                    
                     
                        {include 
                        file="pickers/users/picker.tpl"                         
                        
                        data_id="return_users" 
                        but_text=__("add_admins_from_users")             
                        input_name="unit_data[user_id]" 
                        item_ids=$unit_data.user_id 
                        display = "radio"
                        placement="left"
                        view_mode = "single_button"
                        user_info=$boss_info
                        }
                    </div>
                 </div> 
            
                <div class="control-group">
                    <label class="control-label">{__("worker")}</label>
                    <div class="controls">       
                        {include
                        file="pickers/users/picker.tpl"
                        data_id="return_users" 
                        but_text=__("add_workers_from_users")  
                        but_meta="btn"                  
                        input_name="unit_data[slave_id]" 
                        item_ids=$unit_data.slave_id                        
                        user_info=$worker_info     

                        }
                    </div>
                </div> 

            <div class="control-group" id="banner_graphic">
                <label class="control-label">{__("image")}</label>
                <div class="controls">
                    {include file="common/attach_images.tpl"
                        image_name="unit"
                        image_object_type="unit"
                        image_pair=$unit_data.main_pair
                        image_object_id=$id
                        no_detailed=true
                        hide_titles=true
                    }
                </div>
            </div>

           <div class="control-group" id="banner_text">
            <label class="control-label" for="elm_banner_description">{__("description")}:</label>
            <div class="controls">
                <textarea id="elm_banner_description" 
                name="unit_data[description]" 
                cols="35" rows="8" class="cm-wysiwyg 
                input-large">{$unit_data.description}
                </textarea>
            </div>
        </div>

           <div class="control-group">
            <label class="control-label" for="elm_banner_timestamp_{$id}">{__("creation_date")}</label>
            <div class="controls">
            {include file="common/calendar.tpl" 
            date_id="elm_banner_timestamp_`$id`" 
            date_name="unit_data[timestamp]" 
            date_val=$unit_data.timestamp|default:$smarty.const.TIME 
            start_year=$settings.Company.company_start_year}
            </div>
        </div>   
            
            {include file="common/select_status.tpl" 
            input_name="unit_data[status]" 
            id="elm_unit_status" 
            obj_id=$id 
            obj=$unit_data 
            hidden=false}

        <!--content_general--></div>

    {capture name="buttons"}
        {if !$id}
            {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="banners_form" but_name="dispatch[units.update_unit]"}
        {else}            
            {include file="buttons/save_cancel.tpl" but_name="dispatch[units.update_unit]" but_role="submit-link" but_target_form="banners_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
     
                {capture name="tools_list"}
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="units.delete_unit?unit_id=`$id`" method="POST"}</li>                      
                {/capture}
                {dropdown content=$smarty.capture.tools_list}          
        {/if}
{/capture}

</form>

{/capture}

{include file="common/mainbox.tpl"
    title=($id) ? $unit_data.banner : _("Добавить новый отдел")
    content=$smarty.capture.mainbox
    buttons=$smarty.capture.buttons
    select_languages=true}

{** unit section **}
