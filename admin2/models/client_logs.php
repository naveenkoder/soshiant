<?php 

function createClientLogs($data)
{
    $user = $data['username'];
    $route = $data['route'];
    $period = $data['period'];
    $unique_id = $data['unique_id'];
    $time = $data['time'];
    if($route != '' && $time != '' && $unique_id != '' && $user != '') {
        $sql = "INSERT INTO `arioo_client_logs` (`username`, `route`, `period`,`unique_id`, `time`) VALUES ('$user', '$route', '$period','$unique_id', '$time')";
        if (dbquery($sql))
            return true;
        else
            return false;
    }
    else {
        return false;
    }        
    
}


function getClientLogs() 
{
    //$sql = "SELECT *, FROM_UNIXTIME(time, '%D %M %Y %h:%i:%s') as formatted_time FROM arioo_client_logs ORDER BY `time` DESC";
    $sql = "SELECT *, FROM_UNIXTIME(time, '%D %M %Y') as formatted_time FROM arioo_client_logs ORDER BY `id` DESC";
    $result = dbquery($sql);
    // die($sql);
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




?>