<?php 

function manifestDirContent($directoryPath) : array {
    $files = glob($directoryPath . '*');
    $manifest = [];

    foreach ($files as $file) {
        $manifest[] = basename($file);
    } 

    return $manifest;
}