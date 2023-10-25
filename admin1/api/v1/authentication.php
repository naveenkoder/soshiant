<?php
require_once __DIR__ . "/../../maincore.php";
require_once './services/api.php';
require "./services/authentication.service.php";
require "./services/messages.php";

setHeaders();

function Login($data)
{
    $username = $data['username'];
    $password = $data['password'];
    $sql = "SELECT * FROM " .DB_USERS . " WHERE `user_name` = '$username'";
    $user = dbarray(dbquery($sql));
    if(!isset($user)) {
        return false;
    }
    $inputHash = hash_hmac($user['user_algo'], $password, $user['user_salt']);
    $currentPasswordHash = $user["user_password"];
    if ($inputHash == $currentPasswordHash) {
        return $inputHash;
    }
    else {
        return false;
    }
}

function CheckToken($token, $username)
{
    $sql = "SELECT * FROM " .DB_USERS. " WHERE `user_password` = '$token' AND `user_name` = '$username'";
    $user = dbarray(dbquery($sql));
    return count($user) > 0 && $user !== false;
}


if ($_POST['action'] == 'get_token') {
    $token = Login($_POST);
    http_response_code($token != false ? 200 : 401);
    echo json_encode(['token' => $token, 'user' => $_POST['username']]);
} 
else if ($_POST['action'] == 'check_token') {
    $token = CheckToken($_POST['token'], $_POST['username']);
    http_response_code($token ? 200 : 401);
} 
else {
    echo httpMessage(400);
}
