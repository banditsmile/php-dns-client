<?php

$zoneFile = 'https://www.internic.net/domain/root.zone';
$ns = array();
$rootzone = file($zoneFile, FILE_IGNORE_NEW_LINES);
foreach ($rootzone as $zone) {
    $array = explode("\t", $zone);
    if (isset($array[2]) && $array[2]=='IN' && $array[3]=='NS') {
        $tld = strtolower($array[0]);
        if ($tld) {
            $tld=trim($tld, '.');
            $ns[$tld][] = $array[4];
        }
    }
}
var_export($ns);