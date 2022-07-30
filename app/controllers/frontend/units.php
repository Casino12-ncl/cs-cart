<?php

use Illuminate\Support\Collection;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\OrderStatuses;
use Tygh\Enum\ProfileDataTypes;
use Tygh\Enum\ProfileFieldLocations;
use Tygh\Enum\ProfileFieldSections;
use Tygh\Enum\ProfileFieldTypes;
use Tygh\Enum\ProfileTypes;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UsergroupLinkStatuses;
use Tygh\Enum\UsergroupStatuses;
use Tygh\Enum\UsergroupTypes;
use Tygh\Enum\UserTypes;
use Tygh\Exceptions\DeveloperException;
use Tygh\Http;
use Tygh\Languages\Languages;
use Tygh\Location\Manager;
use Tygh\Navigation\LastView;

use Tygh\Storage;
use Tygh\Tools\SecurityHelper;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tools\Url;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 * @var string $action
 */


if ($mode == 'units'){
    $_REQUEST['unit_id'] = empty($_REQUEST['unit_id']) ? 0 : $_REQUEST['unit_id'];

    Tygh::$app['session']['continue_url'] = "units.units";

    $unit_data = fn_get_unit_data($_REQUEST['unit_id'], CART_LANGUAGE, '*', true, false, $preview);
    $params = $_REQUEST;   
    if ($items_per_page = fn_change_session_param(Tygh::$app['session'], $_REQUEST, 'items_per_page')) {
        $params['items_per_page'] = $items_per_page;        
    }
   
    list($units, $search) = fn_get_units($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

    if (isset($search['page']) && ($search['page'] > 1) && empty($units)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
   $selected_layout = fn_get_products_layout($_REQUEST);

   fn_filters_handle_search_result($params, $units, $search);

   $selected_layout = fn_get_products_layout($_REQUEST);

    
    Tygh::$app['view']->assign('units', $units);
    Tygh::$app['view']->assign('search', $search);          
    Tygh::$app['view']->assign('columns',3);   
    Tygh::$app['view']->assign('selected_layout',$selected_layout);   
    Tygh::$app['view']->assign('unit_data', $unit_data);   
    
    if(!empty($unit_data['page_title'])){
        Tygh::$app['view']->assign('page_title', $unit_data['page_title']);  
    }
    // [Breadcrumbs]
    fn_add_breadcrumb("Отделы");
} 
if ($mode === 'unit') {
    
        $unit_data = [];
        $unit_head_data = [];
        $unit_id = !empty($_REQUEST['unit_id']) ? $_REQUEST['unit_id'] : 0;
        $unit_data = fn_get_slave_data($unit_id, CART_LANGUAGE);
        $unit_head_data = fn_get_unit_data($unit_id, CART_LANGUAGE);
        
        //fn_print_die($unit_head_data);
        Tygh::$app['view']->assign('user_data', $unit_data);
        Tygh::$app['view']->assign('unit_head_data', $unit_head_data);
       
        fn_add_breadcrumb("Отделы", $unit_data['unit']);
        
        $params = $_REQUEST;
        $params['extend'] = array('units', 'description');
        $params['items_ids'] = !empty($unit_data['users']) ? implode (',', $unit_data['users']) : -1;
        
       
       
}
function fn_get_slave_data($unit_id=0, $lang_code = CART_LANGUAGE)
{
   
    $slave_id = db_get_field("SELECT slave_id FROM ?:units WHERE unit_id = ?i", $unit_id);
    
   
    $user_data= db_get_array('SELECT firstname, lastname, email FROM ?:users WHERE user_id IN(?p)', $slave_id);
   
    return $user_data;
   
}

function fn_get_unit_data($unit_id=0, $lang_code = CART_LANGUAGE)
    {
        $unit = [];
        $params = $_REQUEST;
 
        $fields = array (
           
            '?:units.*',            
            '?:unit_descriptions.*',
            '?:users.firstname',
            '?:users.lastname',
           
        );      
       
        $unit['description'] = db_get_field("SELECT description FROM ?:unit_descriptions WHERE unit_id = ?i", $unit_id);
        $unit['main_pair'] = fn_get_image_pairs($unit_id, 'unit', 'M', true, false, $lang_code);;
               
        
       
        return $unit;
    }

 function fn_get_units($params = array(),  $items_per_page = 3, $lang_code = CART_LANGUAGE)
    {
        // Set default values to input params
        $default_params = array(
            'page' => 1,
            'items_per_page' => $items_per_page
        );

       
        $default_params = [
            'items_per_page'  => 4
           
        ];
        $params = array_merge($default_params, $params);
        if (AREA == 'C') {
            $params['status'] = 'A';
        }

        $sortings = array(
            'image'     => '?:units.image_ids',
            'position'  => '?:units.position',
            'timestamp' => '?:units.timestamp',
            'name'      => '?:unit_descriptions.unit',
            'status'    => '?:units.status',
            
        );

        $condition = $limit = $join = '';

        if (!empty($params['limit'])) {
            $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
        }

        $sorting = db_sort($params, $sortings, 'name', 'asc');

        if (!empty($params['item_ids'])) {
            $condition .= db_quote(' AND ?:units.unit_id IN (?n)', explode(',', $params['item_ids']));
        }
       
        if (!empty($params['unit_id'])) {
            $condition .= db_quote(' AND ?:units.unit_id = ?i', $params['unit_id']);
        }
       
        // if (!empty($params['user_id'])) {
        //     $condition .= db_quote(' AND ?:units.user_id = ?i', $params['user_id']);
        // }

        if (!empty($params['status'])) {
            $condition .= db_quote(' AND ?:units.status = ?s', $params['status']);
        }

        $fields = array (
           
            '?:units.*',            
            '?:unit_descriptions.*',
            '?:users.firstname',
            '?:users.lastname'
        );

       
        $join .= db_quote(' LEFT JOIN ?:unit_descriptions ON ?:unit_descriptions.unit_id = ?:units.unit_id AND ?:unit_descriptions.lang_code = ?s', $lang_code);
        $join .= db_quote(' LEFT JOIN ?:users ON ?:users.user_id = ?:units.user_id ');
      
        if (!empty($params['items_per_page'])) {
            $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:units $join WHERE 1 $condition");
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }
       
        
        $units = db_get_hash_array( 
            "SELECT ?p FROM ?:units " .
            $join .
            "WHERE 1 ?p ?p ?p",
            'unit_id', implode(', ', $fields), $condition, $sorting, $limit
        );
        

        

     $unit_image_ids = array_keys($units);
        $images = fn_get_image_pairs($unit_image_ids, 'unit', 'M', true, false, $lang_code);

        foreach ($units as $unit_id => $unit) {
            $units[$unit_id]['main_pair'] = !empty($images[$unit_id]) ? reset($images[$unit_id]) : array();
        }
        

        return array($units, $params);
    }

    function fn_update_unit($data, $unit_id, $lang_code = DESCR_SL) 
    {  

        if (isset($data['timestamp'])) {
            $data['timestamp'] = fn_parse_date($data['timestamp']);
        }

        

        if (!empty($unit_id)) {
            db_query("UPDATE ?:units SET ?u WHERE unit_id = ?i", $data, $unit_id);
            db_query("UPDATE ?:unit_descriptions SET ?u WHERE unit_id = ?i AND lang_code = ?s", $data, $unit_id, $lang_code);
        
        

        } else {
            $unit_id = $data['unit_id'] = db_replace_into('units', $data);
            

            foreach (Languages::getAll() as $data['lang_code'] => $v) {
                db_query("REPLACE INTO ?:unit_descriptions ?e", $data);
            }
        }
        if (!empty($unit_id)) {
            fn_attach_image_pairs('unit', 'unit', $unit_id, $lang_code);
        }
       
        return $unit_id;
    }
    function fn_delete_unit($unit_id)
    {
        if (!empty($unit_id)) {
            $res = db_query('DELETE FROM ?:units WHERE unit_id = ?i', $unit_id);
            db_query('DELETE FROM ?:unit_descriptions WHERE unit_id = ?i', $unit_id);
        }
    }

    $boss_info = fn_get_user_short_info($unit_data['user_id']);
    $workers_info = db_get_fields("SELECT user_id FROM ?:users WHERE user_id IN(?n) ", explode ('.', $unit_data['slave_id']));

    list($workers_info, $search) = fn_get_users($params, Registry::get('settings.Appearance.products_elements_per_page'), CART_LANGUAGE);

    Tygh::$app['view']->assign('boss_info', $boss_info);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('workers_info', $workers_info);