<?php 

function createStatementManifest($directoryPath) : array {
    $pdfFiles = glob($directoryPath . '*');
    $manifest = [];

    foreach ($pdfFiles as $pdfFile) {
        $manifest[] = basename($pdfFile);
    } 

    return $manifest;
}