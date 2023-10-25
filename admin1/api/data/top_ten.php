<?php
require_once '../../maincore.php';
require_once '../../models/top_ten.model.php';
require_once './services/messages.php';
require_once './services/api.php';
require_once "./services/authentication.php";


$userLevel = UseGaurd();
setHeaders();
// if(!iADMIN) {
//     httpMessage(403);
// };

$action = $_POST['action'];

if ($action == "import") {
    isUserAdministrator($userLevel);
    $countOfData = count($_POST['data']);
    $imported = 0;
    $error = [];
    foreach ($_POST['data'] as $data) {
        $topTen = json_decode($data, true);
        $result = createTopTen($topTen);
        if ($result === true)
            $imported++;
        else
            $error[] = $result;
    }
    if ($imported === $countOfData) {
        echo httpMessage(200);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => "imported $imported from $countOfData",
            'code' => $statusCode,
            'cause' => $error
        ]);
    }
} else if ($action == "get") {
    isUserMember($userLevel);
    $routes = getTopTens($_POST);
    echo json_encode($routes);
} else if ($_POST['action'] == "get_all" || $_GET['action'] == "get_all") {
    isUserMember($userLevel);
    $routes = getAllTopTens();
    echo json_encode($routes);
} else {
    echo httpMessage(404);
}