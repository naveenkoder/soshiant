<?php
require_once __DIR__ . "/../../maincore.php";
require_once __DIR__ . "/../../models/routes_data.php";
require_once './services/messages.php';
require_once "./services/authentication.php";


$userLevel = UseGaurd();


if ($_POST['action'] == "create") {
    isUserAdministrator($userLevel);
    $result = createRouteData(json_decode($_POST['data']));
    echo httpMessage($result ? 200 : 500);
} else if ($_POST['action'] == "get_by_unique_id" || $_GET['action'] == "get_by_unique_id") {
    isUserMember($userLevel);
    if (isset($_POST['id']) || isset($_GET['id'])) {
        $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
        echo json_encode(getRecordsDataByUniqueId($id));
    } else
        http_response_code(400);
} else if ($_POST['action'] == "get_by_id" || $_GET['action'] == "get_by_id") {
    isUserMember($userLevel);
    if (isset($_POST['id']) || isset($_GET['id'])) {
        $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
        echo json_encode(getRecordsDataById($id));
    } else
        http_response_code(400);
} else if ($_POST['action'] == "get_all") {
    isUserMember($userLevel);
    // $ids = $_POST['ids'];
    echo json_encode(getAllData());
} else if ($_POST['action'] == "get" || $_GET['action'] == "get") {
    isUserMember($userLevel);
    echo json_encode(getAllPredictedData());
} else if ($_POST['action'] == "get_prediction" || $_GET['action'] == "get") {
    isUserMember($userLevel);
    echo json_encode(getAllPredictedData());
} else if ($_POST['action'] == "get_evaluation" || $_GET['action'] == "get_evaluation") {
    isUserMember($userLevel);
    echo json_encode(getAllActualData());
} else if ($_POST['action'] == "get_actual_history" || $_GET['action'] == "get_actual_history") {
    isUserMember($userLevel);
    echo json_encode(getActualValueHistory());
} else if ($_POST['action'] == "get_predicted_history" || $_GET['action'] == "get_predicted_history") {
    isUserMember($userLevel);
    echo json_encode(getPredictedValueHistory());
} else if ($_POST['action'] == "get_last_actual_data" || $_GET['action'] == "get_last_actual_data") {
    isUserMember($userLevel);
    echo json_encode(getPredictedValueHistory());
} else if ($_POST['action'] == "get_history" || $_GET['action'] == "get_history") {
    isUserMember($userLevel);
    echo json_encode(getHistory());
} else if ($_POST['action'] == "import") {
    isUserAdministrator($userLevel);
    $countOfData = count($_POST['data']);
    $imported = 0;
    foreach ($_POST['data'] as $data) {
        $result = createRouteData(json_decode($data));
        if ($result)
            $imported++;
    }
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
    httpMessage(404);
}
