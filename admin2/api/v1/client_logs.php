<?php
require_once '../../maincore.php';
require_once '../../models/client_logs.php';
require_once './services/api.php';
require_once './services/messages.php';
setHeaders();


if ($_POST['action'] == "save_client_logs" || $_GET['action'] == "save_client_logs") {
    $result = createClientLogs($_POST);
    die('enter');
    if($result) {
       echo json_encode([
            'message' => "Clients Logs saved",
            'code' => 200,
        ]);
    }
    else
    {
        echo json_encode([
            'message' => "Clients Logs not saved",
            'code' => 500,
        ]);
    }
}
else if ($_GET['type'] == "get_client_logs") {
    $result = getClientLogs(true);
    echo json_encode(['data' =>$result]);
}
else {
    echo httpMessage(404);
}




