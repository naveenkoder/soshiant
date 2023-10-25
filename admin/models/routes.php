<?php

function createRoute($data, $is_imported = false)
{
    $id = $data['id'];
    $route = getRouteById($id);
    if ($route) {
        return true;
        // return updateRouteFromExport($data);
    }
    $name = $data['name'];
    $unique_id = $data['unique_id'];
    $parent = is_numeric($data['parent']) ? $data['parent'] : 0;
    $create_time = time();
    $modified_time = time();
    $alerts = "[]";
    $unit = isset($data['unit']) ? $data['unit'] : '';
    $period = isset($data['period']) ? $data['period'] : "";
    $is_active = isset($data['is_active']) ? $data['is_active'] : 1;
    if ($is_imported)
        $is_pendding =  1;
    else
        $is_pendding = isset($data['is_pendding']) ? $data['is_pendding'] : 0;

    
    $sql = "INSERT INTO arioo_routes (
        name,
        unique_id,
        parent,
        create_time,
        modified_time,
        unit,
        period,
        alerts,
        is_active,
        is_pendding
    ) VALUES
    (
       '$name',
       '$unique_id',
        $parent,
        $create_time,
        $modified_time,
       '$unit',
       '$period',
       '$alerts',
        $is_active,
        $is_pendding
    )";

    // echo "\n".var_export($data)."\n";
    // echo "\n$name: $is_active\n";
    if (dbquery($sql)) {
        return true;
    } else {
        return false;
    }
}



function fixRoutes()
{
    $convertParentSql = "UPDATE arioo_routes AS A SET A.parent = COALESCE( (SELECT id FROM ( SELECT * FROM arioo_routes ) AS B WHERE B.unique_id = A.parent), 0), is_pendding = 0 WHERE is_pendding = 1";
    if (dbquery($convertParentSql)) {
        return true;
    } else {
        return false;
    }
}

function getRoutes($getAll)
{
    if ($getAll) {
        $sql = "SELECT * FROM arioo_routes WHERE is_deleted=0 AND is_pendding = 0";
    } else {
        $sql = "SELECT * FROM arioo_routes WHERE is_deleted=0 AND is_pendding = 0 AND is_active = 1";
    }
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function getRouteById($id)
{
    $sql = "SELECT * FROM arioo_routes WHERE is_deleted=0 AND `id` = $id";
    // echo $sql;
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows[0];
}

function getRouteByUniqueId($id)
{
    $sql = "SELECT * FROM arioo_routes WHERE is_deleted=0 AND `unique_id` = $id";
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows[0];
}

function changeStatus($data)
{
    $status = $data['status'];
    $routes = $data['routes'];
    $sql = "UPDATE arioo_routes SET is_active=$status WHERE id in ($routes)";
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}
function editAverages($unique_id, $average)
{
    $modified_time = time();
    $sql = "UPDATE arioo_routes SET modified_time='$modified_time', average='$average' WHERE unique_id = '$unique_id'";
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function updateRoute($data)
{
    $id = $data['id'];
    $fields = [
        "id",
        "alerts",
        "name",
        "unit",
        "unique_id",
        "period",
        "is_active",
    ];
    if (isset($data['parent'])) {
        $fields[] = "parent";
    }
    $sql = "UPDATE arioo_routes SET ";
    $updatedFields = 0;
    for ($i = 0; $i < count($fields); $i++) {
        $field = $fields[$i];
        $value = $data[$field];
        if ($updatedFields === 0) {
            $sql .= "$field='$value'";
        } else
            $sql .= " ,$field='$value'";
        $updatedFields++;
    }
    $sql .= " WHERE id=$id";
    // echo $sql;   
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}


function updateRouteFromExport($data)
{
    $id = $data['id'];
    $fields = [
        "id",
        "name",
        "unit",
        "period",
        "is_active",
    ];
    $sql = "UPDATE arioo_routes SET ";
    $updatedFields = 0;
    for ($i = 0; $i < count($fields); $i++) {
        $field = $fields[$i];
        $value = $data[$field];
        if ($updatedFields === 0) {
            $sql .= "$field='$value'";
        } else
            $sql .= " ,$field='$value'";
        $updatedFields++;
    }
    $sql .= " WHERE id=$id";
    // echo $sql;
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function deleteRoute($ids)
{
    // $sql = "UPDATE arioo_routes SET is_deleted=1 WHERE id in ($ids)";
    $sql = "DELETE FROM arioo_routes WHERE id in ($ids)";
    if (dbquery($sql)) {
        return true;
    } else {
        return false;
    }
}
