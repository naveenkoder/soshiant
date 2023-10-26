<?php
require_once __DIR__ . "/routes.php";

function createTopTen($data)
{
    $unique_id = $data['unique_id'];
    $top_ten = addslashes($data['top_ten']);
    $create_time = time();
    $user_id = fusion_get_userdata()['user_id'] ?? 0 ;
    $username = fusion_get_userdata()['user_name'] ?? null;
    $sql = "INSERT INTO arioo_top_ten (create_time, top_ten, unique_id,user_id, user_name) VALUES ($create_time, '$top_ten', '$unique_id',$user_id, '$username')";
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return $sql;
    }
}



function getTopTens($data)
{
    $rotues = $data['routes'];
    $sql = "SELECT * FROM `arioo_top_ten` WHERE `unique_id` in ($rotues);";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records;
}
function getAllTopTens()
{
    $records = array();
    $ids = getRoutes(true);
    foreach ($ids as $id) {
        $records[] = getTopTenByUniqueId($id["unique_id"]);
    }
    return array_filter($records, function ($value) {
        return !is_null($value);
    });
}

function getTopTenById($id)
{
    $sql = "SELECT * FROM `arioo_top_ten` WHERE `unique_id` = (SELECT `unique_id` FROM arioo_routes WHERE id = $id) ORDER BY id DESC";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records[0];
}

function getTopTenByUniqueId($id)
{
    $sql = "SELECT * FROM `arioo_top_ten` WHERE `unique_id` = $id ORDER BY id DESC";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records[0];
}