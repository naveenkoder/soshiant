<?php
require_once __DIR__ . "/../../../maincore.php";
require_once __DIR__ . "/messages.php";
function Login($data)
{
    $username = $data['username'];
    $password = $data['password'];
    $sql = "SELECT * FROM " . DB_USERS . " WHERE `user_name` = '$username'";
    $user = dbarray(dbquery($sql));
    if (!isset($user)) {
        return false;
    }
    $currentPasswordHash = $user["user_password"];
    $inputHash = hash_hmac($user['user_algo'], $password, $user['user_salt']);
    if ($inputHash == $currentPasswordHash) {
        return base64_encode("$username:$inputHash");
    } else {
        return false;
    }
}

function CheckToken($token)
{
    $token = explode(" ", $token)[1];
    $token = base64_decode($token);
    $token = explode(":", $token);
    $username = $token[0];
    $password = $token[1];
    $user = GetUser($username, $password);
    return $user;
}


$Levels = [
    "-103" => "Administrator",
    "-102" => "Admin",
    "-101" => "Member",
    "Administrator" => "-103",
    "Admin" => "-102",
    "Member" => "-101"
];



function GetUser($username, $password)
{
    $sql = "SELECT * FROM " . DB_USERS . " WHERE `user_password` = '$password' AND `user_name` = '$username'";
    $user = dbarray(dbquery($sql));
    return $user;
}


function UseGaurd()
{
    global $Levels;
    $token = $_SERVER['HTTP_AUTHENTICATION'];
    $user = CheckToken($token);
    if (!$user) {
        echo httpMessage(HTTP_UNAUTHORIZED);
        exit();
    } else {
        return $Levels[$user['user_level']];
    }
}


function isUserAdministrator($authenticationLevel)
{
    if($authenticationLevel !== "Administrator") {
        http_response_code(403);
        exit();
        return false;
    }
    return true;
}

function isUserAdmin($authenticationLevel)
{
    if($authenticationLevel !== "Admin" && $authenticationLevel !== "Administrator") {
        http_response_code(403);
        exit();
        return false;
    }
    return true;
}

function isUserMember($authenticationLevel)
{
    if($authenticationLevel !== "Member" && $authenticationLevel !== "Admin" && $authenticationLevel !== "Administrator") {
        http_response_code(403);
        exit();
    return false;
    }
    return true;
}
