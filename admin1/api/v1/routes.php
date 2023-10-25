<?php
require_once '../../maincore.php';
require_once '../../models/routes.php';
require_once './services/messages.php';
require_once './services/api.php';
setHeaders();


$action = $_POST['action'];
if ($action == "create") {

    $result = createRoute($_POST);
    echo httpMessage($result ? 200 : 500);
}
else if ($action == "edit_drag_menu") {
    $result = changeLocationOfMenu($_POST);
    echo httpMessage($result ? 200 : 500);
}


else if ($action == "change_status") {
    $result = changeStatus($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "get" || $_GET['action'] == "get") {
    $routes = getRoutes(true);
    echo json_encode($routes);
} else if ($action == "get_menu" || $_GET['action'] == "get_menu") {
    $routes = getRoutes(true);
    echo json_encode($routes);
} else if ($action == "edit") {
    $result = updateRoute($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "edit_average") {
    for ($i = 0; $i < count($_POST['data']); $i++) {
        $data = json_decode($_POST['data'][$i]);
        $unique_id = $data->unique_id;
        $average = $data->average;
        $result = editAverages($unique_id, $average);
    }
    echo httpMessage($result ? 200 : 500);
} else if ($action == "delete") {
    $id = $_POST['id'];
    $result = deleteRoute($id);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "import") {
    $countOfData = count($_POST['data']);
    $imported = 0;
    foreach ($_POST['data'] as $data) {
        $result = createRoute(json_decode($data, true));
        if ($result)
            $imported++;
    }
    fixRoutes();
    if ($imported === $countOfData) {
        if($imported !== 0) {
            echo httpMessage(200);
        }
        else {
            echo httpMessage(204);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => "imported $imported from $countOfData",
            'code' => $statusCode
        ]);
    }
} else {
    echo httpMessage(400);
}


?>