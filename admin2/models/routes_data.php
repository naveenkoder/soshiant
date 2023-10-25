<?php
require_once __DIR__ . "/routes.php";
require_once __DIR__ . "/top_ten.model.php";

function getRecords($data)
{
    $rotues = $data['routes'];
    $sql = "SELECT * FROM arioo_routes_data WHERE `unique_id` in (SELECT unique_id FROM arioo_routes WHERE id in ($rotues));";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records;
}
function getAll($ids)
{
    $records = array();
    $ids = explode(",", $ids);
    foreach ($ids as $id) {
        $records[] = getActualValueByUniqueId($id);
    }
    return array_filter($records, function ($value) {
        return !is_null($value);
    });
}

function getAllData()
{
    $records = array();
    $ids = getRoutes(true);
    foreach ($ids as $id) {
        $records[] = getActualValueByUniqueId($id["unique_id"]);
    }
    return array_filter($records, function ($value) {
        return !is_null($value);
    });
}
function getAllPredictedData()
{
    
    $records = array();
    $ids = getRoutes(true);
    
    foreach ($ids as $id) {
        $records[] = getPredicValueByUniqueId($id["unique_id"]);
    }
    return array_filter($records, function ($value) {
        return !is_null($value);
    });
}
function getAllActualData()
{
    
    $records = array();
    $ids = getRoutes(true);
    foreach ($ids as $id) {
        $records[] = getActualValueByUniqueId($id["unique_id"]);
      
    }
   
    return array_filter($records, function ($value) {
        return !is_null($value);
    });
}

function createRouteData($data)
{
    $year = $data->year;
    $period = $data->period;
    $value = $data->value;
    $more_info = $data->more_info;
    $create_time = time();
    $unique_id = $data->unique_id;
    $is_real = $data->is_real;
    $average_accuracy = $data->average_accuracy ?? 0;
    $confidence_level = $data->confidence_level ?? 0;
    $user_id = fusion_get_userdata()['user_id'] ?? 0 ;
    $username = fusion_get_userdata()['user_name'] ?? null;
    
    $sql = "INSERT INTO arioo_routes_data (year, period, value, more_info, unique_id, is_real,average_accuracy, confidence_level, create_time, user_id, user_name ) VALUES ($year, '$period', '$value', '$more_info', '$unique_id', $is_real, $average_accuracy, $confidence_level, $create_time, $user_id, '$username')";
    
    $result = dbquery($sql);
  
    if ($result) {
        return true;
    } else {
        return false;
    }
}



function getRecordsDataByUniqueId($unique_id)
{
    return [
        "predictValue" => getPredicValueByUniqueId($unique_id),
        "actualValue" => getActualValueByUniqueId($unique_id),
        "routeData" => getRouteByUniqueId($unique_id),
        "topTenData" => getTopTenByUniqueId($unique_id)
    ];
}

function getPredicValueByUniqueId($unique_id)
{
   
    $sql = "SELECT * FROM arioo_routes_data WHERE unique_id = $unique_id AND is_real = 0 ORDER BY `year` DESC, `period` DESC, `id` DESC LIMIT 1";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records[0];
    } else {
        return false;
    }
}

function getActualValueByUniqueId($unique_id)
{
    $sql = "SELECT * FROM arioo_routes_data WHERE unique_id = $unique_id AND is_real = 1 ORDER BY `year` DESC, `period` DESC, `id` DESC LIMIT 1";
   
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records[0];
    } else {
        return false;
    }
}
function getActualValueHistory()
{
    $sql = "SELECT * FROM arioo_routes_data WHERE is_real = 1 ORDER BY `year` DESC, `period` DESC, `id` DESC";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records;
    } else {
        return false;
    }
}
function getPredictedValueHistory()
{
    $sql = "SELECT * FROM arioo_routes_data WHERE is_real = 0 ORDER BY `year` DESC, `period` DESC, `id` DESC";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records;
    } else {
        return false;
    }
}
function getHistory()
{
    $sql = "SELECT * FROM arioo_routes_data ORDER BY `year` DESC, `period` DESC, `id` DESC";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records;
    } else {
        return false;
    }
}

/**
 * 
 * 
 *
 */

function getRecordsDataById($id)
{
    return [
        "predictValue" => getPredictedValueById($id),
        "actualValue" => getActualValueById($id),
        "routeData" => getRouteById($id),
        "topTenData" => getTopTenById($id)
    ];
}

function getPredictedValueById($id)
{
    $sql = "SELECT * FROM arioo_routes_data WHERE unique_id = (SELECT `unique_id` FROM arioo_routes WHERE `id` = $id) AND is_real = 0 ORDER BY `year` DESC, `period` DESC, `id` DESC LIMIT 1";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records[0];
    } else {
        return false;
    }
}

function getActualValueById($id)
{
    $sql = "SELECT * FROM arioo_routes_data WHERE unique_id = (SELECT `unique_id` FROM arioo_routes WHERE `id` = $id) AND is_real = 1 ORDER BY `year` DESC, `period` DESC, `id` DESC LIMIT 1";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    if ($result) {
        return $records[0];
    } else {
        return false;
    }
}