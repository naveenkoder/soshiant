<?php


function isPasswordCorrect($username, $password)
{
    return true;
}


function generateToken($username, $password)
{
    return "";
}

function decodeToken($token)
{
    return [
        'username' => "",
        'password' => ""
    ];
}


function isTokenValid($token)
{
    return true;
}