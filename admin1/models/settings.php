<?php


function getChartsSettings()
{
    $sql = "SELECT settings_name AS chartName, settings_value AS value FROM `arioo_settings` WHERE `settings_name` LIKE 'user-panel-chart-%'";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records;
}
function getSetting($name)
{
    $sql = "SELECT * FROM `arioo_settings` WHERE `settings_name` LIKE '$name'";

    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records;
}


function setChartsSettings($data)
{
    $chartType = $data["chartType"];
    $value = $data["value"];
    $idCreated = count(getSetting($chartType)) > 0;
    $sql = "INSERT INTO `arioo_settings` (`settings_name`, `settings_value`) VALUE ('$chartType', '$value')";

    if ($idCreated) {
        $sql = "UPDATE `arioo_settings` SET `settings_value` = '$value' WHERE settings_name = '$chartType'";
    }
    $result = dbquery($sql);
    return $result;
}

function setIntroductionSettings($data)
{
    $introductionKey = "introduction_value";
    $value = $data["value"];
    $idCreated = count(getSetting($introductionKey)) > 0;
    $sql = "INSERT INTO `arioo_settings` (`settings_name`, `settings_value`) VALUE ('$introductionKey', '$value')";

    if ($idCreated) {
        $sql = "UPDATE `arioo_settings` SET `settings_value` = '$value' WHERE settings_name = '$introductionKey'";
    }
    $result = dbquery($sql);
    return $result;
}

function getIntroductionSettings()
{
    $sql = "SELECT * FROM `arioo_custom_pages` WHERE `page_title` = 'Introduction' OR `page_title` = 'ContactUs' OR `page_title` = 'About' OR `page_title` = 'Address'";
    $result = dbquery($sql);
    $records = array();
    while ($row = dbarray($result)) {
        $records[] = $row;
    }
    return $records;
}