<?php
require_once '../../maincore.php';
require_once '../../models/client_logs.php';
require_once './services/api.php';
require_once './services/messages.php';
setHeaders();

$result = getClientLogs(true);
echo json_encode(['data' =>$result]);







?>