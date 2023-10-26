<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_log.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess("CLP");
// Add datatable to head
add_to_head('<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />');
add_to_head('<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>');
#endregion data table
?>

<style>
    .container {
        width: 100%;
    }

    .row {
        width: 100%;
        margin: 10px;
    }

    .table-wrapper {
        width: 95%;
        margin: 30px 10px;

    }
    
</style>
<div class="container">
    <div class="row">
    <div class="col-md-12 table-wrapper table-responsive">
            <table class="table" id="client-logs-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Username</th>
                        <th>Route</th>
                        <th>Period</th>                        
                        <th>Unique Id</th>
                        <th>Date Modified</th>
                    </tr>
                </thead>
                <tbody id="body-client-logs">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function getClientLogs() {    
        $('#client-logs-table').DataTable({
            "ajax": {
                "url": "http://localhost/soshiane/admin2/api/v1/get_client_logs.php", 
                "dataSrc": "data" 
            },
            "columns": [
                { "data": "id" },
                { "data": "period" },
                { "data": "route" },
                { "data": "period" },
                { "data": "unique_id" },
                { "data": "formatted_time" }
            ],
            "order": [[0, "desc"]]
        });
    }

    function bootstrap() {
        getClientLogs();
    }

    $(() => {
        bootstrap();
    });

</script>
<?php 

require_once THEMES.'templates/footer.php';