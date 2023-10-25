<?php 

function createClientLogs($data)
{
    $user = $data['user_id'];
    $route = $data['route'];
    $period = $data['period'];
    $unique_id = $data['unique_id'];
    $time = $data['time'];
    $sql = "INSERT INTO `arioo_client_logs` (`user_id`, `route`, `period`,`unique_id`, `time`) VALUES ('$user', '$route', '$period','$unique_id', '$time')";
    if (dbquery($sql))
        return true;
    else
        return false;
}




?>