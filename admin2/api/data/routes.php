<?php
require_once '../../maincore.php';
require_once '../../models/routes.php';
require_once './services/messages.php';
require_once "./services/authentication.php";

$userLevel = UseGaurd();

$action = $_POST['action'];
if ($action == "create") {
    isUserAdministrator($userLevel);
    $result = createRoute($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "change_status") {
    isUserAdministrator($userLevel);
    $result = changeStatus($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "get" || $_GET['action'] == "get") {
    isUserMember($userLevel);
    $routes = getRoutes(true);
    echo json_encode($routes);
} else if ($action == "get_menu" || $_GET['action'] == "get_menu") {
    isUserAdministrator($userLevel);
    $routes = getRoutes(true);
    echo json_encode($routes);
} else if ($action == "edit") {
    $result = updateRoute($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "edit_average") {
    isUserAdministrator($userLevel);
    for ($i = 0; $i < count($_POST['data']); $i++) {
        $data = json_decode($_POST['data'][$i]);
        $unique_id = $data->unique_id;
        $average = $data->average;
        $result = editAverages($unique_id, $average);
    }
    echo httpMessage($result ? 200 : 500);
} else if ($action == "delete") {
    isUserAdministrator($userLevel);
    $id = $_POST['id'];
    $result = deleteRoute($id);
    echo httpMessage($result ? 200 : 500);
} else if ($action == "import") {
    isUserAdministrator($userLevel);
    $countOfData = count($_POST['data']);
    $imported = 0;
    foreach ($_POST['data'] as $data) {
        $result = createRoute(json_decode($data, true));
        if ($result)
            $imported++;
    }
    fixRoutes();
    if ($imported === $countOfData) {
        echo httpMessage(200);
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
