<?php

require_once __DIR__."../../../maincore.php";
require_once __DIR__."/services/routes_tree.service.php";
require_once './services/api.php';
setHeaders();
if(gettype($_GET['type']) == "NULL" || $_GET['type'] == "default") {
    if(gettype($_GET['key']) == "NULL") {
        echo json_encode(getMenu());
    }
    else {
        echo json_encode(getMenu($_GET['key']));
    }
}
else if($_GET['type'] == "json") {
    echo json_encode(getRoutes(true));
}