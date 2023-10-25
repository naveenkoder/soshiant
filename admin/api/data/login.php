<?php
require_once "./services/messages.php";
require_once "./services/authentication.php";




if (isset($_POST["username"]) && isset($_POST["password"])) {
    $token = Login($_POST);
    if ($token != false) {
        httpMessage(HTTP_OK, ["token" => $token]);
    } else {
        httpMessage(HTTP_UNAUTHORIZED);
    }
} else {
    httpMessage(HTTP_BAD_REQUEST);
}