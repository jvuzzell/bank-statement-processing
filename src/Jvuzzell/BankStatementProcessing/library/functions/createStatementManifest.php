<?php 

function createStatementManifest($directoryPath) : array {
    $files = glob($directoryPath . '*');
    $manifest = [];

    foreach ($files as $file) {
        $manifest[] = basename($file);
    } 

    return $manifest;
}