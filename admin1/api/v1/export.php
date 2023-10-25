<?php
require_once __DIR__ . "/../../maincore.php";
require_once __DIR__ . "/../../models/routes_data.php";
require_once __DIR__ . "/../../models/top_ten.model.php";
require_once './services/api.php';
require_once './services/messages.php';
setHeaders();


if ($_POST['action'] == "export_routes") {
    $data = getRecords($_POST);
    echo json_encode($data);
} else if ($_POST['action'] == "export_top_ten") {
    $data = getTopTens($_POST['routes']);
    echo json_encode($data);
} else {
    httpMessage(404);
}
