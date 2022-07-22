
<?php

use Tygh\Api;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tools\Url;
use Tygh\Tygh;
use Tygh\Languages\Languages;

defined('BOOTSTRAP') or die('Access denied');

$auth = & Tygh::$app['session']['auth'];
$suffix = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $suffix = '';
    fn_trusted_vars(
        'unit_data',
        'update_unit',
        'unit_id',
        'manage_units',
        'user_id',
        'user_info',
        'unit_ids',
        'u_info',
        'users'

       
    );
        if($mode == 'update_unit') {
            $unit_id = !empty($_REQUEST['unit_id']) ? $_REQUEST['unit_id'] :0;
            $data = !empty($_REQUEST['unit_data']) ? $_REQUEST['unit_data'] : [];
            $unit_id = fn_update_unit($data, $unit_id);
           if (!empty($unit_id)) {
            $suffix = ".update_unit?unit_id={$unit_id}";
           } else $suffix = ".add_unit";
    
        } elseif($mode == 'update_units') {
            if (!empty($_REQUEST['units_data'])){
                foreach ($_REQUEST['units_data'] as $unit_id => $data) {
                    fn_update_unit($data, $unit_id);
                }
            }
                $suffix = ".manage_units";
            
        } elseif($mode == 'delete_unit') {
            $unit_id = !empty($_REQUEST['unit_id']) ? $_REQUEST['unit_id'] :0;
            fn_delete_unit($unit_id);
            $suffix = ".manage_units";
        } elseif($mode == 'delete_units') {
           
        //    fn_print_die($_REQUEST);
           
            if (!empty($_REQUEST['units_ids'])) {
                foreach($_REQUEST['units_ids'] as $unit_id){
                    fn_delete_unit($unit_id); 
                }
            }
            $suffix = ".manage_units";
        }
    
    return [CONTROLLER_STATUS_OK, 'units' . $suffix];
}
     if($mode=='update_unit' || $mode == 'add_unit') {
         $unit_id = !empty($_REQUEST['unit_id']) ? $_REQUEST['unit_id'] : 0;
         $unit_data = fn_get_unit_data($unit_id, DESCR_SL);
         
    
     if (empty($unit_data) && $mode == 'update') {
         return [CONTROLLER_STATUS_NO_PAGE];
     }
   // fn_print_die($unit_data.users); 
     Tygh::$app['view']->assign([
        'unit_data' => $unit_data,
        'boss_info' =>   !empty(fn_get_user_info($unit_data['user_id'], DESCR_SL)) ? fn_get_user_info($unit_data['user_id'], DESCR_SL) : [],
        'worker_info' => !empty(fn_get_user_info($unit_data['user_id'], DESCR_SL)) ? fn_get_user_info($unit_data['user_id'], DESCR_SL) : [],
        
        
    ]);    
     }
     
  if($mode =='manage_units') {
    
        list($units, $search) = fn_get_units($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
          
    Tygh::$app['view']->assign('units', $units);
    Tygh::$app['view']->assign('search', $search);
     }

    function fn_get_unit_data($unit_id=0, $lang_code = CART_LANGUAGE)
    {
        $unit = [];
        if (!empty($unit_id)){
            list($units) = fn_get_units([
                'unit_id' => $unit_id
                
            ], 1, $lang_code);
            $unit = !empty($units) ? reset($units) : [];
        }
        return $unit;
    }
     
    function fn_get_units($params = array(),  $items_per_page = 0, $lang_code = CART_LANGUAGE)
    {
        // Set default values to input params
        $default_params = array(
            'page' => 1,
            'items_per_page' => $items_per_page
        );

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

        if (!empty($params['status'])) {
            $condition .= db_quote(' AND ?:units.status = ?s', $params['status']);
        }
        
        $fields = array (            
            '?:units.*',            
            '?:unit_descriptions.*',
        );

       
        $join .= db_quote(' LEFT JOIN ?:unit_descriptions ON ?:unit_descriptions.unit_id = ?:units.unit_id AND ?:unit_descriptions.lang_code = ?s', $lang_code);
       
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
        // fn_print_die($data);
        return $unit_id;
    }
    function fn_delete_unit($unit_id)
    {
    if (!empty($unit_id)) {
        $res = db_query('DELETE FROM ?:units WHERE unit_id = ?i', $unit_id);
        db_query('DELETE FROM ?:unit_descriptions WHERE unit_id = ?i', $unit_id);
    }
    }