<?php
require_once '../../maincore.php';
require_once '../../models/client_logs.php';
require_once './services/api.php';
require_once './services/messages.php';
setHeaders();

$action = $_POST['action'];

if ($action == "save_client_logs") {
    $result = createClientLogs($_POST);
    if($result) {
        echo json_encode([
            'message' => "Clients Logs saved",
            'code' => 200,
        ]);
    }
    else {
        echo json_encode([
            'message' => "Clients Logs not saved",
            'code' => 500,
        ]);
    }
}
else if ($action == "get_client_logs") {
    die('arun');
}
else {
    echo httpMessage(404);
}




?>