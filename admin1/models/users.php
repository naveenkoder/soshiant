<?php

function getAllUsers() {
    $sql = "SELECT `user_id`, `user_name` FROM `arioo_users` WHERE 1";
    $result = dbquery($sql);
    $rows = array();
    if ($result) {
        while ($row = dbarray($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}