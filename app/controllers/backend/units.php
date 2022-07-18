
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

defined('BOOTSTRAP') or die('Access denied');

$auth = & Tygh::$app['session']['auth'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {// if ($mode='add_unit' || $mode='update_unit') {
    
    } 
    if ($mode === 'm_delete') {
        if (!empty($_REQUEST['user_ids'])) {
            foreach ($_REQUEST['user_ids'] as $v) {
                fn_delete_user($v);
            }
        }

        return array(CONTROLLER_STATUS_OK, 'units.manage' . (isset($_REQUEST['user_type']) ? '?user_type=' . $_REQUEST['user_type'] : '' ));
    }
    if($mode='update_unit' || $mode = 'add_unit') {
        
    }
     if($mode='manage_units') {
       
        list($units, $search) = fn_get_units($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
    
   // fn_print_die($units);
   
    Tygh::$app['view']->assign('units', $units);
    Tygh::$app['view']->assign('search', $search);
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
            'position' => '?:units.position',
            'timestamp' => '?:units.timestamp',
            'name' => '?:unit_descriptions.unit',
            
            'status' => '?:units.status',
        );

        $condition = $limit = $join = '';

        if (!empty($params['limit'])) {
            $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
        }

        $sorting = db_sort($params, $sortings, 'name', 'asc');

       
       
        if (!empty($params['item_ids'])) {
            $condition .= db_quote(' AND ?:units.unit_id IN (?n)', explode(',', $params['item_ids']));
        }

       

        

        if (!empty($params['status'])) {
            $condition .= db_quote(' AND ?:units.status = ?s', $params['status']);
        }

       

        $fields = array (
            '?:units.unit_id',
            '?:units.position',
            '?:units.status',
            '?:units.timestamp',
            '?:unit_descriptions.unit',
            '?:unit_descriptions.description',
                       
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

        

        //$banner_image_ids = array_column($units, 'banner_image_id');
       // $images = fn_get_image_pairs($banner_image_ids, 'promo', 'M', true, false, $lang_code);

        // foreach ($units as $unit_id => $unit) {
        //     $units[$unit_id]['main_pair'] = !empty($images[$banner['banner_image_id']]) ? reset($images[$banner['banner_image_id']]) : array();
        // }

        

        return array($units, $params);
    }
     