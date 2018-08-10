<?php
use CFPropertyList\CFPropertyList;

require_once ('vendor/autoload.php');

try {
    $plist = new CFPropertyList();
    $plist->loadXMLStream(fopen("php://stdin", "r"));

    $arr = $plist->toArray();
    $devices = $arr[0]['_items'][0]['device_title'][0];
    foreach ($devices as $device => $info) {
        printf('"%s","%s","%s"', time(), md5($device), str_replace('%', '', $info['device_batteryPercent']));
        echo PHP_EOL;
    }
} catch (Exception $e) {
    exit(1);
}
