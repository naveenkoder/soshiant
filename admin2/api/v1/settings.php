<?php
require_once __DIR__ . "/../../maincore.php";
require_once './services/api.php';
require_once '../../models/settings.php';
require "./services/messages.php";

setHeaders();

if ($_GET['action'] === "get_charts_settings") {
    $chartSettings = getChartsSettings();
    echo json_encode($chartSettings);
} else if ($_POST['action'] === "set_charts_settings") {
    $result = setChartsSettings($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($_POST['action'] === "set_introduction") {
    $result = setIntroductionSettings($_POST);
    echo httpMessage($result ? 200 : 500);
} else if ($_POST['action'] === "get_introduction" || $_GET['action'] === "get_introduction") {
    $result = getIntroductionSettings();
    echo json_encode($result);
} else {
    echo httpMessage(400);
}

?>