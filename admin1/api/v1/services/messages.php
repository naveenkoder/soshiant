<?php


define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_SERVER_ERROR', 500);

function httpMessageCode($statusCode) {
    switch ($statusCode) {
        case 200:
            return "OK";
        case 201:
            return "Created";
        case 204:
            return "No Content";
        case 400:
            return "Bad Request";
        case 401:
            return "Unauthorized";
        case 403:
            return "Forbidden";
        case 404:
            return "Not Found";
        case 500:
            return "Internal Server Error";
        default:
            return "Unknown Status Code";
    }
}


function httpMessage($statusCode = 200) {
    if(!is_numeric($statusCode)) {
        $statusCode = 500;
    }
    http_response_code(200);
    return json_encode([
        'message' => httpMessageCode($statusCode),
        'code' => $statusCode
    ]);
}