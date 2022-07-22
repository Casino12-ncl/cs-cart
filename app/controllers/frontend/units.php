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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

}

 if ($mode == 'units'){
     
          // Save current url to session for 'Continue shopping' button
          Tygh::$app['session']['continue_url'] = "units.units";
  
          
  
          $params = $_REQUEST;
  
    
          $params['user_id'] = Tygh::$app['session']['auth']['user_id'];
         
  
          list($units, $search) = fn_get_units($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);
        
          
          Tygh::$app['view']->assign('units', $units);
          Tygh::$app['view']->assign('search', $search);          
          Tygh::$app['view']->assign('columns', 3);   
           
         
          // [Breadcrumbs]
          fn_add_breadcrumb("Отделы");
          
  
} elseif ($mode === 'unit') {
        $unit_data = [];
        $unit_id = !empty($_REQUEST['unit_id']) ? $_REQUEST['unit_id'] : 0;
        $unit_data = fn_get_unit_data($unit_id, CART_LANGUAGE);
       // fn_print_die($unit_data);
        // if (empty($unit_data)) {
        //     return [CONTROLLER_STATUS_NO_PAGE];
        // }   
        
    
        Tygh::$app['view']->assign('unit_data', $unit_data);
    
        fn_add_breadcrumb("Отделы", $unit_data['unit']);
        //fn_print_die($_REQUEST);
        $params = $_REQUEST;
        $params['extend'] = ['description'];
        $params['items_ids'] = !empty($unit_data['users']) ? implode (',', $unit_data['users']) : -1;
        
        if ($items_per_page = fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'items_per_page')) {
            $params['items_per_page'] = $items_per_page;
        }
        if ($sort_by = fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'sort_by')) {
            $params['sort_by'] = $sort_by;
        }
        if ($sort_order = fn_change_session_param(Tygh::$app['session']['search_params'], $_REQUEST, 'sort_order')) {
            $params['sort_order'] = $sort_order;
        }
    
    
        list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'));
    
        fn_gather_additional_products_data($products, [
            'get_icon'      => true,
            'get_detailed'  => true,
            'get_options'   => true,
            'get_discounts' => true,
            'get_features'  => false
        ]);
       
        $selected_layout = fn_get_units($_REQUEST);
       // fn_print_die($unit_data);
       // fn_print_die($selected_layout);
        Tygh::$app['view']->assign('user_data', $user_data);
        Tygh::$app['view']->assign('search', $search);
        Tygh::$app['view']->assign('selected_layout', $selected_layout);
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
       
    function fn_get_units($params = array(),  $items_per_page = 3, $lang_code = CART_LANGUAGE)
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
            $limit = db_paginate($params['page'], $params['items_per_page']=3, $params['total_items']);
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
 