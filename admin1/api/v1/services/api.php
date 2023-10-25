<?php
function setHeaders() {
    header('Content-type: application/json; charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authentication");
    // header("Access-Control-Allow-Headers: *");
    http_response_code(200);
    return $_SERVER['HTTP_AUTHENTICATION'];
}
