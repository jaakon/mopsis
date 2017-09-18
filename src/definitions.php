<?php

$globalDefinitions = [];

foreach(glob(__DIR__ . '/Definitions/*.php') as $file) {
    $localDefinitions = include_once $file;
    $conflicts        = array_intersect_key($globalDefinitions, $localDefinitions);

    if (count($conflicts)) {
        $keys = implode('", "', array_keys($conflicts));
        die('duplicate definitions detected: "' . $keys . '"');
    }

    $globalDefinitions = array_merge($globalDefinitions, $localDefinitions);
}

return $globalDefinitions;