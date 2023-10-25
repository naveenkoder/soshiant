<?php
require_once __DIR__ . "/../../maincore.php";
require_once __DIR__ . "/../../models/route_access.php";
require_once './services/api.php';
require_once './services/messages.php';
$tokenString = setHeaders();
$token = explode(":", $tokenString);
$username = $token[0];
$password = $token[1];
if ($_POST['action'] == "edit") {
    $user = $_POST['user'];
    $routes = $_POST['routes'];
    $expire_time = $_POST['expire_time'];
    $UserAccess = getRouteAccessByUserId($user);
    if (count($UserAccess) == 0) {
        $result = createRouteAccess($user, $routes, $expire_time);
        echo httpMessage($result ? 200 : 500);
    } else {
        $result = updateRouteAccess($user, $routes, $expire_time);
        echo httpMessage($result ? 200 : 500);
    }
} else if ($_POST['action'] == "get_all" || $_GET['action'] == "get_all") {
    $result = getAllRoutesAccess();
    echo json_encode($result);
} else if ($_POST['action'] == "get_by_username" || $_GET['action'] == "get_by_username") {
    if (!$username) {
        echo httpMessage(403);
        die();
    }
    $result = getRouteAccessByUsername($username);
    $access = explode(",", $result['access']);
    $result['access'] = $access;
    http_response_code(200);
    if (isset($result))
        echo json_encode($result);
    else
        echo httpMessage(401);
} else if ($_POST['action'] == "grouppush") {
    $users = $_POST['user'];
    $routes = $_POST['routes'];
    // var_dump($users);
    // echo "\n\n\n\n";
    // var_dump($routes);
    // echo "\n\n\n\n";
    //$UserAccess = getRouteAccessByUserId($users);
    foreach ($users as $user) {
        // var_dump($user);
        // echo "\n\n\n\n";
        createGroupRouteAccess($user, $routes);
        // $result += $subres;
    }
    // $result += createGroupRouteAccess($users, $routes);
    echo httpMessage($result ? 200 : 500);
} else {
    echo httpMessage(200);
}
