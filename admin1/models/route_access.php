<?php


function createRouteAccess($user, $access, $expire_time)
{
    $sql = "INSERT INTO `arioo_route_access` (`user`, `access`, `expire_time`) VALUES ('$user', '$access', '$expire_time')";
    if (dbquery($sql))
        return true;
    else
        return false;
}


function getAllRoutesAccess()
{
    $sql = "SELECT * FROM `arioo_route_access`";
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
function getRouteAccess($id)
{
    $time = time();
    $sql = "SELECT * FROM `arioo_route_access` WHERE `id`= $id AND `is_ban`= 0 AND expire_time > $time";
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
function getRouteAccessByUsername($username)
{
    $time = time();
    $sql = "SELECT access, expire_time FROM `arioo_route_access` WHERE `user`= (SELECT `user_id` FROM arioo_users WHERE user_name='$username') AND `is_ban`= 0 AND expire_time > $time";
    $result = dbquery($sql);
    $rows = array();
    $expireTime = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row['access'];
            $expireTime[] = $row['expire_time'];
        }
    }
    return ["expireTime" => $expireTime[0], "access" => $rows[0]];
}

function createGroupRouteAccess($user, $access)
{
    $sql = "UPDATE `arioo_route_access` SET `access`= CONCAT(`access`, ',$access') WHERE `user`='$user'";
    echo $sql;
    if (dbquery($sql))
        return true;
    else
        return false;
}

function getRouteAccessByUserId($user_id)
{
    $time = time();
    $sql = "SELECT * FROM `arioo_route_access` WHERE `user`= $user_id AND `is_ban`= 0 AND expire_time > $time";
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}


function updateRouteAccess($user, $access, $expire_time)
{
    $setTime = isset($expire_time) ? ", `expire_time` = '$expire_time'" : '';
    $sql = "UPDATE `arioo_route_access` SET `access`='$access' $setTime WHERE `user`='$user'";
    $result = dbquery($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}



function deleteRouteAccess($id)
{
    $sql = "DELETE FROM arioo_route_access WHERE id=$id";
    if (dbquery($sql)) {
        return true;
    } else {
        return false;
    }
}

function banAccess($id)
{
    $sql = "UPDATE `arioo_route_access` SET `is_ban`= 1 WHERE `id`='$id'";
    if (dbquery($sql)) {
        return true;
    } else {
        return false;
    }
}

function unbanAccess($id)
{
    $time = time();
    $sql = "UPDATE `arioo_route_access` SET `is_ban`= 0 AND expire_time > $time WHERE `id`='$id'";
    if (dbquery($sql)) {
        return true;
    } else {
        return false;
    }
}